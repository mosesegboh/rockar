<template>
    <div>
        <app-part-exchange-core
            :pe-id="peId"
            :valuation-url="valuationUrl"
            :additional-info="additionalInfo"
            :explanatory-text="explanatoryText"
            :car-details-url="carDetailsUrl"
            :save-valuation-url="saveValuationUrl"
            :save-to-session-url="saveToSessionUrl"
            :settlement-terms-url="settlementTermsUrl"
            :reset-url="resetUrl"
            :active-condition="activeCondition"
            :car-conditions="carConditions"
            :car-alternative-details-url="carAlternativeDetailsUrl"
            :saved="saved"
            :vehicle-type-url="vehicleTypeUrl"
            :year-url="yearUrl"
            :make-url="makeUrl"
            :model-url="modelUrl"
            :fuel-type-url="fuelTypeUrl"
            :transmission-url="transmissionUrl"
            :variant-url="variantUrl"
            :custom-car-url="customCarUrl"
            :active-px-url="activePxUrl"
            :saved-px="savedPx"
            :is-used-in-finance="isUsedInFinance"
            :temp-px="tempPx"
            :customer-account-url="customerAccountUrl"
            :settlement-quotes-url="settlementQuotesUrl"
            :update-step-url="updateStepUrl"
            :est-value-disclaimer="estValueDisclaimer"
            :accessed-from="accessedFrom"
            :can-edit="canEdit"
            :step-code="stepCode"
            :checkout-accordion="checkoutAccordion"
            :dropdown-layout="true"
            :finance-quote="financeQuote"
            :customer-is-logged-in="true"
            :show="checkoutAccordion.show"
            v-ref:part-exchange-filter
        >
        </app-part-exchange-core>
    </div>
</template>

<script>
    import appPartExchangeCore from 'dsp2/components/PartExchange/PartExchangeCore/PartExchangeCore';
    import translateString from 'core/filters/Translate';

    export default Vue.extend({
        data: () => ({
            accessedFrom: 'checkout'
        }),

        props: {
            estValueDisclaimer: {
                required: true,
                type: String,
                default: ''
            },

            updateStepUrl: {
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

            peId: {
                required: false,
                type: [Number, String],
                default: 0
            },

            additionalInfo: {
                required: false,
                type: Array,
                default() {
                    return [];
                }
            },

            explanatoryText: {
                required: false,
                type: String,
                default: ''
            },

            carDetailsUrl: {
                required: false,
                type: String,
                default: ''
            },

            valuationUrl: {
                required: false,
                type: String,
                default: ''
            },

            saveValuationUrl: {
                required: false,
                type: String,
                default: ''
            },

            saveToSessionUrl: {
                required: false,
                type: String,
                default: ''
            },

            carAlternativeDetailsUrl: {
                required: false,
                type: String,
                default: ''
            },

            resetUrl: {
                required: false,
                type: String,
                default: ''
            },

            makeUrl: {
                required: false,
                type: String,
                default: ''
            },

            modelUrl: {
                required: false,
                type: String,
                default: ''
            },

            yearUrl: {
                required: false,
                type: String,
                default: ''
            },

            customCarUrl: {
                required: false,
                type: String,
                default: ''
            },

            activePxUrl: {
                required: false,
                type: String,
                default: ''
            },

            carConditions: {
                required: false,
                type: Array,
                default: [
                    {
                        id: 0,
                        title: translateString('Blank')
                    }
                ]
            },

            activeCondition: {
                required: false,
                type: Number,
                default: 0
            },

            activeFinanceSettlementCondition: {
                required: false,
                type: Number,
                default: 0
            },

            saved: {
                required: false,
                type: Boolean,
                default: false
            },

            savedPxList: {
                required: false,
                type: Array,
                default() {
                    return [];
                }
            },

            savedPx: {
                required: false,
                type: [Array, Object],
                default() {
                    return [];
                }
            },

            canOpenCustom: {
                required: false,
                type: Boolean,
                default: true
            },

            isExpired: {
                required: false,
                type: Boolean,
                default: false
            },

            customerAccountUrl: {
                required: false,
                type: String,
                default: ''
            },

            settlementQuotesUrl: {
                required: false,
                type: String
            },

            settlementTermsUrl: {
                required: false,
                type: String,
                default: ''
            },

            fuelTypeUrl: {
                required: false,
                type: String,
                default: ''
            },

            vehicleTypeUrl: {
                required: false,
                type: String
            },

            transmissionUrl: {
                required: false,
                type: String,
                default: ''
            },

            variantUrl: {
                required: false,
                type: String,
                default: ''
            },

            isUsedInFinance: {
                required: false,
                type: Boolean,
                default: false
            },

            canEdit: {
                required: false,
                type: Boolean,
                default: true
            },

            stepCode: {
                required: true,
                type: String
            }
        },

        computed: {
            financeQuote() {
                return this.$root.$refs.financeQuote;
            },

            checkoutAccordion() {
                return this.$root.$refs.partExchangeCheckoutAccordion;
            }
        },

        components: {
            appPartExchangeCore
        }
    })
</script>
