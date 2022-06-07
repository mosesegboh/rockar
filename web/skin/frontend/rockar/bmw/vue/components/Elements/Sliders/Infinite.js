export default {
    props: {
        slides: {
            required: true,
            type: Array
        },
        originalSlides: {
            type: Array
        },
        showNav: {
            required: false,
            type: Boolean,
            default: false
        },

        showArrows: {
            required: false,
            type: Boolean,
            default: true
        },

        initSlidesToShow: {
            required: false,
            type: Number,
            default: 1
        },

        activeId: {
            required: false,
            type: [Number, String, Boolean],
            default: false
        }
    },

    data() {
        return {
            navLeft: 0,
            active: 0,
            slided: 0,
            slidesToShow: this.initSlidesToShow,
            sliderLeftOffset: 0,
            sliderWidth: 0,
            center: false,
            initAnimation: true,
            slidesOffset: 1,
            animationInProgress: false
        }
    },

    computed: {
        slideWidth() {
            return `${100 / this.slidesToShow}%`;
        }
    },

    methods: {
        next() {
            if (!this.animationInProgress) {
                this.animationInProgress = true;
                this.slided = this.getSlided(this.slided + this.slidesOffset);
                this.sliderLeftOffset = this.getSliderLeftOffset();

                if (this.slided + this.slidesOffset > this.slides.length - this.slidesToShow) {
                    setTimeout(() => {
                        this.initAnimation = false;
                        this.slided = this.slidesToShow;
                        this.sliderLeftOffset = this.getSliderLeftOffset();

                        setTimeout(() => {
                            this.initAnimation = true;
                            this.animationInProgress = false;
                        }, 50);
                    }, 500);
                } else {
                    this.animationInProgress = false;
                }
            }
        },

        prev() {
            if (!this.animationInProgress) {
                this.animationInProgress = true;
                this.slided = this.getSlided(this.slided - this.slidesOffset);
                this.sliderLeftOffset = this.getSliderLeftOffset();

                if (this.slided - this.slidesOffset < 0) {
                    setTimeout(() => {
                        this.initAnimation = false;
                        this.slided = this.slides.length - this.slidesToShow * 2;
                        this.sliderLeftOffset = this.getSliderLeftOffset();

                        setTimeout(() => {
                            this.initAnimation = true;
                            this.animationInProgress = false;
                        }, 50);
                    }, 500);
                } else {
                    this.animationInProgress = false;
                }
            }
        },

        getSlided(newPos) {
            const maxPos = this.slides.length - this.slidesToShow;
            const minPos = 0;

            if (newPos >= minPos && newPos <= maxPos) {
                return newPos;
            } else if (newPos < minPos) {
                return minPos;
            } else if (newPos > maxPos) {
                return maxPos;
            }
        },

        getSliderLeftOffset() {
            if (this.slidesToShow === 1) return 0;
            const slideWidth = this.$el.querySelector('.slide-block').getBoundingClientRect().width;
            const slideLeftOffset = `-${this.slided * slideWidth}px`;
            return slideLeftOffset;
        },
        createSlides() {
            this.slides = this.originalSlides;

            if (this.slides.length - 1 < this.slidesToShow) {
                return this.slides;
            }

            const left = [];
            const right = [];
            const length = this.slides.length - 1;

            for (let i = 0; i < this.slidesToShow; i++) {
                left.unshift(this.slides[length - i]);
                right.push(this.slides[i]);
            }

            this.slides = this.slides.concat(right);
            this.slides = left.concat(this.slides);
        }
    },

    created() {
        this.createSlides();

        if (this.activeId !== false) {
            this.slides.forEach((slide, index) => {
                if (slide.id === this.activeId) {
                    this.active = index;

                    if (this.slidesToShow - 1 < index
                        && this.slides.length - this.slidesToShow - 2 > index
                    ) {
                        this.slided = this.getSlided(index);
                    }
                }
            });
        }

        this.center = this.slidesToShow > this.slides.length;
    },

    ready() {
        this.sliderLeftOffset = this.getSliderLeftOffset();

        if (this.showNav) {
            this.navLeft = `-${jQuery(this.$els.dotNav).width() / 2}px`;
        }
    }
}
