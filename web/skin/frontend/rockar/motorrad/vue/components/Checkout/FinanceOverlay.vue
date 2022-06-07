<template>
    <div id="choose-payment">
        <div class="general-preloader" v-show="ajaxLoading">
            <div class="show-loading"></div>
        </div>

        <div class="wrapper" v-show="dataReceived">
            <div class="h-common h-heavy payment-title">{{ 'Payment Options' | translate }}</div>
            <p class="payment-subtitle" v-show="showSlider || !isHirePayment">
                <template v-if="showSlider">
                    {{ 'Tailor your quote and monthly payments using the sliders below.' | translate }}
                </template>
                <template v-if="!isHirePayment">
                    {{ 'If you want to change the type of finance use the tabs on the left.' | translate }}
                </template>
            </p>
            <div class="payment-settings" v-show="showSlider && !isStatic">
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
                                    maxlength="7"
                                    class="keyboard-numbers"
                                />
                            </span>
                        </div>
                    </div>
                    <app-range-slider
                        :use-id="true"
                        :active-on-slide="true"
                        :active="financeParams.deposit"
                        :options="currentFinanceSteps.deposit"
                        custom-event="FinanceOverlay::conditionChanged"
                        custom-event-slide="FinanceOverlay::conditionChangeDeposit"
                        v-ref:deposit-slider
                    ></app-range-slider>
                </div>
                <div class="payment-settings-block settings-block-left" v-show="isHirePayment">
                    <div class="payment-settings-block-title">
                        <div class="h-common h-heavy">
                            {{ 'Deposit multiple' | translate }}
                            <span>{{ financeParams.depositMultiple | numberFormat '0' false }}</span>
                        </div>
                    </div>
                    <app-range-slider
                        :use-id="true"
                        :active-on-slide="true"
                        :active="financeParams.depositMultiple"
                        :options="currentFinanceSteps.depositMultiple"
                        custom-event="FinanceOverlay::conditionChanged"
                        custom-event-slide="FinanceOverlay::conditionChangeDepositMultiple"
                        :display-steps="true"
                        v-ref:deposit-multiple-slider
                    ></app-range-slider>

                    <div class="row">
                        <input
                            id="finance-maintenance"
                            type="checkbox"
                            v-model="financeParams.maintenance"
                            :true-value="1"
                            :false-value="0"
                        />
                        <label for="finance-maintenance">
                            <span></span>
                            {{ 'Add maintenance' }}
                        </label>
                    </div>
                </div>

                <div class="payment-settings-block settings-block-middle">
                    <div class="payment-settings-block-title">
                        <div class="h-common h-heavy">
                            {{ 'Number of Instalments' | translate }}
                            <span>{{ financeParams.term | numberFormat '0' false }}</span>
                        </div>
                    </div>
                    <app-range-slider
                        :use-id="true"
                        :active-on-slide="true"
                        :active="financeParams.term"
                        :options="currentFinanceSteps.term"
                        custom-event="FinanceOverlay::conditionChanged"
                        custom-event-slide="FinanceOverlay::conditionChangeTerm"
                        v-ref:term-slider
                    ></app-range-slider>
                </div>

                <div class="payment-settings-block settings-block-right" v-show="!isInstalmentSaleID(selectedActiveFinanceGroup)">
                    <div class="payment-settings-block-title">
                        <div class="h-common h-heavy">
                            {{ 'Contract End Mileage' | translate }}
                            <span>{{ financeParams.mileage | numberFormat '0,0' false }}</span>
                        </div>
                    </div>
                    <app-range-slider
                        :use-id="true"
                        :active-on-slide="true"
                        :active="financeParams.mileage"
                        :options="currentFinanceSteps.mileage"
                        custom-event="FinanceOverlay::conditionChanged"
                        custom-event-slide="FinanceOverlay::conditionChangeMileage"
                        v-ref:mileage-slider
                    ></app-range-slider>
                </div>

                <div class="payment-settings-block settings-block-right" v-show="isInstalmentSaleID(selectedActiveFinanceGroup)">
                    <div class="payment-settings-block-title">
                        <div class="h-common h-heavy">
                            {{ 'Balloon Percentage' | translate }}
                            <span>{{ financeParams.balloonPercentage | numberPercentageFormat }}</span>
                        </div>
                    </div>
                    <app-range-slider
                        :use-id="true"
                        :active-on-slide="true"
                        :active="financeParams.balloonPercentage"
                        :options="currentFinanceSteps.balloonPercentage"
                        custom-event="FinanceOverlay::conditionChanged"
                        custom-event-slide="FinanceOverlay::conditionChangeBalloonPercentage"
                        v-ref:balloon-percentage-slider
                    ></app-range-slider>
                </div>
            </div>

            <template v-for="option in options">
                <div
                    class="payment-methods-title-block"
                    v-show="activeGroup == option.group_id"
                    :data-id="option.group_title.toLowerCase()"
                >
                    <table class="title-block">
                        <tr>
                            <td class="text-block option-header-block">
                                <div>
                                    {{{ option.header }}}
                                    <template v-if="!isStatic">
                                        <button
                                            @click="isOptionAvailable(option) ? selectAndContinueApprove() : ''"
                                            :class="{ 'button-disabled': !isOptionAvailable(option) }"
                                            :disabled="!isOptionAvailable(option)"
                                        >
                                            {{ 'Select and Continue' | translate }}
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </template>

            <div class="vertical-tabs-container">
                <div class="payment-methods" v-el:methods>
                    <div class="payment-methods-tabs">
                        <template v-for="option in options">
                            <div
                                class="payment-methods-tabs-block"
                                :class="[activeGroup == option.group_id ? 'active' : '']"
                                v-if="checkActive(option)"
                                :data-id="option.group_title.toLowerCase()"
                            >
                                <div
                                    class="payment-methods-tabs-block-body"
                                    @click="changeActiveMethod($index)"
                                    v-if="!parseInt(option.is_static)"
                                >
                                    <div class="h-common h-heavy">{{ option.group_title }}</div>

                                    <p v-if="option.calc && option.pay_in_full == 0 &&
                                        option.calc.min_deposit_validation.value == 0"
                                    >
                                        {{ 'More Deposit required' | translate }}
                                    </p>

                                    <template v-if="option.calc &&
                                        option.calc.monthly_price &&
                                        option.pay_in_full == 0 &&
                                        option.calc.min_deposit_validation.value == 1"
                                    >
                                        <div class="h-common">
                                            {{ option.calc.monthly_price.value | numberFormat '0,0' true true }}
                                        </div>
                                        <p class="per-month">{{ 'a month' | translate }}</p>

                                        <div v-if="isHirePayment">
                                            <div class="h-common">
                                                {{ option.calc.cash_deposit.value | numberFormat '0,0' true true }}
                                            </div>
                                            <p>{{ 'initial payment' | translate }}</p>
                                        </div>

                                        <p class="cashback">
                                            {{ 'plus' | translate }}
                                            {{ option.calc.cashback.value | numberFormat '0,0' true }}
                                            {{ 'Deposit Reimbursement' | translate }}
                                        </p>
                                    </template>

                                    <template v-if="(!option.calc ||
                                        !option.calc.monthly_price ||
                                        option.calc.min_deposit_validation.value == 0) &&
                                        option.pay_in_full == 0"
                                    >
                                        <div class="h-common">{{ sliderInvalidCombinationText | translate }}</div>
                                    </template>

                                    <div v-if="option.calc && option.calc.product_price && option.pay_in_full == 1">
                                        <div class="h-common">
                                            {{ option.calc.product_price.value | numberFormat '0,0' true }}
                                        </div>
                                        <p class="cashback">
                                            {{ 'plus' | translate }}
                                            {{ option.calc.cashback.value | numberFormat '0,0' true }}
                                            {{ 'Deposit Reimbursement' | translate }}
                                        </p>
                                    </div>
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
                        <table
                            class="table table-responsive table-borderless table-zebra"
                            v-if="activeGroup == option.group_id"
                        >
                            <tbody v-if="!parseInt(option.is_static)">
                            <tr style="display:none;">
                                <td>{{ setTempVariables(option) }}</td>
                            </tr>

                            <tr v-for="vars in option.variables" :class="getVariableClassName(vars)">
                                <td class="label">{{ parseVariableTitleMethod(vars.variable_title) }}</td>
                                <td class="value" v-if="vars.value || vars.value == 0">
                                    {{ vars.value_formatted }}
                                </td>
                                <td class="value" v-else>{{ sliderInvalidCombinationText | translate }}</td>
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
                    <div
                        class="payment-methods-body-block"
                        v-show="activeGroup == option.group_id"
                        v-if="!parseInt(option.is_static)"
                    >
                        <div class="text-block option-video-block">{{{ option.video }}}</div>
                        <div class="text-block option-footer-block">{{{ option.footer }}}</div>
                        <div class="payment-methods-body-continue" v-if="!isStatic">
                            <button
                                @click="isOptionAvailable(option) ? selectAndContinueApprove() : ''"
                                :class="{ 'button-disabled': !isOptionAvailable(option) }"
                                :disabled="!isOptionAvailable(option)"
                            >
                                {{ 'Select and Continue' | translate }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    <app-modal :show="!!this.checkoutErrorMessage" :show-close="false" class-name="simple-popup error-popup">
        <div slot="content">
            <h2 v-if="checkoutErrorMessageTitle">{{ checkoutErrorMessageTitle }}</h2>
            <h2 v-else>{{ 'Something went wrong!' | translate }}</h2>
            <div v-html="checkoutErrorMessage"></div>
            <div class="align-right row">
                <div class="col-12">
                    <button type="button" name="button" class="button button-confirm" @click.prevent="processFailure()">{{ 'Back' | translate }}</button>
                </div>
            </div>
        </div>
    </app-modal>
</template>

<script>
    import appRangeSlider from 'core/components/Elements/RangeSlider';
    import FinanceOverlay from 'motorrad/components/Shared/FinanceOverlay';
    import appModal from 'core/components/Elements/Modal';

    import numeral from 'numeral';
    import translateString from 'core/filters/Translate';

    export default Vue.extend({
        mixins: [FinanceOverlay],

        components: {
            appRangeSlider,
            appModal
        },

        props: {
            confirmationOverlayContent: {
                required: true,
                type: String
            },
            confirmationOverlayContentFull: {
                required: false,
                type: String,
                default: ''
            },
            disagreeOverlayContent: {
                required: true,
                type: String
            },
            confirmationUrl: {
                required: true,
                type: String
            },
            payInFullPayment: {
                required: true,
                type: Array
            },
            instalmentGroupId: {
                required: false,
                type: Number
            }
        },

        data() {
            return {
                approveContinue: false,
                approveDisagree: false,
                confirmationAgree: 1,
                confirmationDisagree: 2,
                showFullPopupContent: false,
                sliderInvalidCombinationText: 'Slider combination invalid',
                checkoutErrorMessageTitle: false,
                checkoutErrorMessage: false,
                invalidProductRedirectUrl: false,
            };
        },

        computed: {
            openStep() {
                return this.$parent.$parent.openStep;
            },

            bpSlider() {
                return this.$refs.balloonPercentageSlider;
            },

            storeBalloonPercentage() {
                return this.$store.state.general.balloonPercentage;
            },

            selectedActiveFinanceGroup() {
                return parseInt(this.activeGroup);
            },

            financeActivePaymentGroupId() {
                return this.$store.state.finance.financeGroupId;
            },

            isPayInFullGroup() {
                return this.payInFullPayment.find(payment => payment.group_id === this.financeActivePaymentGroupId)
                    !== undefined;
            }
        },

        events: {
            'FinanceOverlay::conditionChangeBalloonPercentage'(data) {
                this.$store.commit('setBalloonPercentage', data);
                this.financeParams.balloonPercentage = data;
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
                    const configurator = this.$root.$refs.configurator;
                    data.productId = configurator.selectedCar;
                }

                return data;
            },

            selectAndContinueApprove() {
                this.selectAndContinue();
            },

            selectAndContinueApproveSendAction(action) {
                this.$http({
                    url: this.confirmationUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: { action }
                })
            },

            selectAndContinueApproveSuccess() {
                this.ajaxLoading = true;
                this.approveContinue = false;
                this.selectAndContinue();
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
                    maintenance: this.financeParams.maintenance,
                    product_id: this.productId,
                    calc_type: this.calcType,
                    balloonPercentage: this.financeParams.balloonPercentage,
                    step: this.$parent.getNextStepIndex(),
                    action: this.confirmationAgree
                };

                if (this.calcType === 'pdp') {
                    const configurator = this.$root.$refs.configurator;
                    data.product_id = configurator.selectedCar;
                }

                this.financeParamsOrigin[this.activeGroup].term = this.financeParams.term;
                this.financeParamsOrigin[this.activeGroup].mileage = this.financeParams.mileage;
                this.financeParamsOrigin[this.activeGroup].deposit = this.financeParams.deposit;
                this.financeParamsOrigin[this.activeGroup].depositMultiple = this.financeParams.depositMultiple;
                this.financeParamsOrigin[this.activeGroup].maintenance = this.financeParams.maintenance;
                this.financeParamsOrigin[this.activeGroup].balloonPercentage = this.financeParams.balloonPercentage;
                this.activePayment.group_id = this.activeGroup;
                this.activePayment.payment_type = this.activeMethodType;

                this.$http({
                    url: this.paymentSaveUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data
                }).then(this.selectAndContinueSuccess, this.selectAndContinueFail);
            },

            cashPaymentSettlement() {
                this.$store.commit('setPXOutstandingFinanceSettlement', this.tradeinShortfallOnecashPayment);
                this.$store.commit('setPXValuationOutstandingFinance', this.tradeinShortfallOnecashPayment);
                EventsBus.$emit('PartExchangeFilter::outstandingFinanceSettlement', this.tradeinShortfallOnecashPayment);
            },

            selectAndContinueSuccess(response) {
                if (!response.data.hasOwnProperty('finance_variables')) {
                    alert(this.translateString('Something went wrong. Please, try again later.'));
                    this.ajaxLoading = false;

                    return;
                }

                if (this.isPayInFullGroup) {
                    this.cashPaymentSettlement();
                }

                if (this.updateFinanceQuote) {
                    this.ajaxLoading = false;
                    this.FinanceQuote.updateFinanceQuote(response);
                    this.$parent.show = false;
                }
                this.dataReceived = true;

                // Login page is not in accordion group, indexes start with 0
                // So we must add 2 to compensate for that
                const stepIndex = this.openStep + 1,
                    paymentMethodSelectedObject = {
                        'event': 'checkoutOption',
                        'ecommerce': {
                            'checkout_option': {
                                'actionField': {
                                    'step': stepIndex,
                                    'option': this.activeMethodType
                                }
                            }
                        }
                    };

                pushEcommerceTags(paymentMethodSelectedObject);
                this.dataReceived = true;

                /**
                 * Update financeQuote
                 */
                this.prepareFinanceDataBeforeSend();

                this.$root.$refs.summaryStep.carExtras = response.data.car_data;
                this.$dispatch('CheckoutAccordionGroup::nextStep', this.$parent.stepCode);
            },

            prepareFinanceDataBeforeSend() {
                /**
                 * Update financeQuote
                 */
                this.$root.$refs.financeQuote.financeParams.term = this.financeParams.term;
                this.$root.$refs.financeQuote.financeParams.deposit = this.financeParams.deposit;
                this.$root.$refs.financeQuote.financeParams.mileage = this.financeParams.mileage;
                this.$root.$refs.financeQuote.financeParams.depositMultiple = this.financeParams.depositMultiple;
                this.$root.$refs.financeQuote.financeParams.maintenance = this.financeParams.maintenance;
                this.$root.$refs.financeQuote.financeParams.balloonPercentage = this.financeParams.balloonPercentage;
                this.$root.$refs.financeQuote.group_id = parseInt(this.activeGroup);
                this.$root.$refs.financeQuote.payment_type = this.activeMethodType;
                this.$root.$refs.financeQuote.activePayment.group_id = parseInt(this.activeGroup);
                this.$root.$refs.financeQuote.activePayment.payment_type = this.activeMethodType;
            },

            selectAndContinueFail(resp) {
                this.ajaxLoading = false;

                if (resp.data.redirect && resp.status === 401) {
                    this.$root.loggedOutPopup(resp.data.redirect);
                    return;
                }

                if (resp.data.redirect && resp.status === 400 && resp.data.out_of_stock) {
                    if (resp.data.message) {
                        this.checkoutErrorMessage = resp.data.message;

                        if (resp.data.message_title) {
                            this.checkoutErrorMessageTitle = resp.data.message_title;
                        }
                    }

                    this.invalidProductRedirectUrl = resp.data.redirect;

                    return;
                }

                if (resp.data.redirect) {
                    window.location.href = resp.data.redirect;

                    return;
                }

                if (resp.data.error) {
                    alert(resp.data.error);
                    return;
                }

                alert(resp.statusText);
            },

            processFailure() {
                window.location.href = this.invalidProductRedirectUrl ? this.invalidProductRedirectUrl : '';
            },

            beforeGetDataByProductId() {
                this.prepareFinanceDataBeforeSend();
                this.$dispatch('Main::progressUpdateFinanceQuote');
            },

            getDataByProductIdFail(resp) {
                this.ajaxLoading = false;
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

                alert(resp.statusText);
            },

            selectAndContinueApproveFail() {
                this.approveContinue = false;
                this.approveDisagree = true;
                this.selectAndContinueApproveSendAction(this.confirmationDisagree);
            },

            isOptionAvailable(method) {
                return method.calc && method.calc.monthly_price && method.calc.min_deposit_validation.value === true;
            },

            getStatusBar() {
                let statusBar = '';
                const statusAction = 'edit';
                for (let i = 0; i < this.options.length; i++) {
                    const option = this.options[i];
                    if (parseInt(option.group_id) === parseInt(this.activeGroup)) {
                        if (this.payInFullPayment.find(payment => payment.group_id === option.group_id) !== undefined) {
                            statusBar = `${option.group_title} ${currencySymbol}${numeral(
                                option.calc.product_price.value
                            ).format('0,0')}`;
                        } else {
                            statusBar = `${option.group_title} AT ${currencySymbol}${numeral(
                                option.calc.monthly_price.value
                            ).format('0,0')} per month`;
                        }
                    }
                }

                return { message: statusBar, action: statusAction };
            },

            changeActiveMethod(index) {
                if (this.activeMethod !== index) {
                    this.activeMethod = index;
                    this.activeMethodType = this.options[index].type;
                    this.activeGroup = this.options[index].group_id;
                    this.$store.commit('setFinanceGroupId', this.activeGroup);
                    this.showSlider = parseInt(this.options[index].pay_in_full) === 0;
                    setTimeout(this.setSameHeight, 100);

                    this.allowAjaxUpdate = false;
                    this.currentSliderSteps = this.financeSliderSteps[this.activeGroup];

                    if (this.currentSliderSteps && !parseInt(this.options[index].is_static)) {
                        const depositSteps = this.currentSliderSteps.deposit;
                        const deposit = this.findClosestValue(depositSteps, parseInt(this.financeParams.deposit));
                        this.depositSliderRef.changeSteps(this.currentSliderSteps.deposit);
                        this.depositSliderRef.changeActive(deposit);
                        this.financeParams.deposit = deposit;

                        const termSteps = this.currentSliderSteps.term;
                        const term = this.findClosestValue(termSteps, parseInt(this.financeParams.term));
                        this.termSliderRef.changeSteps(termSteps);
                        this.termSliderRef.changeActive(term);
                        this.financeParams.term = term;

                        const mileageSteps = this.currentSliderSteps.mileage;
                        const mileage = this.findClosestValue(mileageSteps, parseInt(this.financeParams.mileage));
                        this.mileageSliderRef.changeSteps(mileageSteps);
                        this.mileageSliderRef.changeActive(mileage);
                        this.financeParams.mileage = mileage;

                        const balloonPercentageSteps = this.currentSliderSteps.balloonPercentage;
                        const balloonPercentage = this.findClosestValue(
                            balloonPercentageSteps,
                            parseInt(this.financeParams.balloonPercentage)
                        );
                        this.bpSlider.changeSteps(balloonPercentageSteps);
                        this.bpSlider.changeActive(balloonPercentage);
                        this.financeParams.balloonPercentage = balloonPercentage;

                        this.allowAjaxUpdate = true;
                        this.getDataByProductId();
                    }
                }
            },

            setBalloonPercentage() {
                if (!this.$store.state.general.balloonPercentage && this.instalmentGroupId) {
                    this.$store.commit('setBalloonPercentage', this.financeParamsOrigin[this.instalmentGroupId].balloonPercentage);
                }
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

        watch: {
            '$parent.show'(result) {
                if (result) {
                    this.getDataByProductId();
                }
            }
        },

        beforeCompile() {
            this.allowAjaxUpdate = false;
            this.activeGroup = parseInt(this.activePayment.group_id);
            this.setFinanceParams(this.financeParamsOrigin[this.activeGroup]);

            this.activeMethodType = this.financeParams.payment_type;
            this.previousValue = {
                deposit: this.financeParams.deposit,
                term: this.financeParams.term,
                mileage: this.financeParams.mileage,
                balloonPercentage: this.financeParams.balloonPercentage,
                depositMultiple: this.financeParams.depositMultiple,
                maintenance: this.financeParams.maintenance
            };

            this.currentFinanceSteps = this.financeSliderSteps[this.activeGroup];
            this.allowAjaxUpdate = true;
        },

        ready() {
            this.setBalloonPercentage();
            if (this.storeBalloonPercentage && this.instalmentGroupId) {
                this.financeParams.balloonPercentage = parseInt(this.storeBalloonPercentage);
                this.bpSlider.changeSteps(this.financeSliderSteps[this.instalmentGroupId].balloonPercentage);
                this.bpSlider.changeActive(this.storeBalloonPercentage);
            }
        }
    });
</script>
