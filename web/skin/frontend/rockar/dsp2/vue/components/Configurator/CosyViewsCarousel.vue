<template>
    <div class="carousel-wrapper">
        <div v-for="slide in slides">
            <div class="carousel-block" :class="generalClass">
                <div
                    @mouseup="mouseup($index)"
                    @mousedown="mousedown()"
                    @mousemove="mousemove()"
                    class="carousel-image-wrapper"
                    :style="{ height: customHeight ? customHeight + 'px' : 'auto' }"
                >
                    <img class="carousel-image" :src="slide.image">
                    <span class="magnifying-glass"></span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import appConfiguratorCarouselImages from 'core/components/Configurator/CarouselImages';

    export default appConfiguratorCarouselImages.extend({
        props: {
            show: {
                required: false,
                type: Boolean,
                default: false
            },

            index: {
                required: false,
                type: Number,
                default: 0
            }
        },

        data() {
            return {
                drag: false
            }
        },

        methods: {
            mousedown() {
                this.drag = false;
            },

            mousemove() {
                this.drag = true;
            },

            mouseup(index) {
                if (!this.drag) {
                    this.index = index;
                    this.show = true;
                }
            }
        }
    });
</script>
