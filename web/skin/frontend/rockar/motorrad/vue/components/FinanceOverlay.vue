<template>
    <div id="choose-payment">
        <template v-if="!sessionError">
            <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

            <div class="wrapper" v-show="dataReceived">
                <div class="h-common h-heavy payment-title">{{ 'Payment Options' | translate }}</div>
                <p class="payment-subtitle" v-show="!useShortVersion">
                    <template v-if="showSlider">{{ 'Tailor your quote and monthly payments using the sliders below.' | translate }}</template>
                    <template v-if="!isHirePayment">{{ 'If you want to change the type of finance use the tabs on the left.' | translate }}</template>
                </p>
                <div class="payment-settings" v-show="showSlider && !useShortVersion && !isStatic">
                    <div class="payment-settings-block settings-block-left" v-show="!isHirePayment">
                        <div class="payment-settings-block-title">
                            <div class="h-common h-heavy">
                                {{ 'Deposit' | translate }}
                                <span class="payment-settings-block-title-input">
                                    <strong>{{ currency }}</strong>
                                    <input
                                        type="text"
                                        v-model="financeParams.deposit | numberFormat '0,0' false"
                                        @keyup.enter="changeValue('deposit')"
                                        @blur="changeValue('deposit')"
                                        size="7"
                                        class="keyboard-numbers"
                                        maxlength="7"
                                    >
                                </span>
                            </div>
                        </div>
                        <app-range-slider :use-id="true" :active-on-slide="true" :active="financeParams.deposit" :options="currentFinanceSteps.deposit" custom-event="FinanceOverlay::conditionChanged" custom-event-slide="FinanceOverlay::conditionChangeDeposit" v-ref:deposit-slider></app-range-slider>
                    </div>
                    <div class="payment-settings-block settings-block-left" v-show="isHirePayment">
                        <div class="payment-settings-block-title">
                            <div class="h-common h-heavy">
                                {{ 'Deposit multiple' | translate }}
                                <span>{{ financeParams.depositMultiple | numberFormat '0' false }}</span>
                            </div>
                        </div>
                        <app-range-slider :use-id="true" :active-on-slide="true" :active="financeParams.depositMultiple" :options="currentFinanceSteps.depositMultiple" custom-event="FinanceOverlay::conditionChanged" custom-event-slide="FinanceOverlay::conditionChangeDepositMultiple" :display-steps="true" v-ref:depositMultiple-slider></app-range-slider>

                        <div class="row">
                            <input id="finance-maintenance" type="checkbox" v-model="financeParams.maintenance" :true-value="1" :false-value="0" />
                            <label for="finance-maintenance"><span></span>{{ 'Add maintenance' }}</label>
                        </div>
                    </div>

                    <div class="payment-settings-block settings-block-middle">
                        <div class="payment-settings-block-title">
                            <div class="h-common h-heavy">
                                {{ 'Number of Instalments' | translate }}
                                <span>{{ financeParams.term | numberFormat '0' false }}</span>
                            </div>
                        </div>
                        <app-range-slider :use-id="true" :active-on-slide="true" :active="financeParams.term" :options="currentFinanceSteps.term" custom-event="FinanceOverlay::conditionChanged" custom-event-slide="FinanceOverlay::conditionChangeTerm" v-ref:term-slider></app-range-slider>
                    </div>

                    <div class="payment-settings-block settings-block-right" v-show="!isInstalmentSaleID(activeGroup)">
                        <div class="payment-settings-block-title">
                            <div class="h-common h-heavy">
                                {{ 'Contract End Mileage'| translate }}
                                <span>{{ financeParams.mileage | numberFormat '0,0' false }}</span>
                            </div>
                        </div>
                        <app-range-slider :use-id="true" :active-on-slide="true" :active="financeParams.mileage" :options="currentFinanceSteps.mileage" custom-event="FinanceOverlay::conditionChanged" custom-event-slide="FinanceOverlay::conditionChangeMileage" v-ref:mileage-slider></app-range-slider>
                    </div>

                    <div class="payment-settings-block settings-block-right" v-show="isInstalmentSaleID(activeGroup)">
                        <div class="payment-settings-block-title">
                            <div class="h-common h-heavy">
                                {{ 'Balloon Percentage'| translate }}
                                <span>{{ financeParams.balloonPercentage | numberPercentageFormat }}</span>
                            </div>
                        </div>
                        <app-range-slider
                            :use-id="true"
                            :active-on-slide="true"
                            :active="financeParams.balloonPercentage"
                            :options="currentFinanceSteps.balloonPercentage"
                            custom-event="FinanceOverlay::conditionChanged"
                            custom-event-slide="FinanceOverlay::bp-update"
                            v-ref:balloon-percentage-slider
                        ></app-range-slider>
                    </div>
                </div>

                <template v-for="option in options">
                    <div class="payment-methods-title-block" v-show="activeGroup == option.group_id" :data-id="option.group_title.toLowerCase()">
                        <div class="text-block option-header-block">
                            <div>
                                {{{ option.header }}}
                                <template v-if="!useShortVersion && !isStatic">
                                    <div class="payment-methods-body-continue">
                                        <button @click="isOptionAvailable(option) ? selectAndContinue() : ''" :class="{ 'button-disabled': !isOptionAvailable(option) }" :disabled="!isOptionAvailable(option)">
                                            {{ 'Select and Continue' | translate }}
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="vertical-tabs-container">
                    <div class="payment-methods" v-el:methods>
                        <div class="payment-methods-tabs">

                            <template v-for="option in options">
                                <div class="payment-methods-tabs-block" :class="[activeGroup == option.group_id ? 'active' : '']" v-if="checkActive(option)" :data-id="option.group_title.toLowerCase()">
                                    <div class="payment-methods-tabs-block-body" @click="changeActiveMethod($index)" v-if="!parseInt(option.is_static)">
                                        <div class="h-common h-heavy">{{ option.group_title }}</div>
                                        <p v-if="option.calc && option.pay_in_full == 0 && option.calc.min_deposit_validation.value == 0">
                                            {{ 'More Deposit required' | translate }}
                                        </p>

                                        <template v-if="option.calc && option.calc.monthly_price && option.pay_in_full == 0 && option.calc.min_deposit_validation.value == 1">
                                            <div class="h-common">{{ option.calc.monthly_price.value | numberFormat '0,0' true true }}</div>
                                            <p class="per-month">{{ 'a month' | translate }}</p>

                                            <div v-if="isHirePayment">
                                                <div class="h-common">{{ option.calc.cash_deposit.value | numberFormat '0,0' true true }}</div>
                                                <p>{{ 'initial payment' | translate }}</p>
                                            </div>

                                            <p class="cashback">{{ 'Plus' | translate }} {{ option.calc.cashback.value | numberFormat '0,0' true }} {{ 'Deposit Reimbursement' | translate }}</p>
                                        </template>

                                        <template v-if="(!option.calc || !option.calc.monthly_price || option.calc.min_deposit_validation.value == 0) && option.pay_in_full == 0">
                                            <div class="h-common">{{ sliderInvalidCombinationText | translate }}</div>
                                        </template>

                                        <template v-if="option.calc && option.calc.product_price && option.pay_in_full == 1">
                                            <div class="h-common">{{ option.calc.product_price.value | numberFormat '0,0' true }}</div>
                                            <p class="cashback">{{ 'Plus' | translate }} {{ option.calc.cashback.value | numberFormat '0,0' true }} {{ 'Deposit Reimbursement' | translate }}</p>
                                        </template>
                                    </div>

                                    <div class="payment-methods-tabs-block-body" @click="changeActiveMethod($index)" v-else>
                                        <div class="h-common h-heavy">{{ option.group_title }}</div>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </div>

                    <div class="vertical-tab-content-container" v-el:container>
                        <template v-for="option in options">
                            <table class="table table-responsive table-borderless table-zebra" v-if="activeGroup == option.group_id">
                                <tbody v-if="!parseInt(option.is_static)">
                                    <tr style="display:none;">
                                        <td>{{ setTempVariables(option) }}</td>
                                    </tr>

                                    <tr v-for="vars in option.variables" :class="getVariableClassName(vars)">
                                        <td class="label">{{ parseVariableTitleMethod(vars.variable_title) }}</td>
                                        <td class="value" v-if="vars.value || vars.value == 0">{{ vars.value_formatted }}</td>
                                        <td class="value" v-else>{{ sliderInvalidCombinationText | translate }} </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td>{{{ option.footer }}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </div>
                </div>

                <div class="payment-methods-body">
                    <template v-for="option in options">
                        <div class="payment-methods-body-block" v-if="activeGroup == option.group_id && !parseInt(option.is_static)">
                            <div class="text-block option-video-block">{{{ option.video }}}</div>
                            <div class="text-block option-footer-block">{{{ option.footer }}}</div>
                            <div class="payment-methods-body-continue" v-if="!isStatic && !useShortVersion">
                                <button @click="isOptionAvailable(option) ? selectAndContinue() : ''" :class="{ 'button-disabled': !isOptionAvailable(option) }" :disabled="!isOptionAvailable(option)">{{ 'Select and Continue' | translate }}</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
        <template v-if="sessionError">
            <div class="wrapper">
                <div class="h-common h-heavy payment-title">{{ 'Session expired' | translate }}</div>
                <p class="payment-subtitle">
                    {{ 'Sorry, but your session expired, please refresh the page to continue' | translate }}
                </p>
            </div>
        </template>
    </div>
</template>

<script>
    import coreFinanceOverlay from 'core/components/FinanceOverlay';
    import FinanceOverlay from 'motorrad/components/Shared/FinanceOverlay';
    import numeral from 'numeral';
    import translateString from 'core/filters/Translate';
    import Constants from 'motorrad/components/Shared/Constants';

    export default coreFinanceOverlay.extend({
        mixins: [Constants, FinanceOverlay],

        data() {
            return {
                sliderInvalidCombinationText: 'Slider combination invalid',
                tradeinShortfallOnecashPayment: 2 // Settle trade-in shortfall in a single lump sum payment.
            }
        },

        methods: {
            translateString,

            getDataForAjaxRequest() {
                const data = {
                    productId: this.productId,
                    calc_type: this.calcType,
                    slider_data: {
                        term: this.financeParams.term,
                        mileage: this.financeParams.mileage,
                        deposit: this.financeParams.deposit,
                        depositMultiple: this.financeParams.depositMultiple,
                        maintenance: this.financeParams.maintenance,
                        balloonPercentage: this.financeParams.balloonPercentage,
                        method: this.activeGroup
                    }
                };

                if (this.calcType === 'pdp') {
                    data.productId = this.configurator.selectedCar;
                }

                return data;
            },

            cashPaymentSettlement() {
                this.$store.commit('setPXOutstandingFinanceSettlement', this.tradeinShortfallOnecashPayment);
                this.$store.commit('setPXValuationOutstandingFinance', this.tradeinShortfallOnecashPayment);
                EventsBus.$emit('PartExchangeFilter::outstandingFinanceSettlement', this.tradeinShortfallOnecashPayment);
            },

            selectAndContinue() {
                this.ajaxLoading = true;
                const data = {
                    group_id: this.activeGroup,
                    payment_type: this.activeMethodType,
                    method: this.activeGroup,
                    term: this.financeParams.term,
                    mileage: this.financeParams.mileage,
                    deposit: this.financeParams.deposit,
                    depositMultiple: this.financeParams.depositMultiple,
                    balloonPercentage: this.financeParams.balloonPercentage,
                    maintenance: this.isHirePaymentByGroupId(this.activeGroup) ? this.financeParams.maintenance : 0,
                    product_id: this.productId,
                    calc_type: this.calcType
                };

                if (this.calcType === 'pdp') {
                    data.product_id = this.configurator.selectedCar;
                } else if (this.calcType === 'quote') {
                    data.product_id = this.FinanceQuote.product_id;
                }

                this.financeParamsOrigin[this.activeGroup].term = this.financeParams.term;
                this.financeParamsOrigin[this.activeGroup].mileage = this.financeParams.mileage;
                this.financeParamsOrigin[this.activeGroup].deposit = this.financeParams.deposit;
                this.financeParamsOrigin[this.activeGroup].depositMultiple = this.financeParams.depositMultiple;
                this.financeParamsOrigin[this.activeGroup].balloonPercentage = this.financeParams.balloonPercentage;
                this.financeParamsOrigin[this.activeGroup].maintenance = this.financeParams.maintenance;
                this.activePayment.group_id = parseInt(this.activeGroup);
                this.activePayment.payment_type = this.activeMethodType;

                this.$http({
                    url: this.paymentSaveUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data
                }).then(this.selectAndContinueSuccess, this.selectAndContinueFail);
            },

            selectAndContinueSuccess(data) {
                EventsBus.$emit('ChooseVehicle::ChooseCar');
                EventsBus.$emit('FinanceQuote::updateUrlAfterClosingPopup', this.OVERLAYSEARCHPARAMS.FINANCE);

                if (this.updateFinanceQuote) {
                    this.ajaxLoading = false;
                    this.FinanceQuote.updateFinanceQuote(data);
                    this.$parent.show = false;
                    this.$dispatch('Main::paymentUpdated');
                    this.$store.commit('setFinanceGroupId', this.activeGroup);
                } else {
                    const productUrl = this.productUrl;

                    if (productUrl) {
                        window.location.href = productUrl;
                    }
                }

                this.dataReceived = true;
            },

            setBalloonPercentage() {
                if (isNaN(this.$store.state.general.balloonPercentage) && this.instalmentGroupId) {
                    this.$store.commit('setBalloonPercentage', this.financeParamsOrigin[this.instalmentGroupId].balloonPercentage);
                }
            }
        },

        computed: {
            configurator() {
                return this.$root.$refs.configurator;
            },

            bpSlider() {
                return this.$refs.balloonPercentageSlider;
            },

            isPayInFullGroup() {
                return this.payInFullPayment.find(payment => payment.group_id === this.activeGroup) !== undefined;
            },

            FinanceFilter() {
                try {
                    return this.$parent.$parent.$parent.$parent.FinanceFilter;
                } catch (e) {
                    // FinanceFilter is available only in car-finder
                }

                return false;
            }
        },

        filters: {
            numberPercentageFormat: {
                read(number) {
                    return numeral(Math.floor(number)).format('0') + this.translateString(' %');
                },

                write(number) {
                    return Math.floor(numeral(number).value());
                }
            }
        },

        events: {
            'FinanceOverlay::bp-update'(data) {
                this.$store.commit('setBalloonPercentage', data);
                this.financeParams.balloonPercentage = data;
                this.bpSlider.changeActive(data);
            }
        },

        props: {
            payInFullPayment: {
                required: true,
                type: Array
            },
            instalmentGroupId: {
                required: false,
                type: Number
            }
        },

        ready() {
            this.setBalloonPercentage();

            if (this.FinanceFilter) {
                this.financeParams.balloonPercentage = this.FinanceFilter.balloonPercentage;
            }

            if (!isNaN(this.financeParams.balloonPercentage) && this.instalmentGroupId) {
                this.bpSlider.changeSteps(this.financeSliderSteps[this.instalmentGroupId].balloonPercentage);
                this.bpSlider.changeActive(this.financeParams.balloonPercentage);
            }
        }
    });
</script>
