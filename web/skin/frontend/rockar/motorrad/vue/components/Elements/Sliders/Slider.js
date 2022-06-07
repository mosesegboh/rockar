export default {
    props: {
        slides: {
            required: true,
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
            center: false
        }
    },

    computed: {
        slidesOffset() {
            if (this.slidesToShow === 1) return 0;
            return (this.slidesToShow - 1) / 2;
        },

        slideWidth() {
            return `${100 / this.slidesToShow}%`;
        }
    },

    methods: {
        prev() {
            if (this.active > 0) {
                this.active -= 1;
            } else {
                this.active = this.slides.length - 1;
            }
        },

        slidePrev() {
            this.slided = this.getSlided(this.slided - this.slidesOffset);
            this.sliderLeftOffset = this.getSliderLeftOffset();
        },

        next() {
            if (this.active < this.slides.length - 1) {
                this.active += 1;
            } else {
                this.active = 0;
            }
        },

        slideNext() {
            this.slided = this.getSlided(this.slided + this.slidesOffset);
            this.sliderLeftOffset = this.getSliderLeftOffset();
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
            this.$nextTick(() => {
                const slideWidth = this.$el.querySelector('.slide-block').getBoundingClientRect().width;
                const slideLeftOffset = `-${this.slided * slideWidth}px`;
                return slideLeftOffset;
            });
        }
    },

    created() {
        if (this.activeId !== false) {
            this.slides.forEach((slide, index) => {
                if (slide.id === this.activeId) {
                    this.active = index;
                    this.slided = this.getSlided(index);
                }
            });
        }

        this.center = this.slidesToShow >= this.slides.length;
    },

    ready() {
        this.sliderLeftOffset = this.getSliderLeftOffset();

        if (this.showNav) {
            this.navLeft = `-${jQuery(this.$els.dotNav).width() / 2}px`;
        }
    }
}
