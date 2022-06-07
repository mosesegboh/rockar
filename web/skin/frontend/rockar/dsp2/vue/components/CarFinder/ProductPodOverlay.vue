<template>
    <div slot="content">
        <div class="product-pod-attributes-overlay mobile" v-show="attributeOverlay && productId === parseInt(lastAttributeOverlayProductId)">
            <div class="attribute-close-mobile" @click="attributeOverlayClose">
                {{ 'Specifications' | translate }}
                <span class="close">&nbsp;</span>
            </div>
            <div class="attribute-view-select" v-show="carOptionsVisible">
                <app-select
                    :options="getSelectOptions"
                    @select="setAttributeType">
                </app-select>
            </div>
            <div class="image-block">
                <img v-for="image in images" alt="{{ image.title }}" v-bind:src="image.images[0].image_path">
            </div>
            <div class="attribute-block">
                <div
                    class="attribute-section"
                    :class="{ 'with-compare-products' : isCompareProductAdded }"
                    v-el:attributes-list
                >
                    <ul class="feature-list" v-show="computedKeyFeatures.length">
                        <li v-for="keyFeature in computedKeyFeatures" track-by="$index" class="attribute" :key="keyFeature.key">
                            <div class="attribute-label">{{ keyFeature.key }}</div>
                            <div class="attribute-value" v-if="keyFeature.value">{{ keyFeature.value }}</div>
                        </li>
                    </ul>
                    <ul class="attribute-list">
                        <li v-for="option in customOptions" track-by="$index" :key="option.option_type">
                            <div v-if="parseInt(option['product_id']) === productId">
                                <div v-if="option['option_type'] === attributeType">
                                    <div class="row">
                                        <div class="option-title" v-html="option.title"></div>
                                        <div class="option-price">
                                            {{ option['price'] | numberFormat '0,0.00' true }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="row option-total-price">
                        <div class="option-title">{{ 'Total specification summary' | translate }}</div>
                        <div class="option-price">
                            {{ customOptionsTotal[attributeType] | numberFormat '0,0.00' true }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="product-pod-attributes-overlay" v-show="attributeOverlay">
            <div class="attribute-view-switcher" v-show="carOptionsVisible">
                <template v-for="(section, total) in customOptionsTotal">
                    <button
                        track-by="$index"
                        :key="section"
                        v-if="total !== false" class="view-switch"
                        :class="{'active': attributeType === section}"
                        @click="setAttributeType(section)">
                          {{ section | translate }}
                    </button>
                </template>
            </div>
            <div class="image-block">
                <img v-for="image in images" alt="{{ image.title }}" v-bind:src="image.images[0].image_path">
            </div>
            <div class="attribute-block">
                <div class="attribute-section" v-el:attributes-list>
                    <ul class="feature-list" v-show="computedKeyFeatures.length">
                        <li v-for="keyFeature in computedKeyFeatures" track-by="$index" class="attribute" :key="keyFeature.key">
                            <div class="attribute-label">{{ keyFeature.key }}</div>
                            <div class="attribute-value" v-if="keyFeature.value">{{ keyFeature.value }}</div>
                        </li>
                    </ul>
                    <ul class="attribute-list">
                        <li v-for="option in customOptions" track-by="$index" :key="option.option_type">
                            <div v-if="parseInt(option['product_id']) === productId">
                                <div v-if="option['option_type'] === attributeType">
                                    <div class="row">
                                        <div class="option-title" v-html="option.title"></div>
                                        <div class="option-price">
                                            {{ option['price'] | numberFormat '0,0.00' true }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="row option-total-price">
                        <div class="option-title">{{ 'Total specification summary' | translate }}</div>
                        <div class="option-price">
                            {{ customOptionsTotal[attributeType] | numberFormat '0,0.00' true }}
                        </div>
                    </div>
                </div>
            </div>
            <button class="pod-specs-button"
                    :class="{'active': attributeOverlay}"
                    @click="attributeOverlayToggle">
                      {{ 'Less Specifications' | translate }}
            </button>
        </div>
    </div>
</template>

<script>
import appSelect from 'dsp2/components/Elements/Select';
import perfectScrollbar from 'perfect-scrollbar';
import translateString from 'core/filters/Translate';

export default Vue.extend({
    props: {
        attributeOverlay: {
            required: true,
            type: Boolean
        },
        carOptionsVisible: {
            required: true,
            type: Boolean
        },
        customOptionsTotal: {
            required: true,
            type: Object
        },
        attributeType: {
            required: true,
            type: String
        },
        images: {
            required: true,
            type: Array
        },
        computedKeyFeatures: {
            required: true,
            type: Array
        },
        customOptions: {
            required: true,
            type: Array
        },
        productId: {
            required: true,
            type: Number
        },
        lastAttributeOverlayProductId: {
            required: true,
            type: Number
        }
    },

    components: {
        appSelect
    },

    methods: {
        attributeOverlayClose() {
            this.$dispatch('ProductPodOverlay::attributeOverlayClose');
        },

        attributeOverlayToggle() {
            this.$dispatch('ProductPodOverlay::attributeOverlayToggle');
        },

        setAttributeType(data) {
            this.$dispatch('ProductPodOverlay::setAttributeType', data);
        },

        translateString
    },

    computed: {
        getSelectOptions() {
            const customOptions = this.customOptionsTotal;

            const options = [];

            Object.keys(customOptions).forEach((key) => {
                if (customOptions[key] !== false) {
                    options.push({
                        title: key
                    });
                }
            });

            return options;
        },

        isCompareProductAdded() {
            return this.$root.$refs.carCompare.carCompareLength > 0;
        }
    },

    ready() {
        perfectScrollbar.initialize(
            this.$els.attributesList,
            {
                suppressScrollX: true,
                wheelPropagation: true
            }
        );
    },
});
</script>
