<template>
    <div class="carousel-wrapper">
        <div v-for="slide in slides">
            <div class="carousel-block" :class="[ generalClass, slideDisabled(slide) ? 'disabled' : '', slide.state ? 'selected' : '']" @click="!this.checkboxes ? this.handleContentClick(slide): ''">

                <div class="carousel-image-wrapper">
                    <div class="carousel-vertical-image-wrapper">
                        <img :src="slide.image" :alt="slide.title" :style="this.customHeight ? `height: ${this.customHeight}px;`: ''">
                    </div>
                </div>

                <div class="checkbox-values" v-if="checkboxes" @click="this.handleContentClick(slide)">
                    <template v-if="disabledNotification">
                        <input type="checkbox" :id="'cs' + $index + slide.id" v-model="slide.state" @change="$emit('changed', slide)" :disabled="slideDisabled(slide)">
                        <label v-if="slide.count > 0 || slide.state" :for="'cs' + $index + slide.id"><span></span> {{ slide.title }}</label>
                        <app-tooltip v-else>
                            <label slot="tooltipElement" :for="'cs' + $index + slide.id"><span></span> {{ slide.title }}</label>
                            <div slot="tooltipContent" v-html="disabledNotification"></div>
                        </app-tooltip>
                    </template>
                    <template v-else>
                        <input type="checkbox" :id="'cs' + $index + slide.id" v-model="slide.state" @change="$emit('changed', slide)" :disabled="slideDisabled(slide)">
                        <label :for="'cs' + $index + slide.id"><span></span> {{ slide.title }}</label>
                    </template>
                </div>
                <div class="additional-info" v-if="(slide.price || slide.monthlyPrice) && !slideDisabled(slide)">
                    <div class="price" v-if="slide.price">{{ 'From' | translate }} <span>{{ slide.price | numberFormat '0,0' true }}</span></div>
                    <div class="price" v-if="allowedMonthlyPayments && slide.monthlyPrice">{{ 'From' | translate }} <span>{{ Math.round(slide.monthlyPrice) | numberFormat '0,0' true }}</span> {{ 'a month' | translate }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import appCarousel from 'bmw/components/YouDrive/Carousel';

    export default appCarousel.extend({
        props: {
            selectLimit: {
                required: false,
                type: Number,
                default: 0
            },

            preSelectedSlide: {
                type: String,
                default: ''
            }
        },

        data() {
            return {
                isContentClick: true
            }
        },

        computed: {
            selectedSlides() {
                return this.slides.filter(item => item.state);
            },

            allowedMonthlyPayments() {
                const financeFilter = this.$root.$refs.financeFilter;
                // If there is no finance filter then allow data to show
                if (!financeFilter) {
                    return true;
                }
                const dissalowedOptions = [
                    'CASH'
                ];
                const paymentType = financeFilter.selectedFinanceGroupData.group_title;
                // If there is no payment type selected then allow data to show
                if (!paymentType) {
                    return true;
                }
                return !dissalowedOptions.includes(paymentType);
            }
        },

        methods: {
            slideDisabled(slide) {
                // Always return false is isValidWithOutStock is true
                if (slide.isValidWithOutStock !== undefined && slide.isValidWithOutStock) {
                    return false;
                }

                // If select limit is 0, then there is no limit
                // If select limit is 1 then adapt radio button behaviour
                // If select limit > 1, then disable other slides when limit reached
                return (slide.count <= 0 && !slide.state) || (this.selectLimit > 1 && this.selectedSlides.length >= this.selectLimit && !slide.state);
            },

            handleContentClick(slide) {
                if (!this.slideDisabled(slide)) {
                    slide.state = !slide.state;
                    this.$emit('changed', slide);
                }
            },

            updateIsContentClick() {
                if (this.$el.slick) {
                    if (this.$el.slick.activeBreakpoint) {
                        this.isContentClick = this.$el.slick.breakpointSettings[this.$el.slick.activeBreakpoint].contentClick;
                    } else {
                        this.isContentClick = this.$el.slick.options.contentClick;
                    }
                }
            },

            /**
             * To simulate 'radio' input functionality,
             * which for some reason doesn't seem to work well with slick, all other slides are de-selected
             *
             * @param id
             */
            deselectOtherSlides(id) {
                this.slides.map((slide) => {
                    if (slide.id !== id) {
                        slide.state = false;
                    }
                });
            },

            /**
             * Gets index for selected model slide
             */
            getActiveModelSlideIndex(reduceIndex = false) {
                // If 1 model is selected
                if (this.selectLimit === 1 && this.selectedSlides.length === 1) {
                    const model = this.selectedSlides[0];
                    let slideIndex = this.slides.indexOf(model);

                    // For desktop need to reduce ny one in order to get slide centered
                    if (reduceIndex) {
                        slideIndex = slideIndex - 1;
                    }

                    return slideIndex;
                }

                // If any other amount of models is selected, then return false
                return false;
            },

            /**
             * Center selected model on slider
             */
            centerSelectedModel() {
                const centerMode = jQuery(this.$el).slick('slickGetOption', 'centerMode');
                const slideIndex = this.getActiveModelSlideIndex(!centerMode);

                if (slideIndex !== false) {
                    if (this.selectedSlides.length === 1) {
                        jQuery(this.$el).slick('slickGoTo', slideIndex);
                    }
                }
            }
        },

        events: {
            'changed'(slide = false) {
                // Radio input behaviour
                if (this.selectLimit === 1 && slide) {
                    this.deselectOtherSlides(slide.id);
                }
                this.$dispatch('CarouselModel::selectedSlidesUpdated', this.selectedSlides);

                // added timeout as 'selected' class is added to template with small delay
                setTimeout(() => {
                    if (this.selectedSlides.length) {
                        jQuery('.carousel-block').each((index, el) => {
                            if (jQuery(el).hasClass('selected')) {
                                jQuery(el).removeClass('gray-out');
                            } else {
                                jQuery(el).addClass('gray-out');
                            }
                        });
                    } else {
                        jQuery('.carousel-block').each((index, el) => {
                            jQuery(el).removeClass('gray-out');
                        });
                    }
                }, 100);
            },

            'Carousel::reInit'() {
                this.centerSelectedModel();
            }
        },

        beforeDestroy() {
            jQuery(window).unbind('resize', this.updateIsContentClick);
            jQuery(this.$el).slick('unslick');
        },

        ready() {
            this.updateIsContentClick();
            jQuery(window).bind('resize', this.updateIsContentClick);
            jQuery(window).trigger('resize');

            // preselect slide
            if (this.preSelectedSlide) {
                this.slides.forEach((slide) => {
                    if (slide.id === this.preSelectedSlide) {
                        this.handleContentClick(slide);
                    }
                });
            }

            // If limit exceeded, then de-select all slides
            if (this.selectLimit === 1 && this.selectedSlides.length > 1) {
                this.deselectOtherSlides(0);
            }

            // Center selected model initially
            this.centerSelectedModel();
        },
    });
</script>
