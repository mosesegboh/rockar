<template>
    <div id="model-filter" v-show="$parent.steps[$parent.currentStep] === 'modelFilter'">
        <h3 class="model-step-title">
            {{ stepTitles['step_title'] }}
        </h3>
        <p class="model-navigation-title" v-if="stepTitles['model_navigation_title']">
            {{ stepTitles['model_navigation_title'] }}
        </p>

        <div class="model-navigation" v-if="showModelNavigation">
            <app-select
                @select="updateCurrentSelectedModel"
                id='model-navigation-mobile'
                :options="modelFilter.modelsNavigation"
                :init-selected="updateInitSelectedModel"
                class="model-navigation-mobile"
            ></app-select>

            <ul class="model-navigation-desktop">
                <li class="model-list"
                    v-for="filterOption in modelFilter.modelsNavigation"
                    :track-by="filterOption.id"
                    :key="filterOption.id"
                >
                    <a @click="changeCurrentSelectedModel(filterOption.id)" id="{{ filterOption.id }}"
                       class="model-link" :class="{
                        'active' : currentSelectedNavigationModel === filterOption.id,
                        'has-selected' : checkModelHasSelected(filterOption.id)
                    }">{{ filterOption.title }}</a>
                </li>
            </ul>
        </div>
        <div class="row" v-if="modelFilter.matrix.length">
            <template v-for="filterOption in modelFilter.matrix">
                <div class="align-center col-3 col-sm-12 model-wrapper" v-if="filterOption.modelId">
                    <div class="model-item"
                         :class="{ 'disabled' : filterOption.count === 0, 'active' : checkMatrixExists(filterOption) }"
                         @click="changeSelectedMatrixItems(filterOption)">
                        <img :src="filterOption.image">
                        <div class="align-left">
                            <p>{{ filterOption.title }}</p>
                            <p v-if="filterOption.count === 0">{{ "This model is either out of stock or doesn't match your budget selection" | translate }}</p>
                            <p v-if="filterOption.count > 0">{{ filterOption.count }} results</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div v-else class="row">
            <p class="model-empty">{{ 'No available series for selected model' | translate }}</p>
        </div>
    </div>
</template>

<script>
    import appSelect from 'core/components/Elements/Select';

    export default Vue.extend({
        props: {
            showModelNavigation: {
                required: false,
                type: Boolean,
                default: true
            },

            stepTitles: {
                required: true,
                type: Object
            }
        },

        data() {
            return {
                currentSelectedNavigationModel: 0,
                activeItems: {},
                activeItemsCount: 0,
                lastActiveNavigationItem: null,
                navigationItems: [],
                matrixAttribute: ['mot_model_matrix_carousel']
            }
        },

        computed: {
            CarFinder() {
                return this.$root.$refs.carFinder;
            },

            updateInitSelectedModel() {
                let selectIndex = 0;

                this.modelFilter.modelsNavigation.forEach((filter, index) => {
                    if (filter.id === this.currentSelectedNavigationModel) {
                        selectIndex = index;
                    }
                });

                return selectIndex;
            },

            filters() {
                return this.$store.state.carFinder.carFilters;
            },

            modelFilter() {
                const result = {}

                this.filters.forEach((filter, index) => {
                    if (this.matrixAttribute.indexOf(filter.code) > -1) {
                        result.modelsNavigation = this.getModelsList(this.filters[index].options);
                        result.matrix = this.getMatrixList(this.filters[index].options);
                    }
                });

                return result;
            },

            modelFilterValid() {
                this.activeItemsCount = 0;
                let valid = false;

                this.filters.forEach((filter) => {
                    if (this.matrixAttribute.indexOf(filter.code) > -1) {
                        filter.options.forEach((option) => {
                            if (option.state === true && option.count !== 0) {
                                valid = true;
                                this.$set(`activeItems['${option.modelId}'][${option.id}]`, true);
                                this.activeItemsCount += Number(option.count);
                            } else {
                                this.$set(`activeItems['${option.modelId}'][${option.id}]`, false);
                            }
                        });
                    }
                });

                if (this.CarFinder !== undefined) {
                    this.CarFinder.updateButtonTitle();
                }

                return valid
            },
        },

        methods: {
            getLatestActiveItem() {
                const filteredList = this.modelFilter.modelsNavigation.filter(navModel => {
                    if (navModel.id !== '') return navModel.id;
                });

                this.currentSelectedNavigationModel = filteredList[0].id;

                this.filters.forEach((filter) => {
                    if (this.matrixAttribute.indexOf(filter.code) > -1) {
                        filter.options.forEach((option) => {
                            if (option.state === true) {
                                this.currentSelectedNavigationModel = option.modelId;
                            }
                        });
                    }
                });
            },

            updateCurrentSelectedModel(data) {
                this.changeCurrentSelectedModel(data.id);
            },

            changeCurrentSelectedModel(id) {
                this.currentSelectedNavigationModel = id;
            },

            getModelsList(options) {
                let count = 0;
                const array = [];

                options.forEach((value) => {
                    if (array.indexOf(value.modelId) === -1) {
                        array.push(value.modelId);

                        this.$set(`navigationItems[${count}]['id']`, value.modelId);
                        this.$set(`navigationItems[${count}]['title']`, value.modelId)
                        this.$set(`navigationItems[${count}]['position']`, value.modelPosition);

                        count++;
                    }

                    if (value.state === true) {
                        this.lastActiveNavigationItem = value.modelId;
                    }
                });

                return this.navigationItems.sort((a, b) => (a.position - b.position));
            },

            getMatrixList(options) {
                if (this.showModelNavigation && this.navigationItems.length) {
                    options = options.filter(value => value.modelId === this.currentSelectedNavigationModel)
                }

                return options;
            },

            changeSelectedMatrixItems(filterOption) {
                let flag = true;

                if (filterOption.count === 0) {
                    return false;
                }

                if (this.checkMatrixExists(filterOption)) {
                    this.activeItemsCount -= Number(filterOption.count);
                    flag = false;
                } else {
                    this.activeItemsCount += Number(filterOption.count);
                }

                filterOption.state = flag;
                this.$set(`activeItems['${filterOption.modelId}'][${filterOption.id}]`, flag);
            },

            checkMatrixExists(filterOption) {
                const matrixId = filterOption.id;
                const modelId = filterOption.modelId;

                return (this.activeItems.hasOwnProperty(modelId) && this.activeItems[modelId].hasOwnProperty(matrixId)
                    && this.activeItems[modelId][matrixId] === true
                    && filterOption.count !== 0);
            },

            checkModelHasSelected(modelId) {
                let hasSelected = false;

                if (this.activeItems.hasOwnProperty(modelId)) {
                    Object.keys(this.activeItems[modelId]).forEach((matrixId) => {
                        if (this.activeItems[modelId][matrixId] === true) {
                            hasSelected = true;
                        }
                    });
                }

                return hasSelected;
            }
        },

        components: {
            appSelect
        },

        ready() {
            if (this.showModelNavigation && this.modelFilter.modelsNavigation.length) {
                const filteredList = this.modelFilter.modelsNavigation.filter(navModel => {
                    if (navModel.id !== '') return navModel.id;
                });

                this.currentSelectedNavigationModel = filteredList[0].id;

                if (this.lastActiveNavigationItem) {
                    this.currentSelectedNavigationModel = this.lastActiveNavigationItem
                }
            }
        }
    });
</script>
