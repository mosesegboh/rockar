<template>
    <div class="universal-carousel" :class="className">
        <slot></slot>
    </div>
</template>

<script>
export default Vue.extend({
    props: {
        className: {
            type: [String, Object, Array],
            default: ''
        },

        settings: {
            required: false,
            type: Object,
            default() {
                return {};
            }
        }
    },

    ready() {
        // Without ready callback slide width was initialized in an incorrect way
        jQuery(this.$el).ready(() => {
            jQuery(this.$el).slick(this.settings);
            jQuery(window).trigger('resize');
        });
    },

    beforeDestroy() {
        jQuery(this.$el).slick('unslick');
    },

    events: {
        'Carousel::removeFromSlider'(index, ...args) {
            jQuery(this.$el).slick('slickRemove', index);

            this.$nextTick(() => {
                this.$dispatch('Carousel::itemRemoved', index, ...args);
                this.$broadcast('Carousel::itemRemoved', index, ...args);
            });
        }
    }
});
</script>