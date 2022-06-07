<template>
    <div class="carousel-wrapper" v-bind:class="{ 'nav-carousel': isNavigation }">
        <div v-for="slide in slides">
            <div class="carousel-block" :class="generalClass">

                <div class="carousel-image-wrapper">
                    <div class="carousel-vertical-image-wrapper">
                        <a v-on:mouseup="openCarImage">
                            <img :alt="slide.title" :data-lazy="slide.image">
                        </a>
                    </div>
                </div>

                <div class="checkbox-values" v-if="checkboxes">
                    <div v-if="disabledNotification">
                        <input type="checkbox" :id="'cs' + $index + slide.id" v-model="slide.state"
                               @change="$emit('changed')" :disabled="slide.count <= 0 && !slide.state">
                        <label v-if="slide.count > 0 || slide.state" :for="'cs' + $index + slide.id">
                            <span></span>
                            {{slide.title }}
                        </label>
                        <app-tooltip v-else>
                            <label slot="tooltipElement" :for="'cs' + $index + slide.id"><span></span> {{ slide.title }}</label>
                            <div slot="tooltipContent" v-html="disabledNotification"></div>
                        </app-tooltip>
                    </div>
                    <div v-else>
                        <input type="checkbox" :id="'cs' + $index + slide.id" v-model="slide.state"
                               @change="$emit('changed')" :disabled="slide.count <= 0 && !slide.state">
                        <label :for="'cs' + $index + slide.id"><span></span> {{ slide.title }}</label>
                    </div>
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
            checkboxes: {
                required: false,
                type: Boolean,
                default: false
            },
            generalClass: {
                required: false,
                type: String,
                default: null
            },
            customHeight: {
                required: false,
                type: String,
                default: 0
            },
            disabledNotification: {
                required: false,
                type: String
            },
            isNavigation: {
                required: false,
                type: Boolean,
                default: false
            },
            targetSlider: {
                required: false,
                type: String
            },

            ignoreClick: {
                required: false,
                type: Boolean,
                default: false
            }
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
                },
                showMobileArrows: false,
                wrapperHeight: 150,
                slickInstance: null,
            }
        },

        methods: {
            updateCarouselWrapperHeight() {
                if (this.customHeight) {
                    this.wrapperHeight = this.customHeight;
                } else {
                    const windowW = jQuery(window).width();
                    this.wrapperHeight = 245;

                    if (windowW <= 830 && windowW > 600) {
                        this.wrapperHeight = 150;
                    } else if (this.device === 'tablet') {
                        this.wrapperHeight = 210;
                    }
                }
            },

            prepareMobile() {
                if (typeof(this.options.mobileNavigation) && this.options.mobileNavigation) {
                    this.defaultOptions.responsive[0].breakpoint = 319;
                }
            },

            onResize() {
                if (this.slickInstance) {
                    this.slickInstance.checkResponsive();
                }
            },

            openCarImage(mouseEvent) {
                let isClick = false;

                if (this.ignoreClick) {
                    return;
                }

                jQuery('.carousel-vertical-image-wrapper').on('mousedown', () => {
                    isClick = true;
                }).on('mousemove', () => {
                    isClick = false;
                }).on('click', () => {
                    if (isClick) {
                        this.$dispatch('ProductPod::selectCar', mouseEvent);
                    }
                });
            },
        },

        events: {
            'Carousel::reInit'() {
                jQuery(this.$el).slick('unslick');
                if (this.isNavigation && this.targetSlider) {
                    jQuery(this.$el).slick(jQuery.extend(this.defaultOptions, this.options, { asNavFor: this.targetSlider }))
                } else {
                    jQuery(this.$el).slick(jQuery.extend(this.defaultOptions, this.options));
                }
            }
        },

        ready() {
            this.prepareMobile();
            this.updateCarouselWrapperHeight();
            jQuery(window).bind('resize', () => {
                this.updateCarouselWrapperHeight();
            });

            jQuery(this.$el).on('init', () => {
                jQuery(window).trigger('resize');
            });

            if (this.isNavigation && this.targetSlider) {
                jQuery(this.$el).slick(jQuery.extend(this.defaultOptions, this.options, { asNavFor: this.targetSlider }))
            } else {
                jQuery(this.$el).slick(jQuery.extend(this.defaultOptions, this.options));
            }

            this.onResize();

            if (this.$root.isSafari()) { // Resize on safari not working as well as excepted
                let lastZoom = window.innerWidth;

                setInterval(() => {
                    const newZoom = window.innerWidth;
                    if (newZoom !== lastZoom) {
                        lastZoom = newZoom;
                        this.onResize();
                    }
                }, 50);
            } else {
                let resizeTimerPointer = null;

                jQuery(window).resize(() => {
                    clearTimeout(resizeTimerPointer);

                    resizeTimerPointer = setTimeout(() => {
                        this.onResize();
                    }, 50);
                });
            }
        }
    });
</script>
