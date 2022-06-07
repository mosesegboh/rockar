<template>
    <div id="choose-payment">
        <div class="general-preloader" v-show="ajaxLoading">
            <div class="show-loading"></div>
        </div>

        <div class="wrapper" v-show="dataReceived">
            <div class="step-header">
                {{ stepHeader }}
            </div>
            <div class="vertical-tabs-container">
                <div class="payment-methods" v-el:methods>
                    <div class="payment-methods-tabs">
                        <template v-for="(index, option) in options">
                            <div
                                class="payment-methods-tabs-block"
                                :class="[activeGroup == option.group_id ? 'active' : '']"
                                v-if="checkActive(option)"
                                :data-id="option.group_title.toLowerCase()"
                                :key="index"
                            >
                                <div
                                    class="payment-methods-tabs-block-body"
                                    @click="changeActiveMethod($index)"
                                    v-if="!parseInt(option.is_static)"
                                >
                                    <div class="h-common h-heavy">{{ option.group_full_title }}</div>

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
                                        <div class="h-common price">
                                            {{ option.calc.monthly_price.value | numberFormat '0,0' true true }}
                                        </div>
                                        <p class="per-month">{{ 'p/m' | translate }}</p>
                                    </template>

                                    <template v-if="(!option.calc ||
                                        !option.calc.monthly_price ||
                                        option.calc.min_deposit_validation.value == 0) &&
                                        option.pay_in_full == 0"
                                    >
                                        <div class="invalid">
                                            <span class="icon-info-grey"></span>
                                            <span>{{ sliderInvalidCombinationText | translate }}</span>
                                        </div>
                                    </template>

                                    <div v-if="option.calc && option.calc.product_price && option.pay_in_full == 1">
                                        <div class="h-common price">
                                            {{ option.calc.product_price.value | numberFormat '0,0' true }}
                                        </div>
                                    </div>
                                </div>
                                <div class="payment-methods-tabs-block-body" @click="changeActiveMethod($index)" v-else>
                                    <div class="h-common h-heavy">{{ option.group_title }}</div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="inner-container" v-el:inner-container>
                <div class="sliders-container">
                    <div class="image-container">
                        <img :src="image" :alt="imageAlt"/>
                    </div>
                    <div class="select-container" v-if="dataReceived">
                        <app-select
                            @select="selectFinance"
                            :options="selectOptions"
                            :init-selected="activeMethod"
                            label="Choose Payment Option">
                        </app-select>
                    </div>
                    <div class="payment-settings" v-show="showSlider && !isStatic">
                        <p class="heading" v-show="showSlider">{{ 'Tailor your quote' | translate }}</p>
                        <div class="payment-settings-block" v-show="!isHirePayment">
                            <div class="payment-settings-block-title">
                                <div class="h-common h-heavy">
                                    {{ 'Deposit' | translate }}
                                </div>
                            </div>
                            <div class="range-sliders-container">
                                <app-range-slider
                                    :use-id="true"
                                    :active-on-slide="true"
                                    :active="financeParams.deposit"
                                    :options="currentFinanceSteps.deposit"
                                    custom-event="FinanceOverlay::conditionChanged"
                                    custom-event-slide="FinanceOverlay::conditionChangeDeposit"
                                    v-ref:deposit-slider
                                ></app-range-slider>
                                <span class="payment-settings-block-title-input">
                                <input
                                    type="text"
                                    v-model="financeParams.deposit | numberCurrencyFormat"
                                    @keyup.enter="changeValue('deposit')"
                                    @blur="changeValue('deposit')"
                                    size="10"
                                    maxlength="10"
                                    class="keyboard-numbers"
                                />
                            </span>
                            </div>
                        </div>
                        <div class="payment-settings-block" v-show="isHirePayment">
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
                                    {{ 'Add maintenance' | translate }}
                                </label>
                            </div>
                        </div>

                        <div class="payment-settings-block">
                            <div class="payment-settings-block-title">
                                <div class="h-common h-heavy">
                                    {{ numberOfInstalments }}
                                </div>
                            </div>
                            <div class="range-sliders-container">
                                <app-range-slider
                                    :use-id="true"
                                    :active-on-slide="true"
                                    :active="financeParams.term"
                                    :options="currentFinanceSteps.term"
                                    custom-event="FinanceOverlay::conditionChanged"
                                    custom-event-slide="FinanceOverlay::conditionChangeTerm"
                                    v-ref:term-slider
                                ></app-range-slider>
                                <span class="payment-settings-block-title-input">
                                <input
                                    type="text"
                                    v-model="financeParams.term | numberMonthsFormat"
                                    size="10"
                                    maxlength="10"
                                    class="keyboard-numbers"
                                    readonly
                                />
                            </span>
                            </div>
                        </div>

                        <div class="payment-settings-block" v-show="!isInstalmentSaleID(selectedActiveFinanceGroup)">
                            <div class="payment-settings-block-title">
                                <div class="h-common h-heavy">
                                    {{ contractEndMileage }}
                                </div>
                            </div>
                            <div class="range-sliders-container">
                                <app-range-slider
                                    :use-id="true"
                                    :active-on-slide="true"
                                    :active="financeParams.mileage"
                                    :options="currentFinanceSteps.mileage"
                                    custom-event="FinanceOverlay::conditionChanged"
                                    custom-event-slide="FinanceOverlay::conditionChangeMileage"
                                    v-ref:mileage-slider
                                ></app-range-slider>
                                <span class="payment-settings-block-title-input">
                                <input
                                    type="text"
                                    v-model="financeParams.mileage | numberKilometerFormat"
                                    size="10"
                                    maxlength="10"
                                    class="keyboard-numbers"
                                    readonly
                                />
                            </span>
                            </div>
                        </div>

                        <div class="payment-settings-block" v-show="isInstalmentSaleID(selectedActiveFinanceGroup)">
                            <div class="payment-settings-block-title">
                                <div class="h-common h-heavy">
                                    {{ 'Balloon Percentage' | translate }}
                                </div>
                            </div>
                            <div class="range-sliders-container">
                                <app-range-slider
                                    :use-id="true"
                                    :active-on-slide="true"
                                    :active="financeParams.balloonPercentage"
                                    :options="currentFinanceSteps.balloonPercentage"
                                    custom-event="FinanceOverlay::conditionChanged"
                                    custom-event-slide="FinanceOverlay::conditionChangeBalloonPercentage"
                                    v-ref:balloon-percentage-slider
                                ></app-range-slider>
                                <span class="payment-settings-block-title-input">
                                <input
                                    type="text"
                                    v-model="financeParams.balloonPercentage | numberPercentageFormat"
                                    size="10"
                                    maxlength="10"
                                    class="keyboard-numbers"
                                    readonly
                                />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-for="(index, option) in options" :key="index">
                    <div
                        class="ffo-header"
                        v-show="parseInt(activeGroup) === parseInt(option.group_id)"
                        :data-id="option.group_title.toLowerCase()"
                        v-html="option.header"
                    ></div>
                    <div v-if="(parseInt(activeGroup) === parseInt(option.group_id)) && openStep === CHECKOUT_FINANCE_STEP" class="video-container">
                        <div v-html="option.video"></div>
                    </div>
                    <div v-if="parseInt(activeGroup) === parseInt(option.group_id)" class="ffo-video" @click="showVideoPopup = !showVideoPopup">
                        <span>{{ 'Watch Video' | translate }}</span>
                    </div>
                    <div
                        class="option-footer-block"
                        v-show="parseInt(activeGroup) === parseInt(option.group_id)"
                        v-html="option.footer"
                    ></div>
                    <div class="payment-methods-body-continue" v-if="!isStatic" v-show="parseInt(activeGroup) === parseInt(option.group_id)">
                        <button
                            class="button dsp2-money"
                            :class="{ 'button-disabled': !isOptionAvailable(option) }"
                            :disabled="!isOptionAvailable(option)"
                            @click="isOptionAvailable(option) ? selectAndContinueApprove() : ''"
                        >
                            {{ 'Save and continue' | translate }}
                        </button>
                    </div>
                </div>

                <app-modal
                    :class="'finance-filter-overlay-video'"
                    :show="showVideoPopup"
                    @close-popup="showVideoPopup = !showVideoPopup">
                    <div slot="content">
                        <template v-for="(index, option) in options">
                            <div v-if="(parseInt(activeGroup) === parseInt(option.group_id)) && showVideoPopup" :key="index">
                                <div v-html="option.video"></div>
                            </div>
                        </template>
                    </div>
                </app-modal>
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
    </div>
</template>

<script>
    import appRangeSlider from 'core/components/Elements/RangeSlider';
    import FinanceOverlay from 'dsp2/components/Shared/FinanceOverlay';
    import appModal from 'core/components/Elements/Modal';
    import appSelect from 'dsp2/components/Elements/Select';
    import numeral from 'numeral';
    import translateString from 'core/filters/Translate';
    import Constants from 'dsp2/components/Shared/Constants';
    import EventTrackerFinanceOverlay from 'dsp2/mixins/EventTrackerFinanceOverlay';

    export default Vue.extend({
        mixins: [FinanceOverlay, Constants, EventTrackerFinanceOverlay],

        components: {
            appRangeSlider,
            appModal,
            appSelect
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
            },
            image: {
                required: false,
                type: String,
                default: ''
            },
            imageAlt: {
                required: false,
                type: String,
                default: ''
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
                showVideoPopup: false,
                quoteUpdateTimer: null
            };
        },

        computed: {
            selectOptions() {
                const result = [];

                Object.values(this.options).forEach((item, index) => {
                    result.push({
                        index,
                        title: item.group_full_title,
                        value: item.group_id
                    });
                });

                return result;
            },

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
            },

            stepHeader() {
                return this.translateString('Choose your payment option');
            },

            numberOfInstalments() {
                return this.translateString('Number of Instalments');
            },

            contractEndMileage() {
                return this.translateString('Contract End Mileage');
            }
        },

        events: {
            'FinanceOverlay::conditionChangeBalloonPercentage'(data) {
                this.$store.commit('setBalloonPercentage', data);
                this.financeParams.balloonPercentage = data;
                this.updateQuoteWithTimer();
            },

            'ProductPod::onDataSuccess'() {
                if (this.openStep === this.CHECKOUT_FINANCE_STEP) {
                    this.setVideoListener();
                }
            },

            'FinanceOverlay::conditionChangeDeposit'() {
                this.updateQuoteWithTimer();
            },

            'FinanceOverlay::conditionChangeDepositMultiple'() {
                this.updateQuoteWithTimer();
            },

            'FinanceOverlay::conditionChangeTerm'() {
                this.updateQuoteWithTimer();
            },

            'FinanceOverlay::conditionChangeMileage'() {
                this.updateQuoteWithTimer();
            }
        },

        methods: {
            translateString,

            updateQuote() {
                if (this.getLoader('updatingQuote')) {
                    // Error will be shown in console but no functionality affected
                    return Promise.reject(new Error('Simultaneous/duplicated calls to the finance save api.'));
                }

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
                }).then(this.updateQuoteSuccess, this.updateQuoteFail);

                this.setLoader('updatingQuote');
            },

            updateQuoteSuccess(response) {
                if (!response.data.hasOwnProperty('finance_variables')) {
                    alert(this.translateString('Something went wrong. Please, try again later.'));
                    this.setLoader('updatingQuote', false)
                    this.ajaxLoading = false;

                    return;
                }

                this.FinanceQuote.updateFinanceQuote(response);

                this.ajaxLoading = false;
                this.setLoader('updatingQuote', false);
            },

            updateQuoteFail(resp) {
                this.ajaxLoading = false;
                this.setLoader('updatingQuote', false);
                console.error(resp.statusText);
            },

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
                        this.setVideoListener();
                        this.updateQuote();
                    }
                }
            },

            setBalloonPercentage() {
                if (!this.$store.state.general.balloonPercentage && this.instalmentGroupId) {
                    this.$store.commit('setBalloonPercentage', this.financeParamsOrigin[this.instalmentGroupId].balloonPercentage);
                }
            },

            selectFinance(data) {
                this.changeActiveMethod(data.index);
            },

            setVideoListener() {
                this.$nextTick(() => {
                    this.$root.toggleVideoPlayButton();
                });
            },

            changeValue(param) {
                const minValue = this.currentFinanceSteps[param][0].id;
                this.financeParams[param] = this.financeParams[param] < minValue ? minValue : this.financeParams[param];

                if (this.previousValue[param] !== this.financeParams[param]) {
                    this.previousValue[param] = this.financeParams[param];
                    this.$refs[`${param}Slider`].changeActive(this.financeParams[param]);
                    this.getDataByProductId();
                    this.updateQuote();
                }
            },

            updateQuoteWithTimer() {
                clearTimeout(this.quoteUpdateTimer);

                this.quoteUpdateTimer = setTimeout(() => {
                    this.updateQuote()
                }, 1500);
            }
        },

        watch: {
            '$parent.show'(result) {
                if (result) {
                    this.getDataByProductId();
                }
            },

            'openStep'(result) {
                if (result === this.CHECKOUT_FINANCE_STEP) {
                    this.setVideoListener();
                    /**
                     * Fire event for tracking purposes on initial load of finance overlay
                     */
                    this.fireFinEventCheckout();
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
        },

        compiled() {
            const self = this;
            jQuery(this.$els.innerContainer).off('click.ffoHeader').on('click.ffoHeader', '.ffo-header h3', () => {
                jQuery(self.$els.innerContainer.querySelectorAll('.ffo-header')).toggleClass('show');
            });
        }
    });
</script>
