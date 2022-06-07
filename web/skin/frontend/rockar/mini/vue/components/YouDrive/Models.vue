<template>
    <div class="you-drive-model">

        <div class="car-list">
            <template v-for="(index, item) in modelsData">
                <div class="car-model" v-for="(index, model) in item">
                    <div :class="[{ 'not-available': !isAvailable( model), 'greyed-out': !canSelectModel(model) }]">
                        <div :class="{ 'padded-images': model.images_from_pdc }">
                            <img :src="model.image" :alt="model.subtitle">
                        </div>

                        <div class="model-options">
                            <strong>
                                <p class="options-title">{{ model.title | convertNCR }}</p>
                                <p class="options-title">{{ model.subtitle | convertNCR }}</p>
                            </strong>
                        </div>
                        <div class="checkbox-block">
                            <input
                                type="checkbox" id="model_{{ storeId }}_{{ model.youdriveId }}"
                                :checked="isModelSelected(model)"
                                @click="toggleVariant(model)"
                            >
                            <label for="model_{{ storeId }}_{{ model.youdriveId }}"><span></span></label>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    import perfectScrollbar from 'perfect-scrollbar';

    export default Vue.extend({
        props: {
            models: {
                required: false,
                type: Array,
                default() {
                    return [];
                }
            },

            storeId: {
                required: false,
                type: String,
                default: ''
            },

            selectedModelsTitles: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            selectedVehicles: {
                required: true,
                type: Array,
            },

            selectModelsLimit: {
                required: false,
                type: Number,
                default: 1
            },

            availableIds: {
                required: true,
                type: Array,
            },

            localStoreCode: {
                required: true,
                type: String
            },

            localStore: {
                required: true,
                type: Object
            }
        },

        data() {
            return {
                variants: this.models,
                selectVariantsLimit: this.selectModelsLimit
            }
        },

        computed: {
            modelsData() {
                const data = {};

                if (this.variants) {
                    Object.keys(this.selectedModelsTitles).forEach((key) => {
                        data[key] = this.variants.filter((obj) => obj.model === key);
                    });
                }

                return this.sortedCarModels(data);
            },

            /**
             * Track model selection via variable changing handle
             *
             * @returns {function(*): boolean}
             */
            isModelSelected() {
                return (model) => this.selectedVehicles.includes(model.youdriveId);
            }
        },

        methods: {
            canSelectModel(model) {
                const selectedCount = this.selectedVehicles.length;

                if (!selectedCount) {
                    return true;
                } else if (
                    !this.isModelSelected(model)
                    && selectedCount >= this.selectVariantsLimit
                ) {
                    return false;
                }

                return this.isAvailable(model);
            },

            // Reorders the models, placing models that are unavailable at the selected store at the end
            sortedCarModels(data) {
                const dataNew = {};
                Object.keys(data).forEach((key) => {
                    dataNew[key] = data[key].sort((a, b) => {
                        const aSort = a.assigned_to === this.localStoreCode;
                        const bSort = b.assigned_to === this.localStoreCode;
                        return (!aSort && bSort) ? 1 : -1;
                    });
                });
                return dataNew;
            },

            selectModel(storeId, model, status) {
                this.$dispatch('YouDrive::selectVariant', storeId, model, status);
            },

            toggleVariant(model) {
                if (this.variants) {
                    this.variants.forEach((item, index) => {
                        if (parseInt(item.youdriveId) === parseInt(model.youdriveId)) {
                            this.selectModel(this.storeId, this.variants[index], !this.isModelSelected(item));
                        } else if (this.isModelSelected(item)) {
                            this.selectModel(this.storeId, this.variants[index], false);
                        }
                    });
                }

                // check browser is Edge
                if (this.$root.isEdge()) {
                    setTimeout(this.edgeRepaint, 100);
                }
            },

            getModelsTitle(modelId) {
                const title = 'models available at this location:';

                if (!this.selectedModelsTitles[modelId]) {
                    return title;
                }

                return `${this.selectedModelsTitles[modelId]} ${title}` || title;
            },

            isAvailable(obj) {
                return this.availableIds.includes(obj.youdriveId);
            },

            // this compensates for Edge not repainting
            edgeRepaint() {
                jQuery('.checkbox-block').find('span').addClass('edgeRepaint');
                jQuery('.checkbox-block').find('span').removeClass('edgeRepaint');
            }
        },

        events: {
            'YouDrive::updateModels'() {
                setTimeout(() => {
                    jQuery('.vehicle-options-wrapper').each((key, item) => {
                        perfectScrollbar.initialize(
                            item,
                            {
                                suppressScrollX: true,
                                wheelPropagation: true
                            }
                        );
                    });
                }, 1);
            },

            'YouDrive::toggleVariant'(variant) {
                this.toggleVariant(variant);
            }
        }
    });
</script>
