<template>
    <div class="part-exchange-valuation" v-show="valuationResult">
        <div class="px-car-worth">
            <p class="px-bold-heading">{{ 'Car Values:' | translate }}</p>
            <p class="h1">{{ computedPartExchangeValue | numberFormat '0,0.00' true }}</p>
        </div>

        <div class="px-outstanding-finance">
            <p class="px-light-text">{{ 'What outstanding finance is on the vehicle:' | translate }}</p>
            <input type="text" v-model="outstandingFinance | numberOutstandingFinanceFormat" class="px-input keyboard-numbers"
                   @keydown="inputKeyDown()"
                   placeholder="0"
                   @focus="selectOutstandingFinance"
                   @blur="deselectOutstandingFinance">
        </div>

        <div class="auto-settlement">
            <div>
                <p class="heading">
                    {{ 'View your Settlement Value' | translate }}
                </p>
                <p>
                    {{ 'If your vehicle is financed through BMW Group Financial Services, you can retrieve your outstanding settlement value using the option below:' | translate }}
                </p>
                <div>
                    <button class="button button-default" @click="getSettlementQuotes()">
                        {{ 'View my settlement' | translate }}
                    </button>
                </div>
            </div>
        </div>

        <div class="px-filter-wrapper" v-if="hasNegativeEquity && !isPayInFullGroup">
            <p>{{ 'Trade-In Shortfall:' | translate }} </p>
            <div class="px-filter-input">
                <div class="px-filter-input-wrapper">
                    <app-select
                        :init-selected="getPxOutstandingFinanceSettlement() - 1"
                        :item-height="45"
                        :options="PX.outstandingFinanceSettlementOptions"
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
        </div>
    </div>
    <app-modal class-name="settlement-quote-popup" :show="showSettlementQuotesPopup" :show-close="true">
        <div slot="content">
            <app-settlement-popup
                :settlement-quotes="settlementQuotes"
                :current-px-cap-id="currentPxCapId"
                :current-px-plate-year="currentPxPlateYear"
                :settlement-quotes-error="settlementQuotesError"
                :settlement-terms-url="PX.settlementTermsUrl"
            ></app-settlement-popup>
        </div>
    </app-modal>
</template>

<script>
    import PartExchangeValuation from 'mini/components/Shared/PartExchangeValuation';
    import numeral from 'numeral';
    import appSelect from 'core/components/Elements/Select';
    import appModal from 'core/components/Elements/Modal';
    import appSettlementPopup from 'mini/components/AutoSettlement/SettlementPopup';

    export default Vue.extend({
        mixins: [PartExchangeValuation],

        data() {
            return {
                leadTimePartExchangeTitle: '',
                hasOutstandingFinanceFocus: false,
                settlementQuotes: [],
                currentPxCapId: '',
                currentPxPlateYear: '',
                showSettlementQuotesPopup: false,
                settlementQuotesError: '',
                noMatchesErrorHtml: '<div>We are unable to validate your BMW Group Financial Services customer information using the details you provided. Please contact us on <a href="tel:+27800600555">080 060 0555</a> for further assistance.<div>'
            };
        },

        props: {
            productId: {
                required: true,
                type: Number
            },

            leadTimePartExchangeValue: {
                required: false,
                type: Number,
                default: 0
            }
        },

        computed: {
            computedPartExchangeValue() {
                if (this.leadTimePartExchangeValue !== false && !isNaN(this.leadTimePartExchangeValue)) {
                    return this.leadTimePartExchangeValue;
                } else {
                    return this.partExchangeValue;
                }
            },

            isValuationValid() {
                return true;
            },
        },

        methods: {
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

            saveValuation() {
                var checkoutStep = (typeof this.$parent.$parent.getNextStepIndex === 'function') ? this.$parent.$parent.getNextStepIndex() : '';
                this.PX.ajaxLoading = true;
                if (this.isPayInFullGroup) {
                    this.changeNegativeEquityPayment(this.tradeinShortfallOnecashPayment);
                }
                this.$http({
                    url: this.PX.saveValuationUrl,
                    data: {
                        vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm,
                        cap_id: parseInt(this.PX.$refs.partExchangeVrm.carInfo.capId),
                        car_model: this.PX.$refs.partExchangeVrm.carInfo.model,
                        car_mileage: this.PX.mileage,
                        car_condition: this.PX.activeCondition,
                        checkboxes: this.checkboxesValues,
                        outstanding_finance: this.outstandingFinance,
                        outstanding_finance_settlement: this.getPxOutstandingFinanceSettlement(),
                        part_exchange_value: this.partExchangeValue,
                        browser: window.navigator.userAgent,
                        px_id: this.PX.peId,
                        step: checkoutStep,
                        updated_part_exchange_value: this.leadTimePartExchangeValue
                    }
                }).then(this.saveValuationSuccess, this.saveValuationFail);
            },

            saveValuationSuccess(save) {
                this.PX.ajaxLoading = false;
                this.valuationCompleted = true;
                this.PX.peId = save.data.px_id;
                this.PX.closePartExchange();
                this.PX.updatePartExchange(save);
                this.PX.isExpired = false;
                this.partExchangeValue = this.leadTimePartExchangeValue;
                if (this.PX.activeFinanceSettlementCondition) {
                    this.$store.commit('setPXOutstandingFinanceSettlement', this.PX.activeFinanceSettlementCondition);
                }
            },

            saveValuationFail(resp) {
                this.PX.ajaxLoading = false;
                if (resp.data.redirect && resp.status === 401) {
                    this.$root.loggedOutPopup(resp.data.redirect);
                    return;
                }
                if (resp.data.redirect && resp.status !== 401) {
                    window.location.href = resp.data.redirect;
                    return;
                }
                if (resp.data.error) {
                    alert(resp.data.error);
                    return;
                }
                if (resp.data.errorMessage) {
                    alert(resp.data.errorMessage);
                    return;
                }

                alert(resp.statusText);
            },

            savePartExchangeToSessionSuccess() {
                this.PX.ajaxLoading = false;
                this.valuationCompleted = true;
                this.PX.closePartExchange();
            },

            getValuationSuccess(valuation) {
                this.activateThirdStep();
                valuation = valuation.data;
                this.partExchangeValue = valuation.totals.total;
                this.leadTimePartExchangeValue = valuation.updated_part_exchange_value;
                this.leadTimePartExchangeTitle = valuation.lead_time_title;
            },

            selectOutstandingFinance() {
                this.hasOutstandingFinanceFocus = true;
            },

            deselectOutstandingFinance() {
                this.hasOutstandingFinanceFocus = false;
            },

            changeNegativeEquityPayment(data) {
                EventsBus.$emit('PartExchange::updateActiveFinanceSettlementCondition', data.id || data);
            },

            getSettlementQuotes() {
                this.PX.ajaxLoading = true;

                this.$http({
                    url: this.PX.settlementQuotesUrl
                }).then(this.getSettlementQuotesSuccess, this.getSettlementQuotesFail);
            },

            getSettlementQuotesSuccess(response) {
                this.settlementQuotes = [];

                if (response.data.success) {
                    this.settlementQuotes = response.data.settlementQuotes;
                    this.currentPxCapId = response.data.currentPxCapId;
                    this.currentPxPlateYear = response.data.currentPxPlateYear;
                    this.showSettlementQuotesPopup = true;
                    this.$broadcast('SettlementQuotes::SettlementQuotesSuccess', this.settlementQuotes);
                } else {
                    if (response.data.errorCode === 'no_matches') {
                        this.settlementQuotesError = this.noMatchesErrorHtml;
                    } else {
                        this.settlementQuotesError = response.data.error;
                    }
                    this.showSettlementQuotesPopup = true;
                }
                this.PX.ajaxLoading = false;
            },

            getSettlementQuotesFail(response) {
                this.$broadcast('SettlementQuotes::SettlementQuotesError', response.data.error);
                this.PX.ajaxLoading = false;
                alert(response.data.error);
            }
        },

        events: {
            'SettlementQuotePopup::QuoteSelected'(value) {
                this.outstandingFinance = value;
                this.showSettlementQuotesPopup = false;
            },

            'close-popup'() {
                this.showSettlementQuotesPopup = false;
            }
        },

        ready() {
            if (this.isPayInFullGroup) {
                this.changeNegativeEquityPayment(this.tradeinShortfallOnecashPayment);
            }

            if (!this.PX.activeFinanceSettlementCondition) {
                this.PX.activeFinanceSettlementCondition = this.PX.outstandingFinanceSettlement;
            }
        },

        components: {
            appSelect,
            appModal,
            appSettlementPopup
        }
    });
</script>
