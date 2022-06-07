<template>
    <div :class="className" :style="{ width: scrollboxWidth, height: scrollboxHeight }">
        <div class="scrollbox-content">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    require('core/vendor/enscroll-0.6.1.min');

    export default Vue.extend({
        props: {
            scrollboxWidth: {
                required: false,
                type: String,
                default: '100%'
            },
            scrollboxHeight: {
                required: false,
                type: String,
                default: '100%'
            },
            allowHorizontal: {
                required: false,
                type: Boolean,
                default: true
            },
            className: {
                required: false,
                type: String,
                default: 'scrollbox'
            }
        },

        ready() {
            jQuery(this.$el).enscroll({
                horizontalScrolling: this.allowHorizontal,
                verticalTrackClass: 'vertical-track',
                verticalHandleClass: 'vertical-handle',
                horizontalTrackClass: 'horizontal-track',
                horizontalHandleClass: 'horizontal-handle'
            });
        }
    });
</script>