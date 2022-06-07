<template>
    <div class="carousel-wrapper">
        <div v-for="slide in slides">
            <div class="carousel-block" :class="generalClass">

                <div class="carousel-image-wrapper" :style="{ height: customHeight > 0 ? customHeight + 'px' : 'auto' }">
                    <div class="carousel-vertical-image-wrapper">
                        <img :src="slide.image" :alt="slide.title">
                    </div>
                </div>

                <div class="checkbox-values" v-if="checkboxes">
                    <div v-if="disabledNotification">
                        <input type="checkbox" :id="'cs' + $index + slide.id" v-model="slide.state" @change="$emit('changed')" :disabled="slide.count <= 0 && !slide.state">
                        <label v-if="slide.count > 0 || slide.state" :for="'cs' + $index + slide.id"><span></span> {{ slide.title }}</label>
                        <app-tooltip v-else>
                            <label slot="tooltipElement" :for="'cs' + $index + slide.id"><span></span> {{ slide.title }}</label>
                            <div slot="tooltipContent" v-html="disabledNotification"></div>
                        </app-tooltip>
                    </div>
                    <div v-else>
                        <input type="checkbox" :id="'cs' + $index + slide.id" v-model="slide.state" @change="$emit('changed')" :disabled="slide.count <= 0 && !slide.state">
                        <label :for="'cs' + $index + slide.id"><span></span> {{ slide.title }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import appTooltip from 'core/components/Elements/Tooltip';

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
                default: null
            },

            customHeight: {
                required: false,
                type: Number
            },

            disabledNotification: {
                required: false,
                type: String
            },

            checkboxes: {
                required: false,
                type: Boolean,
                default: false
            },
        },

        data() {
            return {
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
                    ]
                }
            }
        },

        computed: {
            finalOptions() {
                const overrides = {};
                const result = jQuery.extend(this.defaultOptions, this.options);

                /**
                 * This is a weird fix
                 *
                 * For example we have a situation with 7 slides and 3 to show and we select the last one
                 * (initialSlide = 7) and this gets saved and passed on reiniting it. Slick initialSlide uses differently
                 * for example we start on 0, go to 1,2,3,4 and this is the end (Slick calls it hitting the edge)
                 * because the last slide is visible already and there is nothing more to slie (currentSlide +
                 * + slidersToShow = length) 4 + 3 = 7. If we try to assign initialSlide greater then the largest
                 * currentSlide possible value, Slick breaks. This is a fix for it to check of the initialSlide is
                 * greater the largest currentValue, decrease it to the largest current value. Current slick slide and
                 * the one actually clicked are two different things and can have different values.
                 */
                if (result.slidesToShow > 1 && result.initialSlide > this.slides.length - result.slidesToShow) {
                    overrides.initialSlide = this.slides.length - result.slidesToShow;
                }

                return jQuery.extend(result, overrides);
            }
        },

        events: {
            'Carousel::reInit'(options) {
                jQuery(this.$el).slick('unslick');
                jQuery(this.$el).slick(jQuery.extend(this.defaultOptions, options));
            }
        },

        ready() {
            jQuery(this.$el).slick(this.finalOptions);
            jQuery(this.$el).on('init', () => {
                jQuery(window).trigger('resize');
            });
        },

        components: {
            appTooltip
        }
    });
</script>
