<template>
    <div class="carousel-wrapper">
        <div v-for="(index, slide) in slidesData" :key="slide.id">
            <div class="carousel-block"
                 v-if="!isMobile"
                 :class="{ 'disabled' : slide.count === 0 }"
                 @click="allModels ? changeModel(slide.id) : ''"
            >
                <div class="carousel-image-wrapper">
                    <div class="carousel-vertical-image-wrapper">
                        <label :for="`cs${$index + slide.id}`">
                            <img :src="slide.image" :alt="allModels ? slide.longTitle : slide.title">
                        </label>
                    </div>
                </div>
                <p>{{ slide.longTitle }}</p>

                <div class="checkbox-values" v-if="checkboxes">
                    <div class="carousel-checkbox-wrapper out-of-stock" v-if="slide.count === 0">
                        <input type="checkbox" :id="`cs${$index + slide.id}`" :disabled="true">
                        <label :for="`cs${$index + slide.id}`">
                            {{ slide.title }}
                        </label>
                        <div class="checkbox-info out-of-stock">
                            <p>{{ "Model out of stock or not within budget" | translate }}</p>
                        </div>
                        <a class="notify-me" v-if="notifyMeEnabled" href="javascript:void(0);" @click="redirectToNotifyMe()">
                            <span>{{ 'Notify me' | translate }}</span>
                        </a>
                    </div>
                    <div class="carousel-checkbox-wrapper" v-else>
                        <input
                            v-model="slides[index].state"
                            type="checkbox"
                            :id="`cs${$index + slide.id}`"
                            @change="checkMatrixExists(slide)"
                        >
                        <label :for="`cs${$index + slide.id}`"><span></span>{{ slide.title }}</label>
                        <div class="checkbox-info">
                            <p>{{ slide.count }} {{ "Results" | translate }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="carousel-block">
                <div @click="changeModel(slide.id)" :class="{
                    'active' : selected === slide.id,
                    'has-selected' : this.hasSelected(slide.id)
                }">
                    <p class="dsp2-label">{{ slide.title }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default Vue.extend({
    props: {
        slides: {
            required: true,
            type: Array
        },

        options: {
            required: false,
            type: Object,
            default() {
                return {};
            }
        },

        generalClass: {
            required: false,
            type: String,
            default: ''
        },

        checkboxes: {
            required: false,
            type: Boolean,
            default: false
        },

        allModels: {
            required: true,
            type: Boolean
        },

        isMobile: {
            required: false,
            type: Boolean,
            default: false
        },

        selected: {
            required: false,
            type: String,
            default: ''
        },

        currentId: {
            required: false,
            type: String,
            default: ''
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
            slidesData: this.slides,
            defaultOptions: {
                slidesToShow: 3,
                slidesToScroll: 1,
                focusOnSelect: true,
                centerMode: true,
                swipeToSlide: true,
                variableWidth: false,
                infinite: false,
                centerPadding: 0,
                initialSlide: 1,
                prevArrow: '<span class="slick-prev">Previous</span>',
                nextArrow: '<span class="slick-next">Next</span>',
                responsive: [
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 3
                        }
                    }
                ],
                navAll: {
                    id: 'all',
                }
            }
        }
    },

    methods: {
        changeModel(id) {
            this.$parent.changeCurrentSelectedModel(id);
        },

        checkMatrixExists(slide) {
            this.$parent.checkMatrixExists(slide);

            if (this.$root.$refs.carFinder.currentStep === 1) {
                // Update grid if we are at corresponding step
                this.$dispatch('CarFinder::updateFilters');
            }
        },

        hasSelected(modelId) {
            return this.$root.$refs.modelFilter.checkModelHasSelected(modelId);
        },

        redirectToNotifyMe() {
            if (this.notifyMeUrl) {
                window.open(this.notifyMeUrl, '_blank');
            }
        }
    },

    computed: {
        finalOptions() {
            const overrides = {};
            const result = jQuery.extend(this.defaultOptions, this.options);

            return jQuery.extend(result, overrides);
        }
    },

    ready() {
        jQuery(this.$el).on('init', () => {
            jQuery(window).trigger('resize');
        });
        jQuery(this.$el).slick(this.finalOptions);
    }
});
</script>

