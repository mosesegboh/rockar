<template>
    <div id="model-filter" v-show="isVisible">
        <h3 class="model-filter-title">{{ 'Choose Your MINI' | translate }}</h3>
        <app-carousel-model-matrix
            v-if="showModelMatrixCarousel"
            :slides="matrixFilter"
            :notify-me-enabled="notifyMeEnabled"
            :notify-me-url="notifyMeUrl"
            :options="sliderConfig"
        ></app-carousel-model-matrix>
    </div>
</template>

<script>
import appModelFilter from 'dsp2/components/CarFinder/ModelFilter.vue';
import appCarouselModelMatrix from 'mini2/components/CarFinder/CarouselModelMatrix.vue';

export default appModelFilter.extend({
    components: {
        appCarouselModelMatrix
    },

    data() {
        return {
            matrixAttribute: ['min_model_matrix_carousel'],
            sliderConfig: {
                slidesToShow: 4,
                initialSlide: 0,
                centerMode: false,
                focusOnSelect: false,
                responsive: [
                    {
                        breakpoint: 1600,
                        settings: {
                            slidesToShow: 3
                        }
                    },
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            }
        }
    },

    computed: {
        modelFilter() {
            const result = []

            this.filters.forEach((filter, index) => {
                if (this.matrixAttribute.indexOf(filter.code) > -1) {
                    this.getModelsList(this.filters[index].options);
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
                    matrix = this.filters[index].options;
                }
            });

            return matrix;
        }
    },

    methods: {
        getModelsList(options) {
            let count = 0;
            const array = [];

            options.forEach((value) => {
                if (array.indexOf(value.modelId) === -1) {
                    array.push(value.modelId);
                    const longTitle = `${value.modelId} ${RegExp('[0-9]+', 'g').test(value.modelId) ? ' Series' : ' Models'}`;
                    this.$set(`navigationItems[${count}]['id']`, value.modelId);
                    this.$set(`navigationItems[${count}]['title']`, value.modelId);
                    this.$set(`navigationItems[${count}]['position']`, parseInt(value.modelPosition));
                    this.$set(`navigationItems[${count}]['longTitle']`, longTitle);
                    count++;
                }

                if (value.state === true) {
                    this.lastActiveNavigationItem = value.modelId;
                }
            });
        },
    }
});
</script>
