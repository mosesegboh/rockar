<template>
    <template v-if="settlementQuotes.length">
        <div class="modal-dialog">
            <div class="modal-content">
                <p class="modal-header">
                    {{
                        'Please confirm the BMW Group Financial Services vehicle you\'d like a settlement for:' | translate
                    }}
                </p>
            </div>
        </div>
        <div v-for="(index, settlementQuote) in settlementQuotes" class="settlement-quote-item">
            <input
                type="radio"
                name="settlementAmount"
                id="{{ 'settlementAmount' + index }}"
                class="radio-checkbox small-business-check"
                value="settlementQuote.settlementAmount"
                @change="selectSettlement(index, settlementQuote)"
                :checked="index === setCheckedSettlementQuote(settlementQuotes)"
            >
            <label for="{{ 'settlementAmount' + index }}">
                <div class="data-block">
                    <div class="popup-line popup-line-title">
                        {{ settlementQuote.vehicleDescription }}
                    </div>
                    <div class="popup-line popup-line-reference">{{ 'Reference' | translate }}
                        {{ settlementQuote.dealReference }}
                    </div>
                    <div class="popup-line popup-line-outstanding-finance">*{{ 'Outstanding Finance' | translate }}
                        {{ settlementQuote.settlementAmount | numberFormat '0,0.00' true }}
                    </div>
                </div>
            </label>
        </div>
        <p class="settlement-quote-popup-notification">
            <span v-if="!carMatched">
                {{
                    'Please note: The selection doesn\'t match your original trade-in vehicle. Are you sure you want to continue?' | translate
                }}
            </span>
        </p>
        <div class="settlement-quote-popup-footer">
            <a :href="settlementTermsUrl" target="_blank" class="button-empty terms-conditions">*{{ 'Terms and conditions apply' | translate }}</a>
            <button type="button"
                    @click="selectSettlementQuote"
                    class="button">
                <span>{{ 'CONTINUE' | translate }}</span>
            </button>
        </div>
    </template>
    <template v-else>
        <div class="modal-dialog">
            <div class="modal-content">
                <p class="modal-header">
                    {{ 'No match found.' | translate }}
                </p>
            </div>
        </div>
        <div class="settlement-popup-error" v-html="settlementQuotesError"></div>
    </template>
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
            type: String
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
        }
    },

    data() {
        return {
            selectedSettlement: '',
            selectedSettlementCapCode: '',
            selectedPxPlateYear: '',
            selectedSettlementQuote: '',
            settlementQuotesError: '',
            settlementQuoteIndex: ''
        };
    },

    computed: {
        carMatched() {
            return this.selectedSettlementCapCode === this.currentPxCapId && this.selectedPxPlateYear === this.currentPxPlateYear;
        }
    },

    methods: {
        selectSettlementQuote() {
            this.$dispatch('SettlementQuotePopup::QuoteSelected', this.selectedSettlement);
            this.$parent.closePopup();
        },

        selectSettlement(index, selectedQuote) {
            this.selectedSettlement = selectedQuote.settlementAmount;
            this.selectedSettlementCapCode = selectedQuote.mmCode;
            this.selectedPxPlateYear = selectedQuote.regYear.toString();
            this.selectedSettlementQuote = selectedQuote;
            this.settlementQuoteIndex = index;
        },

        setCheckedSettlementQuote(settlementQuotes) {
            if (!this.settlementQuoteIndex) {
                this.selectSettlement(0, settlementQuotes[0])
                return this.settlementQuoteIndex;
            } else {
                return this.settlementQuoteIndex;
            }
        }
    }
});
</script>