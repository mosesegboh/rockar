<template>
    <div class="hero-wrapper" v-el:slider>
        <div class="hero hero-left images-wrapper homepage-slider">
            <template v-for="(index, slide) in slides">
                <div class="homepage-slider-images-wrapper">
                    <div class="slider-image-wrapper"
                         :class="{fadeout: index === active.faded, animated: active.animation && index === active.slide, active: index === active.slide, next: index > active.slide && index !== active.faded, prev: index < active.slide && index !== active.faded}">
                        <div v-if="slide.vimeo_video" class="slider-video">
                            <iframe v-if="isVideoMode" :src="slide.vimeo_video" frameborder="0"></iframe>
                        </div>
                        <img v-else alt="" :src="slide.image"/>
                    </div>
                    <ul class="slider-nav mobile">
                        <template v-for="(index, slide) in slides">
                            <li :class="{active: index === active.slide}" @click="switchSlideTo(index)">
                                <span>{{{ index + 1 }}}</span>
                            </li>
                        </template>
                    </ul>
                    <button class="scroll-down-button" @click="slideDownHeroWrapper">
                        <span class="icon no-after">&nbsp;</span>
                    </button>
                </div>
            </template>
            <div class="disclaimer-wrapper" v-if="enabledMarketingMode && enabledDisclaimer">
                <div class="disclaimer-content">{{ activeSlide.disclaimer_block_text}}</div>
            </div>
            <div class="hero-inner" v-if="!enabledMarketingMode">
                <div class="hero-copy apply-box-bkg">
                    <h1><strong>{{ activeSlideTitle }}</strong></h1>
                    <p v-html="activeSlideText"></p>
                    <div class="button-wrapper">
                        <a class="button" href="#" @click="slideLink(active.slide)">
                            <span>{{ activeSlideButtonText }}</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="hero-inner marketing-block" v-else>
                <div class="hero-copy apply-box-bkg">
                    <div class="hero-header">
                        <div class="hero-main-header"><strong>{{ activeSlide.car_title }}</strong></div>
                        <div class="hero-secondary-header"><strong>{{ activeSlide.car_model }}</strong></div>
                    </div>
                    <div class="button-wrapper">
                        <a class="button" href="#" @click="slideLink(active.slide)">
                            <span>{{ activeSlideButtonText }}</span>
                        </a>
                    </div>
                    <div class="hero-detail-wrapper">
                        <div class="top-block">
                            <div class="hero-detail left-block">
                                <span class="pcp-from">{{ 'PCP From' | translate }}</span>
                                <div class="detail-header">
                                    <span class="pcp-from-block">{{ activeSlide.from_price | numberFormat '0,0' true }}</span>
                                </div>
                                <div class="term-wrapper">
                                    <div class="term-message">
                                        {{ 'A Month for' | translate }}
                                        <strong>
                                            {{ activeSlide.payment_term }}
                                            {{ 'Months' | translate }}
                                        </strong>
                                    </div>
                                    <div class="term-additional-message">
                                        {{ 'Plus Optional Final Payment' | translate }}
                                    </div>
                                </div>
                            </div>
                            <div class="hero-detail">
                                <div class="detail-header right-block">
                                    <div class="fade-block" v-show="!flippedFade" transition="fade">
                                        <span class="deposit-block"> {{ activeSlide.inital_deposite  | numberFormat '0,0' true }}</span>
                                        <span class="deposit-message">{{ 'Deposit' | translate }}</span>
                                    </div>
                                    <div class="fade-block" v-show="flippedFade" transition="fade">
                                        <span class="apr-block">{{ activeSlide.apr_rate }}</span>
                                        <span class="apr-message">{{ 'APR Rate' | translate }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bottom-block">
                            <div class="price-block">
                                <strong><span class="rockar-price price-block">{{ activeSlide.rockar_price | numberFormat '0,0' true }}</span></strong>
                                {{ 'Full Price' | translate }}
                            </div>
                            <div class="off-rrp-wrapper price-block">
                                <strong><span class="off-rrp">{{ activeSlide.off_rrp | numberFormat '0,0' true  }}</span></strong>
                                {{ 'Off RRP' | translate }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <ul class="slider-nav desktop">
                <template v-for="(index, slide) in slides">
                    <li :class="{active: index === active.slide}" @click="switchSlideTo(index)">
                        <span>{{{ index + 1 }}}</span>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</template>

<script>
    import Swipe from 'core/utils/Swipe';

    export default Vue.extend({
        props: {
            id: {
                type: String,
                required: true
            },

            width: {
                type: Number,
                required: true
            },

            height: {
                type: Number,
                required: true
            },

            slides: {
                type: Array,
                required: true
            },

            slidesInterval: {
                type: Number,
                required: true,
                default: 8
            }
        },

        data() {
            return {
                active: {
                    slide: 0,
                    animation: false,
                    faded: 0,
                    videoPlaying: false
                },
                sliderWidth: null,
                enableAutorotation: true,
                rotationInterval: null,
                rotationTimeout: null,
                renewRotation: 10,
                animationDuration: 0.5,
                flippedFade: false,
                flipTimeOut: null,
                showedSlides: [],
                isVideoMode: false
            }
        },

        computed: {
            nextImage() {
                if (this.active.slide === this.slides.length - 1) {
                    return Number(0);
                } else {
                    return this.active.slide + 1;
                }
            },

            prevImage() {
                if (this.active.slide === 0) {
                    return this.slides.length - 1;
                } else {
                    return this.active.slide - 1;
                }
            },

            activeSlide() {
                return this.slides[this.active.slide];
            },

            activeSlideTitle() {
                return this.slides[this.active.slide].image_title;
            },

            enabledMarketingMode() {
                return parseInt(this.slides[this.active.slide].is_marketing_enabled);
            },

            activeSlideText() {
                return this.slides[this.active.slide].slide_text;
            },

            activeSlideButtonText() {
                return this.slides[this.active.slide].button_text;
            },

            activeSlideButtonLink() {
                return this.slides[this.active.slide].button_link;
            },

            fadeAnimationSpeed() {
                return parseInt(this.activeSlide.animation_time);
            },

            enabledDisclaimer() {
                return parseInt(this.activeSlide.is_disclaimer_enabled);
            }
        },

        methods: {
            startAutorotation() {
                if (this.enableAutorotation) {
                    this.rotationInterval = setInterval(() => {
                        this.nextSlide(false, true);
                    }, this.slidesInterval * 1000);
                }
            },

            switchSlideTo(index) {
                if (!this.active.animation) {
                    this.stopAutorotation();
                    this.active.animation = true;
                    this.active.slide = index;
                    this.sendInitialSlideShow(index);
                    this.disableAnimation();
                    this.renewAutorotation();
                }
            },

            nextSlide(disableAutorotation = true, disableLoop = false) {
                if (!this.active.animation) {
                    if (!disableLoop) {
                        if (this.active.slide === (this.slides.length - 1)) {
                            return;
                        }
                    }
                    if (disableAutorotation) {
                        this.stopAutorotation();
                        this.renewAutorotation();
                    }
                    this.active.animation = true;
                    this.sendInitialSlideShow(this.nextImage);
                    this.active.slide = this.nextImage;
                    this.disableAnimation();
                }
            },

            prevSlide(disableAutorotation = true, disableLoop = false) {
                if (!this.active.animation) {
                    if (!disableLoop) {
                        if (this.active.slide === 0) {
                            return;
                        }
                    }
                    if (disableAutorotation) {
                        this.stopAutorotation();
                        this.renewAutorotation();
                    }
                    this.active.animation = true;
                    this.sendInitialSlideShow(this.prevImage);
                    this.active.slide = this.prevImage;
                    this.disableAnimation();
                }
            },

            disableAnimation() {
                if (this.active.animation) {
                    setTimeout(() => {
                        this.active.animation = false;
                        this.active.faded = this.active.slide;
                    }, this.animationDuration * 1000);
                }
            },

            stopAutorotation() {
                clearInterval(this.rotationInterval);
            },

            renewAutorotation() {
                clearTimeout(this.rotationTimeout);
                this.rotationTimeout = setTimeout(() => {
                    this.startAutorotation();
                }, this.renewRotation * 1000);
            },

            enableSwiping() {
                new Swipe(this.$els.slider).onLeft(this.nextSlide).onRight(this.prevSlide).run();
            },

            prepareVimeoVideoLinks() {
                this.slides.forEach((slide) => {
                    var video = slide.vimeo_video;

                    if (video) {
                        slide.vimeo_video = `https://player.vimeo.com/video/${video}?title=0&portrait=0&color=ffffff&autoplay=1&loop=1&background=1`;
                    }
                });
            },

            sendInitialSlideShow(index) {
                if (this.showedSlides.indexOf(index) === -1) {
                    this.showedSlides.push(index);

                    const homeSliderObject = {
                        'event': 'promoView',
                        'ecommerce': {
                            'promoView': {
                                'promotions': [
                                    {
                                        'id': this.slides[index].id,
                                        'name': this.slides[index].image_title,
                                        'creative': 'slider',
                                        'position': index + 1,
                                    }
                                ]
                            }
                        }
                    };
                    pushEcommerceTags(homeSliderObject);
                }
            },

            slideLink(index) {
                var _self = this;

                const homeSliderObject = {
                    'event': 'promotionClick',
                    'ecommerce': {
                        'promoClick': {
                            'promotions': [
                                {
                                    'id': this.slides[index].id,
                                    'name': this.slides[index].image_title,
                                    'creative': 'slider',
                                    'position': index + 1,
                                }
                            ]
                        }
                    },
                    'eventCallback'() {
                        document.location = _self.activeSlideButtonLink;
                    }
                };

                pushEcommerceTags(homeSliderObject);
            },

            slideDownHeroWrapper() {
                jQuery('html, body').animate({
                    scrollTop: jQuery('.hero-wrapper').offset().top + jQuery('.hero-wrapper').height()
                }, 1000);
            },
        },
        watch: {
            activeSlide: {
                immediate: true,
                handler(newSlider) {
                    if (newSlider.is_marketing_enabled && this.fadeAnimationSpeed) {
                        clearInterval(this.flipTimeOut);
                        this.flipTimeOut = setInterval(() => {
                            this.flippedFade = !this.flippedFade;
                        }, this.fadeAnimationSpeed * 1000);
                    }
                },
            }
        },

        ready() {
            this.startAutorotation();
            this.enableSwiping();
            this.prepareVimeoVideoLinks();
            this.sendInitialSlideShow(0);
            this.$nextTick(() => {
                this.isVideoMode = true;
            });
        }
    });
</script>
