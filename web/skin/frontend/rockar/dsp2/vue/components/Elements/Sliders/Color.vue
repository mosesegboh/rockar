<template>
    <div class="slider-wrapper slider-color" v-el:slider>
        <p class="color-notice">{{ 'Your chosen colour is'  | translate}} <strong v-if="slides[active]">{{ slides[active].description }}</strong></p>
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <div v-show="showSliderArrows">
            <a class="slider-arrow slider-arrow-left" @click="prev()"></a>
            <a class="slider-arrow slider-arrow-right" @click="next()"></a>
        </div>

        <div class="slides">
            <div class="slides-wrapper" :class="{ animation: initAnimation }" :style="{ left: sliderLeftOffset, justifyContent: center ? 'center' : 'flex-start' }">
                <template v-for="(index, slide) in slides" track-by="$index">
                    <div class="slide-block" :class="{ active: slide.id === activeId }" :style="{ flexBasis: slideWidth }" @click="select(slide.id, slide.url, index)">
                        <div class="slide-image">
                            <img :src="slide.image">
                            <div class="slide-selected" v-if="slide.id === activeId"></div>
                        </div>
                        <p class="slide-price">{{ slide.price | numberFormat '0,0.00' true }}</p>
                        <div class="slide-info">
                            <p>{{ formatLeadTime(slide.leadTime) }}</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    import Swipe from 'core/utils/Swipe';
    import Slider from 'core/components/Elements/Sliders/Infinite';

    export default Vue.extend({
        mixins: [Slider],

        props: {
            ajaxLoading: {
                required: false,
                type: Boolean
            }
        },

        data() {
            return {
                tempSlide: null,
                configurator: this.$parent
            }
        },

        computed: {
            showSliderArrows() {
                return this.slides.length > this.slidesToShow;
            }
        },

        methods: {
            select(id, url, index) {
                this.ajaxLoading = true;
                this.tempSlide = id;
                this.active = index;
                this.prevCar = this.configurator.selectedCar;

                this.$http({
                    url,
                    method: 'POST'
                }).then(this.updateConfiguration, this.ajaxFail);
            },

            updateConfiguration(resp) {
                if (resp.data.success) {
                    if (resp.data.media.interior.length && resp.data.media.exterior.length) {
                        this.updateProduct(resp.data.product);
                        this.updatePreConfiguredCars(resp.data.vehicles);
                        this.updateMedia(resp.data.media);
                        this.updateAccessories(resp.data.accessories);
                        this.$dispatch('Main::updateFinanceQuote', resp);

                        setTimeout(() => { // a little timeout to compensate media image initialising
                            if (this.prevCar) {
                                this.$dispatch('Configurator::preSelectConfiguration', this.prevCar);
                            } else {
                                this.$dispatch('Configurator::preSelectConfiguration');
                            }

                            this.ajaxLoading = false;
                        }, 10);

                        this.activeId = this.tempSlide;
                        this.tempSlide = null;
                    } else {
                        console.error('Update Configuration: No Images Found');
                    }
                }
            },

            ajaxFail(resp) {
                this.ajaxLoading = false;
                console.error(resp.data.message);
                this.tempSlide = null;
            },

            updateMedia(data) {
                if (data) {
                    this.$emit('update360', data);
                }
            },

            updateProduct(data) {
                if (data) {
                    this.$emit('product-update', data);
                }
            },

            updateAccessories(data) {
                if (data) {
                    this.$emit('accessories-update', data);
                }
            },

            updatePreConfiguredCars(data) {
                if (data) {
                    this.$emit('preconfigured-update', data);
                }
            },

            centerActiveSlide() {
                for (let index = 0; index < this.slides.length; index++) {
                    if (this.slides[index].id === this.activeId) {
                        this.active = index;
                        this.slided = this.getSlided(index);
                        this.sliderLeftOffset = this.getSliderLeftOffset();
                        break;
                    }
                }
            },

            slidesUpdate() {
                if (window.innerWidth <= 500) {
                    this.slidesToShow = 2;
                } else if (window.innerWidth <= 600) {
                    this.slidesToShow = 3;
                } else if (window.innerWidth <= 700) {
                    this.slidesToShow = 4;
                } else if (window.innerWidth <= 800) {
                    this.slidesToShow = 5;
                } else {
                    if (this.initSlidesToShow > this.slides.length) {
                        this.slidesToShow = this.slides.length;
                    } else {
                        this.slidesToShow = this.initSlidesToShow;
                    }
                }
                this.slided = this.getSlided(this.active);
                if ((this.slides.length - this.slidesToShow) > 0) {
                    if (this.slided + this.slidesOffset > this.slides.length - this.slidesToShow) {
                        this.slided = this.slidesToShow;
                    }
                }

                this.center = this.slidesToShow >= this.slides.length;

                this.createSlides();

                setTimeout(() => {
                    this.centerActiveSlide();
                }, 500);
            },

            formatLeadTime(leadTime) {
                return leadTime.replace('Delivery', '');
            }
        },

        events: {
            'CarouselColor::paymentUpdated'() {
                this.$nextTick(() => {
                    var active = this.slides[this.active];
                    this.select(active.id, active.url, this.active);
                });
            }
        },

        ready() {
            this.slidesUpdate();
            window.addEventListener('resize', this.slidesUpdate.bind(this));
            new Swipe(this.$el).onLeft(this.next.bind(this)).onRight(this.prev.bind(this)).run();
        }
    });
</script>
