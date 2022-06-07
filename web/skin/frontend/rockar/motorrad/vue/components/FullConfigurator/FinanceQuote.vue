<template>
    <div class="finance-quote-wrapper">
        <div class="finance-quote-sticky"></div>
        <div class="finance-quote-configurator">
            <div class="general-preloader" v-show="ajaxLoading">
                <div class="show-loading"></div>
            </div>
            <div class="finance-quote grid no-padding-bottom">
                <div class="finance-quote-header row">
                    <div class="fq-heading h-common">{{ 'Your Quotation' | translate }}</div>
                    <div class="fq-button-wrapper">
                        <a href="#" class="link-edit-finance bottom" @click="showFinanceOverlay()">
                            <span class="finance-calculator-icon"></span>
                            <span class="edit-finances">{{ 'Edit finance' | translate }}</span>
                        </a>
                    </div>
                </div>
                <div class="finance-quote-content">
                    <div class="car-data-wrapper">
                        <template v-for="data in carDataSorted">
                            <div v-if="!data.group && !data.type">
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
                                <tr @click="openOptionGroup(dataIndex)" :class="data.group">
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
                                            <a class="remove" @click="removeAccessory(item)" v-if="item.remove"></a>
                                        </app-confirmation-modal>
                                        <span :class="[!item.remove ? 'no-icon' : '']">{{ item.label | convertNCR }}</span>
                                    </td>
                                    <td>
                                        <span class="table-right">{{ item.price === false ? '0.00' : item.price | numberFormat '0,0.00' true }}</span>
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

                <div class="finance-quote grid no-padding-top overview" v-if="financeVariables.length">
                    <div class="finance-quote-content">
                        <table class="table">
                            <tr :class="[vars.css_classes, vars.variable]" v-for="vars in financeVariables">
                                <td>
                                <span class="variable-title">
                                    {{ parseVariableTitleMethod(vars.variable_title) }}
                                    <a class="fq-edit" href="#" v-if="vars.show_edit_link == 1"
                                       @click="showFinanceOverlay()">
                                        <span class="fq-edit-delimitor">-</span>
                                        {{ 'Edit' | translate }}</a>
                                    <a class="fq-edit" href="#" v-if="vars.variable == 'part_exchange'"
                                       @click="showEditPartExchange()">
                                        <span class="fq-edit-delimitor">-</span>
                                        <span v-if="!hasPXResults">{{ 'Add' | translate }}</span>
                                        <span v-if="hasPXResults">{{ 'Edit' | translate }}</span>
                                    </a>
                                    <a class="fq-edit" href="#" v-if="vars.show_edit_icon == 1"
                                       @click="showFinanceOverlay()">
                                        <span class="fq-edit-delimitor">-</span>
                                        {{ 'Edit' | translate }}
                                    </a>
                                </span>
                                </td>
                                <td>
                                <span class="table-right" v-if="vars.value || vars.value == 0">
                                    {{{ vars.value_formatted }}}
                                </span>
                                    <span class="table-right" v-else>{{ sliderInvalidCombinationText | translate }}</span>
                                </td>
                            </tr>
                        </table>

                        <app-modal :show.sync="editPartExchange" v-show="editPartExchange && partExchangeAdditional" width="60%">

                            <div slot="content">
                                <app-configurator-part-exchange
                                    :pe-id="partExchange.pxId"
                                    :valuation-url="partExchange.valuationUrl"
                                    :car-details-url="partExchangeAdditional.carDetailsUrl"
                                    :reset-url="partExchangeAdditional.resetUrl"
                                    :additional-info='additionalInfo'
                                    :explanatory-text="partExchangeAdditional.explanatoryText"
                                    :active-condition="partExchange.activeCondition"
                                    :car-conditions="carConditions"
                                    :car-alternative-details-url="partExchangeAdditional.carAlternativeDetailsUrl"
                                    :save-valuation-url="partExchange.saveValuationUrl"
                                    :save-to-session-url="partExchangeAdditional.saveToSessionUrl"
                                    :saved='partExchangeSaved'
                                    :saved-px-list="partExchangeAdditional.savedPxList"
                                    :make-url="partExchangeAdditional.makeUrl"
                                    :range-url="partExchangeAdditional.rangeUrl"
                                    :model-url="partExchangeAdditional.modelUrl"
                                    :year-url="partExchangeAdditional.yearUrl"
                                    :colour-url="partExchangeAdditional.colourUrl"
                                    :derivative-url="partExchangeAdditional.derivativeUrl"
                                    :custom-car-url="partExchangeAdditional.customCarUrl"
                                    :active-px-url="partExchangeAdditional.activePxUrl"
                                    :saved-px="savedPx"
                                    :can-open-custom="false"
                                    :product-id="productId"
                                    :lead-time="leadTime"
                                ></app-configurator-part-exchange>
                            </div>
                        </app-modal>
                    </div>
                </div>
            </div>

            <div class="finance-overlay">
                <app-modal :show.sync="financeOverlay" v-if="financeOverlay" width="80%" >
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
                                :calc-type="calcType">
                        </app-finance-overlay>
                    </div>
                </app-modal>
            </div>

            <div class="finance-payments" v-if="!isHire">
                <div class="payment">
                    <p class="payment-amount">{{ rockarPrice | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Offer price' | translate }}</p>
                </div>
                <div class="payment" v-if="saveOffRrp">
                    <p class="payment-amount">{{ saveOffRrp | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'off RRP' | translate }}</p>
                </div>
                <div class="payment" v-if="monthlyPrice != 0">
                    <p class="payment-amount">{{ monthlyPrice | numberFormat '0,0' true true }}</p>
                    <p class="payment-label">{{ 'A month' | translate }}</p>
                </div>
            </div>

            <div class="finance-payments" v-if="isHire">
                <div class="payment">
                    <p class="payment-amount">{{ cashDeposit| numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Initial payment' | translate }}</p>
                </div>
                <div class="payment" v-if="cashback">
                    <p class="payment-amount">{{ cashback | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Cashback' | translate }}</p>
                </div>
                <div class="payment" v-if="monthlyPrice != 0">
                    <p class="payment-amount">{{ monthlyPrice | numberFormat '0,0' true true }}</p>
                    <p class="payment-label">{{ 'A month' | translate }}</p>
                </div>
            </div>

            <div class="finance-delivery" v-if="leadTime">
                <p class="lead-time">{{ leadTime | convertNCR }}</p>
            </div>

            <div class="finance-quote-buttons checkout-button" v-if="canCheckout()">
                <button class="button button-medium btn-checkout btn-mobile-large" @click="checkout()">{{ 'Checkout' | translate }}</button>
            </div>
        </div>
    </div>
</template>

<script>
    import FinanceQuote from 'motorrad/components/Shared/FinanceQuote';
    import appConfiguratorPartExchange from 'motorrad/components/Configurator/PartExchange';
    import appConfirmationModal from 'core/components/Elements/ConfirmationModal';
    import perfectScrollbar from 'perfect-scrollbar';
    import Stickyfill from '@rq/stickyfill';

    export default Vue.extend({
        mixins: [FinanceQuote],

        props: {
            statesLocked: {
                required: true,
                type: Boolean
            },

            partExchange: {
                type: Object,
                required: false
            },

            carConditions: {
                type: Array,
                required: false
            },

            partExchangeAdditional: {
                type: Object,
                required: false
            },

            partExchangeSaved: {
                required: false,
                type: Boolean,
                default: false
            },

            savedPx: {
                required: false,
                default: false
            },

            additionalInfo: {
                required: true,
                type: Array
            }
        },

        data() {
            return {
                editPartExchange: false,
                showSubsection: false,
                tempState: false,
                flip: 1024,
                financeQuote: {
                    width: 0,
                    height: 0,
                    scrollbar: {
                        instance: null,
                        enabled: false,
                        autoshow: true
                    }
                },
                scrollbar: {
                    autoshow: false,
                    enabled: false
                },
                sliderInvalidCombinationText: 'Slider combination invalid'
            }
        },

        computed: {
            hasPXResults() {
                return this.$store.state.general.PX.Valuation.valuationResult;
            }
        },

        methods: {
            showEditPartExchange() {
                this.editPartExchange = true;
            },

            checkout() {
                this.$dispatch('Main::FullConfiguratorCheckout');
            },

            removeAccessory(accessory) {
                this.$dispatch('Main::removeAccessory', accessory);
            },

            checkOverflow() {
                const financeQuoteWrapper = jQuery('.finance-quote-configurator');
                const windowHeight = jQuery(window).height();
                const desktopContainer = jQuery('.finance-quote-desktop-container');

                if (financeQuoteWrapper.outerHeight() > windowHeight) {
                    desktopContainer.css({
                        'overflow-y': 'scroll'
                    });
                } else {
                    desktopContainer.css({
                        'overflow-y': 'hidden'
                    });
                }
            },

            financeQuotePosition() {
                const $configuratorWindow = jQuery('.configurator-window');
                const $financeQuoteConfigurator = jQuery('.finance-quote-configurator');

                if (jQuery(window).width() <= this.flip) {
                    $financeQuoteConfigurator.css({ position: 'static', 'max-width': '100%' });
                    $configuratorWindow.css({ 'margin-bottom': '0px' });
                } else {
                    $financeQuoteConfigurator.css({ position: 'absolute', 'max-width': '290px' });
                    this.financeQuoteScroll();
                }
            },

            financeQuoteScroll() {
                const $financeQuoteConfigurator = jQuery('.finance-quote-configurator'),
                    $configuratorWindow = jQuery('.configurator-window'),
                    $configuratorStickyPoint = jQuery('.finance-quote-sticky'),
                    $footer = jQuery('footer'),
                    topMinMargin = 10,
                    footerMargin = 12,
                    scrollPosition = jQuery(document).scrollTop(),
                    maxFinanceQuoteHeight = jQuery(window).height() - 40,
                    minFinanceQuoteHeight = 200;
                let maxFinanceQuoteWrapperHeight = jQuery(window).height() - 320;
                const maxFinanceQuoteWrapperHeightWithConfigurator = $configuratorWindow.height() - 277;

                if (jQuery(window).width() > this.flip) {
                    if ($financeQuoteConfigurator.height() !== this.financeQuote.height) {
                        if ($financeQuoteConfigurator.height() >= maxFinanceQuoteHeight || $financeQuoteConfigurator.height() < $configuratorWindow.height()) {
                            maxFinanceQuoteWrapperHeight = maxFinanceQuoteWrapperHeight < minFinanceQuoteHeight ? minFinanceQuoteHeight : maxFinanceQuoteWrapperHeight;
                            if (maxFinanceQuoteWrapperHeight > maxFinanceQuoteWrapperHeightWithConfigurator) maxFinanceQuoteWrapperHeight = maxFinanceQuoteWrapperHeightWithConfigurator;
                            jQuery(this.$els.financeQuoteOptionsWrapper).css('max-height', `${maxFinanceQuoteWrapperHeight}px`);
                            this.initFinanceQuoteScrollbar();
                        } else {
                            if ($financeQuoteConfigurator.height() > $configuratorWindow.height()) {
                                jQuery(this.$els.financeQuoteOptionsWrapper).css('max-height', `${maxFinanceQuoteWrapperHeight}px`);
                            }
                        }
                        this.financeQuote.height = $financeQuoteConfigurator.height();
                    }

                    if ($financeQuoteConfigurator.height() <= $configuratorWindow.height()) { // block is fixed to window
                        if (scrollPosition > $configuratorStickyPoint.position().top) {
                            $financeQuoteConfigurator.css({
                                'position': 'fixed',
                                'top': `${topMinMargin}px`
                            }).removeClass('static-quote');
                        } else { // block is fixed to top
                            $financeQuoteConfigurator.css({
                                'position': 'absolute',
                                'top': 'auto'
                            }).addClass('static-quote');
                        }

                        // block is fixed to bottom
                        if ($financeQuoteConfigurator.height() + $financeQuoteConfigurator.offset().top + topMinMargin + footerMargin > $footer.offset().top) {
                            const offsetTop = $footer.offset().top - $financeQuoteConfigurator.height() - footerMargin - topMinMargin;
                            $financeQuoteConfigurator.css({
                                'position': 'absolute',
                                'top': `${offsetTop}px`
                            });
                        }
                    } else { // block is bigger than main block
                        jQuery(this.$els.financeQuoteOptionsWrapper).css('max-height', `${maxFinanceQuoteWrapperHeightWithConfigurator}px`);
                        this.initFinanceQuoteScrollbar();
                        $financeQuoteConfigurator.css({
                            'position': 'static'
                        });
                    }
                }
            },

            initFinanceQuoteScrollbar() {
                if (!this.financeQuote.scrollbar.enabled) {
                    this.financeQuote.scrollbar.enabled = true;
                    perfectScrollbar.initialize(
                        this.$els.financeQuoteOptionsWrapper,
                        {
                            suppressScrollX: true,
                            wheelPropagation: true
                        }
                    );
                }
            },

            disableFinanceQuoteScrollbar() {
                if (this.financeQuote.scrollbar.enabled) {
                    this.financeQuote.scrollbar.instance.destroy();
                    this.financeQuote.scrollbar.enabled = false;
                }
            },

            openOptionGroup(index) {
                const _self = this;
                const $financeQuoteConfiguratorHeight = jQuery('.finance-quote-configurator').height();
                this.groupsOpenStatus.$set(index, !this.groupsOpenStatus[index]);
                this.$nextTick(() => {
                    this.financeQuoteScroll();
                });
            },

            forceUpdate(view) {
                if (view) {
                    this.tempState = view;
                }

                this.ajaxLoading = true;
                const data = {};
                if (typeof this.$root.$refs.configurator !== 'undefined') {
                    data.product_id = this.$root.$refs.configurator.product.id;
                }

                this.$http({
                    url: this.paymentSaveUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data
                }).then(this.updateFinanceQuote, this.forceUpdateFail);
            },

            updateFinanceQuote(response) {
                const data = response.data;
                const $self = this;
                if (typeof data.car_data !== 'undefined') {
                    this.carData = data.car_data;
                }

                if (typeof data.finance_variables !== 'undefined') {
                    for (let i = 0; i < data.finance_variables.length; i++) {
                        const variable = data.finance_variables[i];
                        if (variable.variable === 'part_exchange') {
                            $self.savedPx = (variable.value > 0);
                            this.$dispatch('Main::PxValueUpdated', variable.value);
                        }
                    }
                    this.financeVariables = data.finance_variables;
                }

                if (typeof data.rockar_price !== 'undefined') {
                    this.rockarPrice = data.rockar_price;
                    this.$store.commit('setProductPrice', this.rockarPrice);
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

                if (this.tempState) {
                    this.groupsOpenStatus = [];
                    this.carData.forEach((item, index) => {
                        if (typeof item.group !== 'undefined' && item.group === this.tempState) {
                            this.$nextTick(() => {
                                this.openOptionGroup(index);
                            });
                        }
                    });
                    this.tempState = false;
                }
                this.ajaxLoading = false;
            },

            formatAdditionalFeeName(label) {
                return label.replace(/ /g, '-').toLowerCase();
            }
        },

        events: {
            'FinanceQuote::FullConfiguratorForceUpdateFinanceQuote'(view) {
                this.forceUpdate(view);
            },
            'chooseVehicle::updateVehicles'(view) {
                this.forceUpdate(view);
            }
        },

        created() {
            EventsBus.$on('FinanceQuote::financeQuoteScroll', () => {
                this.financeQuoteScroll();
            });
        },

        ready() {
            window.addEventListener('scroll', this.financeQuoteScroll);
            window.addEventListener('resize', this.financeQuotePosition);
            this.handleFinanceQuoteMobileContainer();
            this.listenFinanceQuoteContentChange();
            this.initScrollbar();
            this.financeQuotePosition();
            this.financeQuoteScroll();
            this.openOptionGroup(1);

            this.$watch('tempState', () => {
                this.financeQuoteScroll();
            });

            const stickyfill = Stickyfill(); // include library for browsers that do not provide native support
            stickyfill.add(document.getElementsByClassName('finance-quote-desktop-container')[0]);
        },

        components: {
            appConfiguratorPartExchange,
            appConfirmationModal
        }
    });
</script>
