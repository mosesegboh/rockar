<template>
    <div id="model-filter" v-show="isVisible">
        <h3 class="model-filter-title">{{ 'Choose Your Bike' | translate }}</h3>

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
                :options="slickOptionsTitles"
            >
            </app-carousel-model-matrix>

            <ul class="model-navigation-desktop">
                <template v-for="filterOption in modelFiltersShort">
                    <li class="model-list"
                        :track-by="filterOption.id"
                        :key="filterOption.id"
                        :class="{'active' : currentSelectedNavigationModel === filterOption.id}"
                    >
                        <a
                            @click="changeCurrentSelectedModel(filterOption.id)"
                            class="model-link"
                            :class="{
                                'active' : currentSelectedNavigationModel === filterOption.id,
                                'has-selected' : checkModelHasSelected(filterOption.id)
                            }"
                        >{{ filterOption.title }}</a>
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
                :options="slickOptionsModels"
            ></app-carousel-model-matrix>
        </div>
        <div v-if="currentSelectedNavigationModel === 'all'">
            <app-carousel-model-matrix
                v-if="showModelMatrixCarousel"
                :slides="modelFilter"
                :all-models="true"
                :notify-me-enabled="notifyMeEnabled"
                :notify-me-url="notifyMeUrl"
                :options="slickOptionsAll"
            ></app-carousel-model-matrix>
        </div>
    </div>
</template>
<script>
    import coreModelFilter from 'dsp2/components/CarFinder/ModelFilter';
    import appCarouselModelMatrix from 'motorrad2/components/CarFinder/CarouselModelMatrix';

    export default coreModelFilter.extend({
        components: {
            appCarouselModelMatrix
        },

        data() {
            return {
                matrixAttribute: ['mot_model_matrix_carousel'],
                modelAttribute: 'mot_series',
                slickOptionsAll: {
                    slidesToShow: 4,
                    initialSlide: 0,
                    centerMode: false,
                    responsive: [
                        {
                            breakpoint: 1200,
                            settings: {
                                slidesToShow: 3
                            }
                        },
                        {
                            breakpoint: 600,
                            settings: {
                                slidesToShow: 1
                            }
                        }
                    ]
                },
                slickOptionsModels: {
                    slidesToShow: 4,
                    initialSlide: 0,
                    centerMode: false,
                    focusOnSelect: false,
                    responsive: [
                        {
                            breakpoint: 1200,
                            settings: {
                                slidesToShow: 3
                            }
                        },
                        {
                            breakpoint: 600,
                            settings: {
                                slidesToShow: 1
                            }
                        }
                    ]
                },
                slickOptionsTitles: {
                    initialSlide: 0,
                    variableWidth: true,
                    centerMode: false,
                    focusOnSelect: false,
                    infinite: false,
                    useTransform: false,
                    responsive: [
                        {
                            breakpoint: 600,
                            settings: {
                                slidesToShow: 3
                            }
                        }
                    ]
                }
            }
        },

        methods: {
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
                        this.$set(`navigationItems[${count}]['image']`, value.image);
                        this.$set(`navigationItems[${count}]['longTitle']`, longTitle);
                        count++;
                    }

                    if (value.state === true) {
                        this.lastActiveNavigationItem = value.modelId;
                    }
                });
            }
        },

        computed: {
            modelFiltersShort() {
                const result = [
                    {
                        id: 'all',
                        title: this.translateString('All Models')
                    }
                ];

                result.push(...this.modelFilter);

                return result;
            }
        }
    });
</script>
