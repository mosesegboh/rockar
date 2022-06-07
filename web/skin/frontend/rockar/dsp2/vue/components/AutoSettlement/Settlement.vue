<template>
    <div class="settlement-block-content" v-if="!this.$parent.PX.ajaxLoading">
        <template v-if="settlementQuotes.length">
            <div class="settlement-content-header">
                <p class="header">
                    {{
                        'Please confirm the BMW Group Financial Services vehicle you\'d like a settlement for:' | translate
                    }}
                </p>
                <a :href="settlementTermsUrl" target="_blank" class="button-empty terms-conditions">*{{ 'Terms and Conditions apply' | translate }}</a>
            </div>
            <div v-for="(index, settlementQuote) in settlementQuotes" class="settlement-quote-item" :class="{ selected: index === this.settlementQuoteIndex }">
                <div class="settlement-quote-item-description">
                    <input
                        type="radio"
                        name="settlementAmount"
                        id="{{ 'settlementAmount' + index }}"
                        class="radio-checkbox small-business-check"
                        value="settlementQuote.settlementAmount"
                        @change="selectSettlement(index, settlementQuote) + selectSettlementQuote()"
                        :checked="index === this.settlementQuoteIndex"
                    >
                    <label for="{{ 'settlementAmount' + index }}">
                        <span></span>
                        <div class="data-block">
                            <div class="settlement-line-title">
                                {{ settlementQuote.vehicleDescription }}
                            </div>
                            <div class="settlement-line-reference">{{ 'Reference' | translate }}
                                {{ settlementQuote.dealReference }}
                            </div>
                        </div>
                    </label>
                </div>
                <div class="settlement-quote-item-amount">
                    <hr>
                    <div class="settlement-line-outstanding-finance">
                        <div class="outstanding-finance-title">
                            {{ 'Outstanding Finance' | translate }}
                        </div>
                        <div class="outstanding-finance-value">
                            {{ settlementQuote.settlementAmount | numberFormat '0,0.00' true }}
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
            <p class="settlement-quote-notification">
            <span v-if="!carMatched && settlementQuoteIndex">
                {{
                    'Please note: The selection doesn\'t match your original trade-in vehicle. Are you sure you want to continue?' | translate
                }}
            </span>
            </p>
        </template>
        <template v-else>
            <div class="settlement-no-results-found">
                <p class="header">
                    {{ 'No vehicles found.' | translate }}
                </p>
                <p class="settlement-contact-info">
                    {{ 'Please contact us on' | translate }}
                    <a href="tel:+27800600555">080 060 0555</a>
                    {{ 'for further assistance.' | translate }}
                </p>
            </div>
        </template>
    </div>
</template>

<script>

export default Vue.extend({
    props: {
        settlementQuotes: {
            required: true,
            type: Array
        },

        settlementQuotesError: {
            required: false,
            type: String,
            default: ''
        },

        currentPxCapId: {
            required: false,
            type: String,
            default: ''
        },

        currentPxPlateYear: {
            required: false,
            type: String,
            default: ''
        },

        settlementTermsUrl: {
            required: false,
            type: String,
            default: ''
        },

        selectedSettlementCapCode: {
            required: false,
            type: String,
            default: ''
        }
    },

    data() {
        return {
            selectedSettlement: '',
            selectedPxPlateYear: '',
            selectedSettlementQuote: '',
            settlementQuoteIndex: ''
        };
    },

    computed: {
        carMatched() {
            return this.selectedSettlementCapCode === this.currentPxCapId && this.selectedPxPlateYear === this.currentPxPlateYear;
        }
    },

    watch: {
        settlementQuotes: {
            handler(val) {
                if (this.selectedSettlementCapCode !== '') {
                    const index = val.findIndex(obj => obj.mmCode === this.selectedSettlementCapCode);

                    if (index !== -1) {
                        const result = val[index];
                        this.selectSettlement(index, result)
                        this.settlementQuoteIndex = index;
                        this.selectSettlementQuote();
                    }
                } else {
                    this.selectedSettlement = '';
                    this.selectedPxPlateYear = '';
                    this.selectedSettlementQuote = '';
                    this.settlementQuoteIndex = '';
                }
            }
        }
    },

    methods: {
        selectSettlementQuote() {
            this.$dispatch('SettlementQuotePopup::QuoteSelected', {
                selectedSettlement: this.selectedSettlement,
                selectedSettlementCode: this.selectedSettlementCapCode
            });
        },

        selectSettlement(index, selectedQuote) {
            this.selectedSettlement = selectedQuote.settlementAmount;
            this.selectedSettlementCapCode = selectedQuote.mmCode;
            this.selectedPxPlateYear = selectedQuote.regYear.toString();
            this.selectedSettlementQuote = selectedQuote;
            this.settlementQuoteIndex = index;
        }
    }
});
</script>
