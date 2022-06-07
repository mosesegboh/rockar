<template>
    <div class="trade-in-block-wrapper" v-show="isVisible">
        <app-part-exchange-block-content
            @remove="removePX"
            @open="openTradeInModalPxActive"
            :has-saved-px="hasSavedPx"
            :product-title="productTitle"
            :saved-px-value="savedPxValue"
            :est-value-disclaimer="estValueDisclaimer"
            v-ref:part-exchange-info-block
        ></app-part-exchange-block-content>
        <template v-if="!hasSavedPx">
            <p class="trade-in-get-started">
                {{ descriptionText }}
            </p>
            <button class="button" :class="buttonClass" @click="openTradeInModal()">
                {{ buttonText }}
            </button>
        </template>
    </div>
</template>

<script>
    import appPartExchangeBlockContent from 'dsp2/components/PartExchange/PartExchangeBlock/PartExchangeBlockContent';
    import translateString from 'core/filters/Translate';

    export default Vue.extend({
        props: {
            estValueDisclaimer: {
                required: true,
                type: String
            },

            expireDate: {
                required: false,
                type: String,
                default: ''
            },

            tempPx: {
                required: false,
                type: Object,
                default() {
                    return {
                        vrm: '',
                        mileage: 0,
                        capId: 0,
                        model: '',
                        title: '',
                        derivative: '',
                        registrationYear: ''
                    };
                }
            },

            savedPx: {
                required: false,
                type: Object,
                default() {
                    return {
                        part_exchange_value: '',
                        cap_extended: {
                            product_name: ''
                        }
                    };
                }
            },

            isPxRemoved: {
                required: false,
                type: Boolean,
                default: false
            },

            isExpired: {
                required: false,
                type: Boolean,
                default: false
            }
        },

        computed: {
            savedPxValue() {
                return this.savedPx.part_exchange_value;
            },

            productTitle() {
                return this.savedPx.cap_extended.product_name;
            },

            hasSavedPx() {
                return this.FinanceFilter
                    ? this.FinanceFilter.showPxInfoBlock && this.hasVrm
                    : this.hasVrm;
            },

            hasVrm() {
                if (this.tempPx) {
                    return !!this.tempPx.vrm && !!this.savedPx.vrm;
                } else {
                    return false
                }
            },

            isVisible() {
                return this.CarFinder
                    ? (this.CarFinder.isMobile && this.ProductGrid.hideProductGrid) || !this.CarFinder.isMobile
                    : true;
            },

            FinanceFilter() {
                return this.$root.$refs.financeFilter;
            },

            CarFinder() {
                return this.$root.$refs.carFinder;
            },

            MyAccount() {
                return this.$root.$refs.myDetails;
            },

            ProductGrid() {
                return this.$root.$refs.productGrid;
            },

            PartExchangeFilter() {
                return this.$root.$refs.partExchange.$refs.partExchangeFilter;
            },

            buttonClass() {
                return 'dsp2-outline';
            },

            descriptionText() {
                return this.translateString('Considering trading in your vehicle for a BMW? Let\'s get started.');
            },

            buttonText() {
                return this.translateString('Add a trade-in');
            }
        },

        methods: {
            openTradeInModal() {
                if (this.isPxRemoved) {
                    window.EventsBus.$emit('PartExchange::resetPxData');
                }

                if (this.CarFinder) {
                    window.EventsBus.$emit('FinanceFilter::openFromTradeInBlock', true);
                    this.FinanceFilter.$refs.tradeInModal.show = true;
                } else if (this.MyAccount) {
                    this.$root.$refs.tradeInMyAccountModal.show = true;
                }

                if (this.PartExchangeFilter && this.PartExchangeFilter.currentStep === 2
                    || this.PartExchangeFilter && this.PartExchangeFilter.currentStep === 0 && !this.hasSavedPx) {
                    window.EventsBus.$emit('PartExchange::triggerEventTrackerCheck');
                }
            },

            /**
             * Opens Trade-in Modal (saved Px)
             */
            openTradeInModalPxActive(isExpired) {
                this.openTradeInModal();
                EventsBus.$emit('FinanceFilter::setSavedPxData', isExpired);

                if (!isExpired) {
                    window.EventsBus.$emit('PartExchangeFilter::hasSavedPx');
                }
            },

            removePX() {
                if (this.MyAccount) {
                    // Trade-in removed from Confirmation modal
                    this.$refs.partExchangeInfoBlock.ajaxLoading = true;
                    window.EventsBus.$emit('PartExchange::resetPxData');
                } else {
                    window.EventsBus.$emit('FinanceFilter::removePxFromTradeInBlock');
                }
            },

            translateString
        },

        events: {
            'ConfirmationModal::cancel'() {
                if (this.CarFinder) {
                    window.EventsBus.$emit('FinanceFilter::openFromTradeInBlock', true);
                    this.FinanceFilter.$refs.tradeInModal.show = true;
                } else {
                    this.$root.$refs.tradeInMyAccountModal.show = false;
                }
            },

            'TradeInBlock::resetPX'(val) {
                if (val && this.$root.$refs.tradeInMyAccountModal) {
                    this.$root.$refs.tradeInMyAccountModal.show = false;
                }

                this.tempPx = {
                    vrm: '',
                    mileage: 0,
                    capId: 0,
                    model: '',
                    title: '',
                    derivative: '',
                    registrationYear: ''
                };

                this.$refs.partExchangeInfoBlock.ajaxLoading = false;
            }
        },

        ready() {
            if (this.CarFinder) {
                if (this.FinanceFilter.pxRemoved || !this.PartExchangeFilter.tempPx) {
                    this.FinanceFilter.showPxInfoBlock = false;
                }
            }
        },

        components: {
            appPartExchangeBlockContent
        }
    });
</script>
