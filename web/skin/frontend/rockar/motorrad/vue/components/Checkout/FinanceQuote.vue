<template>
    <div class="finance-quote-wrapper">
        <div class="general-preloader" v-show="ajaxLoading">
            <div class="show-loading"></div>
        </div>
        <div class="finance-quote-configurator">
            <div class="general-preloader" v-show="ajaxLoading">
                <div class="show-loading"></div>
            </div>
            <div class="finance-quote grid no-padding-bottom">
                <div class="finance-quote-header row">
                    <div class="fq-heading h-common">{{ 'Your Quotation' | translate }}</div>
                </div>
                <div class="finance-quote-content">
                    <div class="car-data-wrapper">
                        <template v-for="data in carDataSorted">
                            <div v-if="!data.group && data.type !== 'additional_fee'">
                                <div class="car-data-label">
                                    <span>{{ data.label }}</span>
                                </div>
                                <div class="car-data-value">
                                    <span class="car-road-value">{{ isHire ? 'Base Rental Price' : 'Offer Price' | translate }}</span>
                                    <span class="car-data-price">{{ getFormattedValue(data) }}</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="finance-quote-scroll" v-el:finance-quote-options-wrapper>
                <template v-for="(dataIndex, data) in carDataSorted">
                    <div v-if="data.group && data.items.length"
                         :class="[groupsOpenStatus[dataIndex] ? 'block-expand' : 'block-collapse', 'finance-quote', 'grid', 'no-padding-top', 'no-padding-bottom', 'group-block']">
                        <div class="finance-quote-content">
                            <table :class="['table', 'table-two']">
                                <tbody>
                                <tr @click="toggleOptionGroupHandler(dataIndex)" :class="data.group">
                                    <td>
                                        <span class="group-title">{{ data.group }}</span>
                                        <a :class="[groupsOpenStatus[dataIndex] ? 'expand' : 'collapse']"></a>
                                    </td>
                                    <td>
                                        <span class="table-right">{{ getGroupPrice(data) }}</span>
                                    </td>
                                </tr>
                                <tr v-for="item in data.items" v-show="groupsOpenStatus[dataIndex]">
                                    <td>
                                        <app-confirmation-modal
                                                :confirmation-question="'Do you really want to Remove?' | translate">
                                        </app-confirmation-modal>
                                        <span :class="[!item.remove ? 'no-icon' : '']">{{ item.label | convertNCR }}</span>
                                    </td>
                                    <td>
                                        <span class="table-right">{{ getFormattedValue(item) }}</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <div class="car-data-wrapper finance-quote no-padding-top">
                    <div v-for="data in additionalFeesSorted">
                        <div class="minor-car-data" :class="formatAdditionalFeeName(data.label)">
                            <span class="variable-title">{{ data.label }}
                                <span class="ved-info-trigger" v-if="data.info" @click="data.show = true">{{ '- info' | translate }}</span>
                                <div v-if="data.info">
                                    <app-modal :show.sync="data.show" v-show="data.show">
                                       <div slot="content">
                                           <div class="ved-info-modal">{{{data.info}}}</div>
                                       </div>
                                    </app-modal>
                                </div>
                            </span>
                            <span class="right">{{ getFormattedValue(data) }}</span>
                        </div>
                    </div>
                </div>

                <div class="finance-quote grid no-padding-top overview">
                    <div class="finance-quote-content">
                        <table class="table">
                            <tr :class="[vars.css_classes, vars.variable]" v-for="vars in financeVariables">
                                <td>
                                    <span class="variable-title">{{ parseVariableTitleMethod(vars.variable_title) }}</span>
                                </td>
                                <td>
                                    <span class="table-right" v-if="vars.value || vars.value == 0">{{{ vars.value_formatted }}}</span>
                                    <span class="table-right" v-else>{{ sliderInvalidCombinationText | translate }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="finance-payments" v-if="!isHire">
                <div class="payment">
                    <p class="payment-amount">{{ rockarPrice | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Offer Price' | translate }}</p>
                </div>

                <div class="payment" v-if="saveOffRrp">
                    <p class="payment-amount">{{ saveOffRrp | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'off RRP' | translate }}</p>
                </div>

                <div class="payment" v-if="monthlyPrice != 0">
                    <p class="payment-amount">{{ monthlyPrice | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'A month' | translate }}</p>
                </div>
            </div>


            <div class="finance-payments" v-if="isHire">
                <div class="payment">
                    <p class="payment-amount">{{ cashDeposit | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Initial payment' | translate }}</p>
                </div>

                <div class="payment" v-if="cashback > 0">
                    <p class="payment-amount">{{ cashback | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Cash Back' | translate }}</p>
                </div>

                <div class="payment" v-if="monthlyPrice != 0">
                    <p class="payment-amount">{{ monthlyPrice | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'A month' | translate }}</p>
                </div>
            </div>

            <div class="finance-delivery" v-if="leadTime">
                <p class="lead-time">{{ leadTime | convertNCR }}</p>
            </div>

            <div class="coupon" v-if="couponFormUrl">
                <div class="coupon-form-wrapper">
                    <form id="coupon-form" method="post">
                        <div class="row">
                            <div class="col-12">
                                <div v-if="couponCode === ''" class="apply-voucher">
                                    <div class="coupon-label">
                                        <p class="coupon-label-text">{{ 'Do you have a voucher code?' | translate }}</p>
                                        <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400" v-if="couponTooltipText !== ''">
                                            <span class="action-badge info-small" slot="tooltipElement"></span>

                                            <div slot="tooltipContent">
                                                <p>{{ couponTooltipText }}</p>
                                            </div>
                                        </app-tooltip>
                                    </div>
                                    <div class="input-coupon-wrapper">
                                        <input placeholder="{{ 'Please enter coupon code' | translate }}"
                                               type="text" class="input-coupon input-text"
                                               name="coupon_code"
                                               value="{{ couponCode }}"
                                        />
                                        <div id="coupon-error-wrapper" class="validation-advice" style="display: none;"></div>
                                    </div>

                                    <button class="button-default full-width" type="submit" value="{{ 'Apply Voucher' | translate }}" @click.prevent="submitCouponForm(true)">
                                        <span><span>{{ 'Apply Voucher' | translate }}</span></span>
                                    </button>
                                </div>

                                <div v-if="couponCode !== ''" class="remove-voucher">
                                    <div class="coupon-label">
                                        <p class="coupon-label-text">{{ 'Following voucher was applied' | translate }}</p>
                                        <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400" v-if="couponTooltipText !== ''">
                                            <span class="action-badge info-small" slot="tooltipElement"></span>

                                            <div slot="tooltipContent">
                                                <p>{{ couponTooltipText }}</p>
                                            </div>
                                        </app-tooltip>

                                        <p class="align-center h-common">{{ couponCode }}</p>
                                    </div>

                                    <button class="button-empty full-width" type="submit" value="{{ 'Remove Voucher' | translate }}" @click.prevent="submitCouponForm(false)">
                                        <span><span>{{ 'Remove Voucher' | translate }}</span></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <app-modal :show.sync="financeOverlay" v-if="financeOverlay" class="finance-popup">
                <div slot="content">
                    <app-finance-overlay
                            :product-id="productId"
                            :finance-url="financeUrl"
                            :finance-params-origin="financeParamsOrigin"
                            :finance-slider-steps="financeSliderSteps"
                            :pay-in-full-payment="payInFullPayment"
                            :hire-payments="hirePayments"
                            :active-payment="activePayment"
                            :payment-save-url="paymentSaveUrl"
                            :product-url="productUrl"
                            :update-finance-quote="true"
                            :calc-type="calcType"
                            :maintenance-disabled-notification='maintenanceDisabledNotification'
                    ></app-finance-overlay>
                </div>
            </app-modal>
        </div>
    </div>
</template>

<script>
    import appModal from 'core/components/Elements/Modal';
    import appFinanceOverlay from 'motorrad/components/FinanceOverlay';
    import FinanceQuote from 'motorrad/components/Shared/FinanceQuote';
    import appConfirmationModal from 'core/components/Elements/ConfirmationModal';
    import appTooltip from 'core/components/Elements/Tooltip';
    import perfectScrollbar from 'perfect-scrollbar';
    import Stickyfill from '@rq/stickyfill';

    export default Vue.extend({
        mixins: [FinanceQuote],

        props: {
            couponTooltipText: {
                required: false,
                type: String
            },

            couponCode: {
                required: false,
                type: String
            },

            couponFormUrl: {
                required: false,
                type: String
            },

            payInFullPayment: {
                required: true,
                type: Array
            }
        },

        data() {
            return {
                flip: 1024,
                scrollbar: {
                    autoshow: false,
                    instance: null,
                    enabled: false
                },
                sliderInvalidCombinationText: 'Slider combination invalid',
                monthlyPriceId: 4,
                offerPriceId: 5
            }
        },

        methods: {
            forceUpdateFail(error) {
                if (error.status !== 401) {
                    alert(error.statusText);
                    this.ajaxLoading = false;
                }
            },

            toggleOptionGroupHandler(index) {
                this.toggleOptionGroup(index);
            },

            openAllOptionGroups() {
                this.carData.forEach(group => {
                    this.toggleOptionGroupHandler(group.type);
                });
            },

            initScrollbar() {
                if (!this.scrollbar.enabled) {
                    this.updateOptionsWrapperMaxHeight();
                    this.scrollbar.enabled = true;
                    perfectScrollbar.initialize(
                        this.$els.financeQuoteOptionsWrapper,
                        {
                            suppressScrollX: true,
                            wheelPropagation: true
                        }
                    );
                }
            },

            disableScrollbar() {
                if (this.scrollbar.enabled) {
                    perfectScrollbar.destroy(this.$els.financeQuoteOptionsWrapper);
                    this.scrollbar.enabled = false;
                }
            },

            updateFinanceQuote(response) {
                var data = response.data;
                var $self = this;
                const oldCarData = this.carData;
                const customOptions = [];

                oldCarData.reverse();

                if (typeof this.customOptionTitles !== 'undefined') {
                    this.customOptionTitles.forEach(title => {
                        if (title.option_type !== 'Accessories') {
                            customOptions.push(title.option_type);
                        }
                    });
                }

                if (typeof data.car_data !== 'undefined') {
                    if (sessionStorage.getItem('car_data') && sessionStorage.getItem('from_pdp') === 'true') {
                        this.carData = JSON.parse(sessionStorage.getItem('car_data'));
                    } else {
                        this.carData = data.car_data;
                    }

                    oldCarData.forEach(element => {
                        if (customOptions.includes(element.group)) {
                            this.carData.push(element);
                        }
                    });

                    this.carData.reverse();
                }
                if (typeof data.finance_variables !== 'undefined') {
                    for (var i = 0; i < data.finance_variables.length; i++) {
                        var variable = data.finance_variables[i];
                        if (variable.variable === 'part_exchange') {
                            $self.savedPx = (variable.value > 0);
                        }
                    }
                    this.financeVariables = data.finance_variables;
                }
                if (typeof data.rockar_price !== 'undefined') {
                    this.rockarPrice = data.rockar_price;
                }
                if (typeof data.save_off_rrp !== 'undefined') {
                    this.saveOffRrp = data.save_off_rrp;
                }
                if (typeof data.monthly_price !== 'undefined') {
                    this.monthlyPrice = data.monthly_price;
                }
                if (typeof data.lead_time !== 'undefined') {
                    this.leadTime = data.lead_time;
                }
                if (typeof data.customer_deposit !== 'undefined') {
                    this.customerDeposit = data.customer_deposit;
                }
                if (typeof data.pay_deposit !== 'undefined') {
                    this.payDeposit = data.pay_deposit;
                }
                if (typeof data.cashback !== 'undefined') {
                    this.cashback = data.cashback;
                }
                if (typeof data.cash_deposit !== 'undefined') {
                    this.cashDeposit = data.cash_deposit;
                }
                if (typeof data.is_business !== 'undefined') {
                    this.isBusiness = data.is_business;
                }
                EventsBus.$emit('Checkout::UpdatedCarExtras', true);
                this.ajaxLoading = false;
            },

            updateScrollbar() {
                if (this.scrollbar.enabled) {
                    perfectScrollbar.update(this.$els.financeQuoteOptionsWrapper);
                }
            },

            listenFinanceQuoteContentChange() {
                setTimeout(this.handleFinanceQuoteMobileContainer, 10);
                jQuery(window).bind('resize', () => {
                    this.updateOptionsWrapperMaxHeight();
                    this.handleFinanceQuoteMobileContainer();
                });
                setInterval(this.updateScrollbar, 200);
            },

            handleFinanceQuoteMobileContainer() {
                const device = this.$root.currentDevice;

                if (device === 'mobile' || device === 'tablet') {
                    this.disableScrollbar();
                    jQuery(this.$els.financeQuoteOptionsWrapper).css('max-height', 'none');
                } else {
                    this.initScrollbar();
                }
            },

            updateOptionsWrapperMaxHeight() {
                const minHeight = 300;
                const optionsWrapperHeight = parseInt(jQuery(window).height() - jQuery(this.$els.financeQuoteOptionsWrapper).position().top - 120);

                jQuery(this.$els.financeQuoteOptionsWrapper).css('max-height', (optionsWrapperHeight < minHeight ? minHeight : optionsWrapperHeight));
            },

            formatAdditionalFeeName(label) {
                return label.replace(/ /g, '-').toLowerCase();
            },

            getFinanceVariable(variables, id) {
                if (!variables) {
                    return 0;
                }

                const variable = variables.find(v => v.variable_id === id.toString());

                return variable ? variable.value : 0;
            }
        },

        ready() {
            this.openAllOptionGroups();
            this.handleFinanceQuoteMobileContainer();
            this.listenFinanceQuoteContentChange();
            this.initScrollbar();
            this.updateScrollbar();

            const stickyfill = Stickyfill(); // include library for browsers that do not provide native support
            stickyfill.add(document.getElementsByClassName('checkout-quote')[0]);

            if (sessionStorage.getItem('car_data') && sessionStorage.getItem('from_pdp') === 'true') {
                this.carData = JSON.parse(sessionStorage.getItem('car_data'));
            }

            EventsBus.$emit('Checkout::UpdatedCarExtras', true);
        },

        events: {
            'FinanceQuote::onFinanceCalculatorChange'(data) {
                this.financeVariables = data.variables;
                this.monthlyPrice = this.getFinanceVariable(data.variables, this.monthlyPriceId);
                this.rockarPrice = this.getFinanceVariable(data.variables, this.offerPriceId);
            }
        },

        components: {
            appModal,
            appFinanceOverlay,
            appTooltip,
            appConfirmationModal
        }
    });
</script>
