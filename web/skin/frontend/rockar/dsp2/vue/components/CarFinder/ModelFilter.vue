<template>
    <div id="model-filter" v-show="isVisible">

        <div class="model-navigation" v-if="showModelNavigation">
            <app-carousel-model-matrix
                v-if="showModelMatrixCarousel"
                class="model-navigation-mobile"
                :slides="modelFiltersShort"
                :all-models="false"
                :notify-me-enabled="notifyMeEnabled"
                :notify-me-url="notifyMeUrl"
                :is-mobile="true"
                :selected="currentSelectedNavigationModel"
                :options="{
                    initialSlide: 0,
                    variableWidth: true,
                    centerMode: false,
                    focusOnSelect: false,
                    infinite: false,
                    prevArrow: false,
                    nextArrow: false,
                    useTransform: false
                }"
            >
            </app-carousel-model-matrix>

            <ul class="model-navigation-desktop">
                <template v-for="filterOption in modelFiltersShort">
                    <li class="model-list"
                        :track-by="filterOption.id"
                        :key="filterOption.id"
                        :class="{'active' : currentSelectedNavigationModel === filterOption.id}"
                    >
                        <a @click="changeCurrentSelectedModel(filterOption.id)" id="{{ filterOption.id }}"
                           class="model-link" :class="{
                            'active' : currentSelectedNavigationModel === filterOption.id,
                            'has-selected' : checkModelHasSelected(filterOption.id)
                        }">{{ filterOption.title }}</a>
                    </li>
                </template>
            </ul>
        </div>
        <div v-if="matrixFilter.length">
            <app-carousel-model-matrix
                v-if="showModelMatrixCarousel"
                :slides="matrixFilter"
                :checkboxes="true"
                :all-models="false"
                :notify-me-enabled="notifyMeEnabled"
                :notify-me-url="notifyMeUrl"
                :options="{
                    slidesToShow: 4,
                    initialSlide: 0,
                    centerMode: false,
                    focusOnSelect: false,
                    responsive: [
                        {
                            breakpoint: 1200,
                            settings: {
                                slidesToShow: 3,
                            }
                        },
                        {
                            breakpoint: 600,
                            settings: {
                                slidesToShow: 2,
                            }
                        }
                    ]
                }"
            ></app-carousel-model-matrix>
        </div>
        <div v-if="currentSelectedNavigationModel === 'all'">
            <app-carousel-model-matrix
                v-if="showModelMatrixCarousel"
                :slides="modelFilter"
                :all-models="true"
                :notify-me-enabled="notifyMeEnabled"
                :notify-me-url="notifyMeUrl"
                :options="{
                    slidesToShow: 4,
                    initialSlide: 0,
                    centerMode: false,
                    responsive: [
                        {
                            breakpoint: 1200,
                            settings: {
                                slidesToShow: 3,
                            }
                        },
                        {
                            breakpoint: 600,
                            settings: {
                                slidesToShow: 2,
                            }
                        }
                    ]
                }"
            ></app-carousel-model-matrix>
        </div>
    </div>
</template>

<script>
    import translateString from 'core/filters/Translate';
    import appCarouselModelMatrix from 'dsp2/components/CarFinder/CarouselModelMatrix';

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
            },

            notifyMeEnabled: {
                required: false,
                type: Boolean,
                default: false
            },

            notifyMeUrl: {
                required: false,
                type: String,
                default: ''
            }
        },

        data() {
            return {
                currentSelectedNavigationModel: 'all',
                activeItems: {},
                activeItemsCount: 0,
                lastActiveNavigationItem: null,
                navigationItems: [],
                matrixAttribute: ['bmw_model_matrix_carousel'],
                modelAttribute: 'bmw_series',
                showModelMatrixCarousel: true,
                selectedModels: []
            }
        },

        computed: {
            modelFiltersShort() {
                const result = [
                    {
                        id: 'all',
                        title: this.translateString('ALL')
                    }
                ];
                result.push(...this.modelFilter);

                return result;
            },

            CarFinder() {
                return this.$root.$refs.carFinder;
            },

            filters() {
                return this.$store.state.carFinder.carFilters;
            },

            modelFilter() {
                const result = []
                const models = this.filters
                    .filter(item => item.code === this.modelAttribute);
                let modelOptions = {};

                if (models.length) {
                    modelOptions = models[0].options
                        .reduce((acc, item) => {
                            acc[item.title] = item;

                            return acc;
                        },
                        {}
                    );
                }

                this.filters.forEach((filter, index) => {
                    if (this.matrixAttribute.indexOf(filter.code) > -1) {
                        this.getModelsList(this.filters[index].options, modelOptions);
                    }
                });

                result.push(...this.navigationItems);
                result.sort((a, b) => (parseInt(a.position) - parseInt(b.position)));

                return result;
            },

            matrixFilter() {
                let matrix = [];

                this.filters.forEach((filter, index) => {
                    if (this.matrixAttribute.indexOf(filter.code) > -1) {
                        matrix = this.getMatrixList(this.filters[index].options);
                    }
                });

                return matrix;
            },

            modelFilterValid() {
                this.activeItemsCount = 0;
                let valid = false;

                this.filters.forEach((filter) => {
                    if (this.matrixAttribute.indexOf(filter.code) > -1) {
                        filter.options.forEach((option) => {
                            if (option.state === true && option.count !== 0) {
                                valid = true;
                                this.$set(`activeItems['${option.modelId}'][${option.id}]['title']`, option.title);
                                this.activeItemsCount += Number(option.count);
                            } else {
                                this.$set(`activeItems['${option.modelId}'][${option.id}]`, false);
                            }
                        });
                    }
                });

                return valid
            },

            getActiveModelTitles() {
                const modelsTitles = [];

                Object.values(this.activeItems).forEach(models => {
                    Object.values(models).forEach(value => {
                        if (typeof value === 'object') {
                            modelsTitles.push(value.title)
                        }
                    })
                });

                return modelsTitles;
            },

            carFilter() {
                return this.$root.$refs.carFilter;
            },

            isVisible() {
                return !(this.CarFinder.steps[this.CarFinder.currentStep] === 'carFilter' && !this.carFilter.showMobileMenu);
            }
        },

        methods: {
            translateString,

            getLatestActiveItem() {
                const filteredList = this.modelFilter.filter(navModel => {
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

            changeCurrentSelectedModel(id) {
                this.currentSelectedNavigationModel = id;
                this.rerenderModelMatrixCarousel();
            },

            rerenderModelMatrixCarousel() {
                this.showModelMatrixCarousel = false;
                this.$nextTick(() => {
                    this.showModelMatrixCarousel = true;
                });
            },

            getModelsList(options, models) {
                let count = 0;
                const array = [];

                options.forEach((value) => {
                    if (array.indexOf(value.modelId) === -1) {
                        array.push(value.modelId);
                        const longTitle = `${value.modelId} ${RegExp('[0-9]+', 'g').test(value.modelId) ? ' Series' : ' Models'}`;
                        this.$set(`navigationItems[${count}]['id']`, value.modelId);
                        this.$set(`navigationItems[${count}]['title']`, value.modelId);
                        this.$set(`navigationItems[${count}]['position']`, parseInt(value.modelPosition));
                        this.$set(`navigationItems[${count}]['image']`, models[value.modelId].image);
                        this.$set(`navigationItems[${count}]['longTitle']`, longTitle);
                        count++;
                    }

                    if (value.state === true) {
                        this.lastActiveNavigationItem = value.modelId;
                    }
                });
            },

            getMatrixList(options) {
                if (this.showModelNavigation && this.navigationItems.length && this.currentSelectedNavigationModel !== 'all') {
                    return options.filter(value => value.modelId === this.currentSelectedNavigationModel);
                }

                return [];
            },

            checkMatrixExists(filterOption) {
                const matrixId = filterOption.id;
                const modelId = filterOption.modelId;

                return (this.activeItems.hasOwnProperty(modelId) && this.activeItems[modelId].hasOwnProperty(matrixId)
                    && typeof this.activeItems[modelId][matrixId] === 'object'
                    && filterOption.count !== 0);
            },

            checkModelHasSelected(modelId) {
                let hasSelected = false;

                if (this.activeItems.hasOwnProperty(modelId)) {
                    Object.keys(this.activeItems[modelId]).forEach((matrixId) => {
                        if (typeof this.activeItems[modelId][matrixId] === 'object') {
                            hasSelected = true;
                        }
                    });
                }

                return hasSelected;
            },

            resetToDefault() {
                this.currentSelectedNavigationModel = 'all';
            }
        },

        events: {
            'ModelFilter::reRenderCarousel'() {
                this.rerenderModelMatrixCarousel();
            }
        },

        components: {
            appCarouselModelMatrix
        },

        ready() {
            if (this.showModelNavigation && this.modelFilter.length) {
                this.currentSelectedNavigationModel = 'all';

                if (this.lastActiveNavigationItem) {
                    this.currentSelectedNavigationModel = this.lastActiveNavigationItem
                }
            }
        }
    });
</script>
