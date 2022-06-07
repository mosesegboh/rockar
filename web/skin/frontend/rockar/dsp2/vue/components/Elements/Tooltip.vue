<template>
    <div class="tooltip-wrapper">
        <div v-el:tooltip-trigger>
            <slot name="tooltipElement"></slot>
        </div>
        <div v-show="false" v-el:tooltip-content>
            <slot :class="[className ? className + '-content' : 'tooltip-element-content']" name="tooltipContent"></slot>
        </div>
    </div>
</template>
<script>
    export default Vue.extend({
        props: {
            tooltipWidth: {
                required: false,
                type: Number,
                default: 300
            },
            tooltipPosition: {
                required: false,
                type: String,
                default: 'top-left'
            }
        },

        data() {
            return {
                interval: null
            }
        },

        methods: {
            updateTooltipPosition() {
                this.interval = setInterval(() => {
                    jQuery(this.$els.tooltipTrigger).tooltipster('reposition');
                }, 50);

                setTimeout(() => {
                    clearInterval(this.interval);
                }, 500);
            }
        },

        ready() {
            jQuery(this.$els.tooltipTrigger).tooltipster({
                content: (typeof this.$els.tooltipContent !== 'undefined') ? this.$els.tooltipContent.innerHTML : '',
                maxWidth: this.tooltipWidth,
                position: this.tooltipPosition,
                contentAsHTML: true,
                interactive: true,
                functionReady: this.updateTooltipPosition
            })
        }
    });
</script>
