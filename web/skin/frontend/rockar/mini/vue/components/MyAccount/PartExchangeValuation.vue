<template>
    <div class="part-exchange-valuation" v-show="valuationResult">
        <div class="px-car-worth">
            <p class="px-bold-heading">{{ 'Car Value:' | translate }}</p>
            <p class="h1">{{ partExchangeValue | numberFormat '0,0.00' true }}</p>
        </div>

        <div class="px-outstanding-finance">
            <p class="px-light-text">{{ 'What outstanding finance is on the vehicle:' | translate }}</p>
            <input type="text" v-model="outstandingFinance | numberOutstandingFinanceFormat" class="px-input keyboard-numbers"
                   @keydown="inputKeyDown()"
                   placeholder="0"
                   @focus="selectOutstandingFinance"
                   @blur="deselectOutstandingFinance">
        </div>

        <div class="col-12 text-center" v-if="hasNegativeEquity && !isPayInFullGroup">
            <p>{{ 'Trade-In Shortfall:' | translate }} </p>
            <div class="px-filter-input">
                <div class="px-filter-input-wrapper">
                    <app-select
                        :init-selected="getPxOutstandingFinanceSettlement() - 1"
                        :item-height="45"
                        :options="outstandingFinanceSettlementOptions"
                        @select="changeNegativeEquityPayment"
                        v-ref:outstanding-finance-settlement
                    ></app-select>
                </div>
            </div>
        </div>

        <div class="px-result-actions">
            <button :class="[isValuationValid ? 'button-default' : 'button-disabled']" :disabled="!isValuationValid" @click="saveValuation()">{{ PX.canEdit ? 'Continue with Trade-In' : 'Save' | translate }}</button>
            <button class="button-empty" @click="PX.continueWithout()" v-if="PX.canEdit">{{ 'Continue without Trade-In' | translate }}</button>
            <button class="button-empty" @click="PX.closePartExchange()" v-if="!PX.canEdit">{{ 'Cancel' | translate }}</button>
            <span v-if="!isValuationValid">{{ 'As you have negative equity on your car, please click Continue without Trade-In or' | translate}} <a href="/contact-us">{{ 'Contact Us' | translate }}</a>{{' to discuss' | translate }}</span>
        </div>
    </div>
</template>

<script>
    import PartExchangeValuation from 'mini/components/Shared/PartExchangeValuation';
    import numeral from 'numeral';
    import appSelect from 'core/components/Elements/Select';

    export default Vue.extend({
        mixins: [PartExchangeValuation],

        data() {
            return {
                hasOutstandingFinanceFocus: false
            };
        },

        methods: {
            inputKeyDown() {
                // software keyboard doesn't detect the backspace key, invoking the change event on all keypresses
                this.$nextTick(() => this.$els.pxOutstandingInput.dispatchEvent(new Event('change')));
            },

            saveValuationSuccess(save) {
                this.PX.ajaxLoading = false;
                this.valuationCompleted = false;
                this.PX.peId = save.data.px_id;
                this.valuationResult = true;
                this.PX.disableSelects = false;
                this.PX.valuationResult = true;
                this.PX.closePartExchange();
                this.PX.updatePartExchange(save);
                this.$store.commit('setPXValuationValuationResult', true);
                this.$store.commit('setPXValuationValuationCompleted', true);
                this.$store.commit('setPXValuationOutstandingFinance', save.request.params.outstanding_finance);
                this.$store.commit('setPXOutstandingFinanceSettlement', save.request.params.outstanding_finance_settlement);

                if (this.PX.canEdit) {
                    window.location.reload();
                }
            },

            savePartExchangeToSessionSuccess() {
                this.PX.ajaxLoading = false;
                this.valuationCompleted = true;
                this.$store.commit('setPXValuationValuationCompleted', true);
                this.PX.closePartExchange();
            },

            selectOutstandingFinance() {
                this.hasOutstandingFinanceFocus = true;
            },

            deselectOutstandingFinance() {
                this.hasOutstandingFinanceFocus = false;
            }
        },

        ready() {
            if (this.isPayInFullGroup) {
                this.changeNegativeEquityPayment(this.tradeinShortfallOnecashPayment);
            }
        },

        components: {
            appSelect
        }
    });
</script>
