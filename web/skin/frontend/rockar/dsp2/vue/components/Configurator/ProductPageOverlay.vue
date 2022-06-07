<template>
    <div id="product-page-overlay" class="product-page-overlay-section" :class="[{ 'double': compareVisible }, { 'overlay': overlayActive }]">
        <div class="product-page-overlay-top" :class="{ 'active': active }">
            <div class="toggle">
                <div class="toggle-button" @click="toggle()">
                    <div class="icon"></div>
                </div>
            </div>
            <template v-if="compareVisible">
                <div class="titles">
                    <div :class="{ 'selected': selected === 'quote' }" @click="selectOverlay('quote')">
                        <span>{{ 'Quote' | translate }}</span>
                    </div>
                    <div :class="{ 'selected': selected === 'compare' }" @click="selectOverlay('compare')">
                        <span>{{ 'Compare List' | translate }}</span>
                        <span class="clear" @click="openClearAllPopup()">{{ 'Clear All' | translate }}</span>
                    </div>
                </div>
            </template>
        </div>
        <slot name="finance-quote" class="product-page-overlay-bottom"></slot>
        <slot name="compare" class="product-page-overlay-bottom"></slot>
        <slot name="compare-clear"></slot>
    </div>
</template>

<script>
    import EventTracker from 'dsp2/mixins/EventTracker';

    export default Vue.extend({
        mixins: [EventTracker],

        data() {
            return {
                active: false,
                selected: 'quote',
                compareVisible: false,
                overlayActive: false
            }
        },

        computed: {
            eventTrackerVehicles() {
                return this.$root.$refs.carCompare.compareData.items.map(vehicles => vehicles.name);
            }
        },

        methods: {
            toggle() {
                this.active = !this.active;
                this.$root.$refs.financeQuote.active = this.active;
                this.$root.$refs.carCompare.active = this.active;
                const elem = jQuery(this.$el).find('.finance-quote-bottom');

                if (this.active) {
                    this.$root.$refs.carCompare.initTitles();
                    elem.addClass('border');
                    jQuery('body').css({ overflow: 'hidden' });
                } else {
                    elem.removeClass('border');
                    jQuery('body').css({ overflow: 'visible' });
                }
            },

            selectOverlay(value) {
                this.selected = value;
                const element = jQuery(this.$el).find('#car-compare-wrapper');

                if (this.selected === 'quote') {
                    element.removeClass('top').addClass('bottom');
                } else {
                    element.removeClass('bottom').addClass('top');

                    if (this.active) {
                        this.fireEventForTracking(
                            this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                            `${this.getEventConstants().EVENTRACKERVALUES.COMPAREEXPAND}${this.eventTrackerVehicles}`
                        );
                    }
                }
            },

            openClearAllPopup() {
                this.$parent.$broadcast('CarCompare::openClearPopup');
            }
        },

        events: {
            'ProductPageOverlay::carCompareVisible'(value) {
                this.compareVisible = value;
                if (!value) {
                    this.active = false;
                    jQuery(this.$el).find('.finance-quote-bottom').removeClass('border');
                }
            },

            'ProductPageOverlay::carCompareActive'(value) {
                this.$root.$refs.financeQuote.active = value;
                this.selectOverlay(value ? 'compare' : 'quote');
                this.active = value;
            },

            'ProductPageOverlay::Overlay'(val) {
                this.overlayActive = val;
            }
        }
    });
</script>
