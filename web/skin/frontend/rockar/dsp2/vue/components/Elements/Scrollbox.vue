<template>
    <div v-el:scrollbox :class="className" :style="{ width: scrollboxWidth, height: scrollboxHeight }">
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

        methods: {
            /**
             * Calls enscroll function on scrollbox wrapper to make it scrollable
             */
            enscrollComponent() {
                jQuery(this.$els.scrollbox).enscroll({
                    horizontalScrolling: this.allowHorizontal,
                    verticalTrackClass: 'vertical-track',
                    verticalHandleClass: 'vertical-handle',
                    horizontalTrackClass: 'horizontal-track',
                    horizontalHandleClass: 'horizontal-handle'
                });
            },

            /**
             * Calculates width for scrollbar and scroll handle elements
             */
            adjustScrollbar() {
                const scrollboxHeight = jQuery(this.$els.scrollbox).height();
                const scrollbarElement = jQuery(this.$els.scrollbox).parent().children().last();
                const scrollboxScrollHeight = jQuery(this.$els.scrollbox).get(0).scrollHeight;
                const scrollbarHeight = Math.pow(scrollboxHeight, 2) / scrollboxScrollHeight;

                jQuery(scrollbarElement).height(scrollboxHeight);
                jQuery(scrollbarElement).find('.vertical-handle').get(0).style
                    .setProperty('--scroll-height', `${scrollbarHeight}px`);
            }
        },

        ready() {
            const scrollboxHeight = jQuery(this.$els.scrollbox).height();
            const scrollboxScrollHeight = jQuery(this.$els.scrollbox).get(0).scrollHeight;

            // Fix for Chrome and other browsers which have difference in element height
            // And scroll height less than 1px but more than 0px
            if ((scrollboxScrollHeight - scrollboxHeight) < 1) {
                return;
            }

            this.enscrollComponent();
            this.adjustScrollbar();
        }
    });
</script>