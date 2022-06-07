<template>
    <div class="slider-wrapper slider-single" v-el:slider>
        <a v-if="showArrows && slides.length > 1" class="slider-arrow slider-arrow-left" @click="prev()"></a>
        <a v-if="showArrows && slides.length > 1" class="slider-arrow slider-arrow-right" @click="next()"></a>

        <div class="slides">
            <template v-for="(index, slide) in slides">
                <div class="slide-block" :class="{ active: index === active }">
                    <img :src="slide.image">
                </div>
            </template>
        </div>

        <template v-if="slides.length > 1">
            <ul class="slider-nav" v-if="showNav" :style="{ marginLeft: navLeft }" v-el:dot-nav>
                <template v-for="(index, slide) in slides">
                    <li :class="{ active: index === active }" @click="active = index">
                        <span></span>
                    </li>
                </template>
            </ul>
        </template>
    </div>
</template>

<script>
    import Swipe from 'core/utils/Swipe';
    import Slider from 'core/components/Elements/Sliders/Slider';

    export default Vue.extend({
        mixins: [Slider],

        methods: {
            setCarouselHeight() {
                jQuery(this.$els.slider).css('min-height', '');

                if (parseInt(jQuery(this.$els.slider).height()) > 0) {
                    jQuery(this.$els.slider).css('min-height', jQuery(this.$els.slider).height());
                }
            }
        },

        watch: {
            slides() {
                this.setCarouselHeight();
            }
        },

        ready() {
            new Swipe(this.$el).onLeft(this.next.bind(this)).onRight(this.prev.bind(this)).run();

            jQuery(window).on('resize', () => {
                this.setCarouselHeight();
            });
        }
    });
</script>
