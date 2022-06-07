<template>
    <div class="part-exchange-valuation" v-show="valuationResult">
        <div class="px-car-worth">
            <p class="px-bold-heading">{{ 'Estimate trade-in value:' | translate }}</p>
            <p class="h1">{{ computedPartExchangeValue | numberFormat '0,0.00' true }}</p>
        </div>

        <div class="px-outstanding-finance">
            <div class="px-outstanding-finance-title">
                <p class="px-light-text">{{ 'Outstanding finance to be settled:' | translate }}</p>
                <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                    <span class="action-badge info-small" slot="tooltipElement"></span>
                    <div slot="tooltipContent">
                        <p>
                            If you are still paying off your vehicle, what is the amount required to settle your vehicle finance?
                            If your vehicle is fully paid off, please leave it blank.
                        </p>
                    </div>
                </app-tooltip>
            </div>
            <input type="text" v-model="outstandingFinance | numberOutstandingFinanceFormat" class="px-input keyboard-numbers"
                   @keydown="inputKeyDown()"
                   placeholder="0"
                   @focus="selectOutstandingFinance"
                   @blur="deselectOutstandingFinance">
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
        </div>

        <div class="px-result-actions">
            <button :class="[isValuationValid ? 'button-default' : 'button-disabled']" :disabled="!isValuationValid" @click="saveValuation()">{{ PX.canEdit ? 'Continue with offer' : 'Save' | translate }}</button>
            <button class="button-empty" @click="PX.continueWithout()" v-if="PX.canEdit">{{ 'Continue without Trade in' | translate }}</button>
            <button class="button-empty" @click="PX.closePartExchange()" v-if="!PX.canEdit">{{ 'Cancel' | translate }}</button>
            <span v-if="!isValuationValid">{{ 'As you have negative equity on your car, please click Continue without Trade in or' | translate}} <a href="/contact-us">{{ 'Contact Us' | translate }}</a>{{' to discuss' | translate }}</span>
        </div>
    </div>
</template>

<script>
    import PartExchangeValuation from 'bmw/components/Shared/PartExchangeValuation';
    import appTooltip from 'core/components/Elements/Tooltip';
    import numeral from 'numeral';
    import appSelect from 'core/components/Elements/Select';

    export default Vue.extend({
        mixins: [PartExchangeValuation],

        data() {
            return {
                hasOutstandingFinanceFocus: false,
                cashValue: 0
            };
        },

        props: {
            productId: {
                required: false,
                type: [Number, Boolean],
                default: false
            },

            leadTimePartExchangeValue: {
                required: false,
                type: Number,
                default: 0
            }
        },

        computed: {
            isValuationValid() {
                return true;
            },

            payInFullPaymentConfigurator() {
                const result = this.$root.$refs.financeQuote;

                return (result && result.payInFullPayment) ? result.payInFullPayment : [];
            },

            isPayInFullGroupConfigurator() {
                return this.payInFullPaymentConfigurator.find(payment => payment.group_id === this.financeActivePaymentGroupId)
                    !== undefined;
            }
        },

        methods: {
            inputKeyDown() {
                // software keyboard doesn't detect the backspace key, invoking the change event on all keypresses
                if (this.$els.pxOutstandingInput) {
                    this.$nextTick(() => this.$els.pxOutstandingInput.dispatchEvent(new Event('change')));
                }
            },

            getValuation() {
                this.PX.ajaxLoading = true;
                this.$http({
                    url: this.PX.valuationUrl,
                    data: {
                        productId: this.productId,
                        px_id: this.PX.peId,
                        mileage: this.PX.mileage,
                        carCondition: this.PX.activeCondition,
                        additionalInfo: this.PX.additionalInfo
                    }
                }).then(this.getValuationSuccess, this.getValuationFail);
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
                this.partExchangeValue = this.leadTimePartExchangeValue;
                if (this.PX.activeFinanceSettlementCondition) {
                    this.$store.commit('setPXOutstandingFinanceSettlement', this.PX.activeFinanceSettlementCondition);
                }
                this.$store.commit('setPXValuationValuationResult', true);
                this.$store.commit('setPXValuationValuationCompleted', true);
                this.$store.commit('setPXValuationOutstandingFinance', save.request.params.outstanding_finance);
                this.$store.commit('setPxShowNotification', true);
                this.$store.commit('setPxShowNotificationVehicle', true);
                this.$dispatch('Main::showPartExchangeNotification', true);
                if (typeof this.$root.$refs.carFinder !== 'undefined') {
                    this.$root.$refs.carFilter.$refs.partExchangeFilterMenu.updatePXData(false);
                }

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

            getValuationSuccess(valuation) {
                this.activateThirdStep();
                valuation = valuation.data;
                this.partExchangeValue = valuation.totals.total;
                this.leadTimePartExchangeValue = valuation.updated_part_exchange_value;
                this.$store.commit('setPXValuationPartExchangeValue', valuation.totals.total);
                this.$store.commit('setPXValuationValuationResult', true);
                this.$store.commit('setPXVrmCarInfoModel', valuation.cap.model);
                this.$store.commit('setPXVrmCarInfoVrm', valuation.vrm);
                this.$store.commit('setPXVrmCarInfoVrmInput', valuation.vrm);
                this.$store.commit('setPXVrmCarInfoCapId', valuation.cap.capid);
                this.$store.commit('setPXVrmCarInfoTitle', valuation.cap.derivative_spec);
                this.$store.commit('setPXVrmCarInfoDerivative', valuation.cap.derivative);
                this.$store.commit('setPXMileage', valuation.mileage);
            },

            selectOutstandingFinance() {
                this.hasOutstandingFinanceFocus = true;
            },

            deselectOutstandingFinance() {
                this.hasOutstandingFinanceFocus = false;
            }
        },

        ready() {
            if (this.isPayInFullGroupConfigurator) {
                this.changeNegativeEquityPayment(this.tradeinShortfallOnecashPayment);
            }
        },

        components: {
            appTooltip,
            appSelect
        }
    });
</script>
