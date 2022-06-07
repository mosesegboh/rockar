<template>
    <div class="finance-quote-wrapper finance-quote-configurator" :class="{ 'active': active }">
        <div class="finance-quote-floating">
            <div class="finance-quote-floating-wrapper">
                <div class="finance-quote-floating-checkout col-4 col-md-12">
                    <button class="button dsp2-money" v-if="canCheckout()" @click="checkout()">{{ orderButtonText }}</button>
                </div>
            </div>
        </div>
        <div class="general-preloader" v-show="ajaxLoading">
            <div class="show-loading"></div>
        </div>
        <div class="toggle">
            <div class="toggle-button" @click="toggle()">
                <div class="icon"></div>
            </div>
        </div>
        <div class="finance-quote-top" :class="{ active: quotePopup }">
            <slot name="offer-tags"></slot>
            <div class="finance-quote-header">
                <div class="finance-quote-header-image">
                    <img :src="image" :alt="carData[0].label">
                </div>
                <div class="finance-quote-header-body">
                    <div class="finance-quote-title">{{ 'Your Quote' | translate }}</div>
                    <div class="finance-calculator" @click="showFinanceOverlay()">
                        <a class="finance-calculator-icon"></a>
                        <span>
                            {{ 'Edit Payment Option' | translate }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="finance-payments" v-if="!isHire">
                <div class="payment rockar-price">
                    <p class="payment-label">{{ 'Offer Price' | translate }}</p>
                    <p class="payment-amount">{{ rockarPrice | numberFormat '0,0' true }}</p>
                </div>

                <div class="payment off-rrp" v-if="saveOffRrp">
                    <p class="payment-label">{{ 'off RRP' | translate }}</p>
                    <p class="payment-amount">{{ saveOffRrp | numberFormat '0,0' true }}</p>
                </div>

                <div class="payment monthly-price" v-if="monthlyPrice !== 0">
                    <p class="payment-label">{{ 'Per Month' | translate }}</p>
                    <p class="payment-amount">{{ monthlyPrice | numberFormat '0,0' true }}</p>
                </div>
            </div>

            <div class="finance-payments" v-if="isHire">
                <div class="payment initial-payment">
                    <p class="payment-label">{{ 'Initial payment' | translate }}</p>
                    <p class="payment-amount">{{ cashDeposit | numberFormat '0,0' true }}</p>
                </div>

                <div class="payment cash-back" v-if="cashback > 0">
                    <p class="payment-label">{{ 'Cash Back' | translate }}</p>
                    <p class="payment-amount">{{ cashback | numberFormat '0,0' true }}</p>
                </div>

                <div class="payment monthly-price" v-if="monthlyPrice !== 0">
                    <p class="payment-label">{{ 'Per Month' | translate }}</p>
                    <p class="payment-amount">{{ monthlyPrice | numberFormat '0,0' true }}</p>
                </div>
            </div>

            <div class="finance-quote grid no-padding-top overview">
                <template v-for="(dataIndex, data) in carDataSorted">
                    <div v-if="data.group && data.items.length" :key="dataIndex"
                        class="finance-quote grid no-padding-top no-padding-bottom group-block"
                        :class="groupsOpenStatus[dataIndex] ? 'block-expand' : 'block-collapse'">
                        <div class="finance-quote-content">
                            <table class="table table-two">
                                <tbody :class="data.group">
                                    <tr @click="toggleOptionGroupHandler(dataIndex)" :class="data.group">
                                        <td>
                                            <span class="variable-title">{{ data.group | translate }}</span>
                                            <a :class="[groupsOpenStatus[dataIndex] ? 'expand' : 'collapse']"></a>
                                        </td>
                                        <td>
                                            <span class="table-right">{{ getGroupPrice(data) }}</span>
                                        </td>
                                    </tr>
                                    <tr v-for="(index, item) in data.items" v-show="groupsOpenStatus[dataIndex]" :key="index">
                                        <td v-for="(index, access) in preSelectedAccessories" :key="index">
                                            <app-confirmation-modal
                                                    :title="'Accessories' | translate"
                                                    :confirmation-question="'Are you sure you want to remove this item?' | translate">
                                                <a class="delete" @click="removeAccessory(item)" v-if="item.remove && (item.id !== access.identifier)"></a>
                                            </app-confirmation-modal>
                                            <span class="variable-value" :class="[!item.remove || item.id === access.identifier ? 'no-icon' : '']">{{ item.label | convertNCR }}</span>
                                        </td>
                                        <td v-if="preSelectedAccessIsEmpty">
                                            <app-confirmation-modal
                                                    :title="'Accessories' | translate"
                                                    :confirmation-question="'Are you sure you want to remove this item?' | translate">
                                                <a class="delete" @click="removeAccessory(item)" v-if="item.remove"></a>
                                            </app-confirmation-modal>
                                            <span class="variable-value" :class="[!item.remove ? 'no-icon' : '']">{{ item.label | convertNCR }}</span>
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

                <div class="finance-quote grid no-padding-top overview">
                    <div class="finance-quote-content">
                        <table class="table">
                            <template v-for="(index, vars) in financeVariables">
                                <tr :class="[vars.css_classes, vars.variable]" :key="index" v-if="vars.variable !== 'x_monthly_payments_of'">
                                    <td>
                                        <span class="variable-title">
                                            {{ parseVariableTitleMethod(vars.variable_title) }}
                                            <a class="fq-edit" href="#" v-if="vars.variable === 'part_exchange' && vars.value === 0"
                                            @click="showEditPartExchange()">
                                                {{ 'Add' | translate }}
                                            </a>
                                            <a class="fq-edit" href="#" v-if="vars.variable === 'part_exchange' && vars.value !== 0"
                                            @click="showEditPartExchange()">
                                                {{ 'Edit' | translate }}
                                            </a>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="table-right" v-if="vars.value || vars.value === 0">
                                            {{ vars.value_formatted }}
                                        </span>
                                        <span class="table-right" v-else>{{ sliderInvalidCombinationText | translate }}</span>
                                    </td>
                                </tr>
                            </template>
                        </table>

                      <div class="get-quote-button">
                        <slot name="productActions"></slot>
                      </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="finance-quote-bottom">
            <div class="finance-payments" v-if="!isHire">
                <div class="payment monthly-price">
                    <template v-if="monthlyPrice !== 0">
                        <p class="payment-label">{{ 'Per Month' | translate }}</p>
                        <p class="payment-amount">{{ monthlyPrice | numberFormat '0,0' true }}</p>
                    </template>
                    <template v-else>
                        <p class="payment-label">{{ 'Offer Price' | translate }}</p>
                        <p class="payment-amount">{{ rockarPrice | numberFormat '0,0' true }}</p>
                    </template>
                </div>
            </div>
            <div class="finance-quote-buttons checkout-button">
                <button class="button dsp2-money" v-if="canCheckout()" @click="checkout()">{{ orderButtonText }}</button>
            </div>
        </div>

        <app-modal
            :show.sync="financeOverlay"
            :blur-background="false"
            v-if="financeOverlay"
            class="finance-popup finance-filters-overlay"
        >
            <div slot="content">
                <app-finance-overlay
                    v-ref:finance-overlay
                    :active-payment="activePayment"
                    :finance-params-origin="financeParamsOrigin"
                    :finance-slider-steps="financeSliderSteps"
                    :hire-payments="hirePayments"
                    :instalment-group-id="instalmentGroupId"
                    :pay-in-full-payment="payInFullPayment"
                    :product-id="productId"
                    :update-finance-quote="updateFinanceQuote"
                    :calc-type="calcType"
                    :finance-url="financeUrl"
                    :image="image"
                    :payment-save-url="paymentSaveUrl"
                    :product-url="productUrl"
                    :title="title"
                ></app-finance-overlay>
            </div>
        </app-modal>

        <app-modal
            v-if="!isCheckout"
            :show.sync="editPartExchange"
            v-show="editPartExchange"
            @close-popup="closeTradeInPopup"
            class="px-popup-wrapper"
        >
            <div slot="content">
                <slot name="part-exchange"></slot>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import FinanceQuote from 'dsp2/components/Shared/FinanceQuote';
    import appConfirmationModal from 'dsp2/components/Elements/ConfirmationModal';
    import UrlParser from 'dsp2/mixins/UrlParser';
    import Constants from 'dsp2/components/Shared/Constants';
    import translateString from 'core/filters/Translate';
    import EventTrackerFinanceOverlay from 'dsp2/mixins/EventTrackerFinanceOverlay';
    import appFinanceOverlay from 'dsp2/components/FinanceOverlay';

    export default Vue.extend({
        mixins: [FinanceQuote, Constants, UrlParser, EventTrackerFinanceOverlay],

        props: {
            image: {
                required: false,
                type: String,
                default: ''
            },

            calcType: {
                required: false,
                type: String,
                default: ''
            },

            financeSliderSteps: {
                required: false,
                type: Object,
                defaut: {}
            },

            financeUrl: {
                required: false,
                type: String,
                defaut: ''
            },

            leadTime: {
                required: false,
                type: Number,
                defaut: -1
            },

            updateStepUrl: {
                required: false,
                type: String,
                default: null
            },

            instalmentGroupId: {
                required: false,
                type: Number,
                default: -1
            },

            updateFinanceQuote: {
                required: false,
                type: Boolean,
                default: false
            },

            productUrl: {
                required: false,
                type: String,
                default: null
            },

            title: {
                required: false,
                type: String,
                default: ''
            },

            financeParamsOrigin: {
                required: true,
                type: Object
            }
        },

        data: () => ({
            active: false,
            editPartExchange: false,
            flip: 1024,
            showCheckout: false,
            scrollbar: {
                autoshow: false,
                enabled: false
            },
            processHandleFinanceQuote: false,
            sliderInvalidCombinationText: 'Slider combination invalid',
            CAR_DATA_SORT_ORDERS: {
                'Base Price': 0,
                'Exterior': 10,
                'Interior': 20,
                'Options': 30,
                'Line / Packages': 40,
                'Extra Options': 50,
                'Accessories': 60
            },
            groupsOpenStatus: [false, false, false, false],
            quotePopup: false,
            financeOverlay: false
        }),

        computed: {
            financeGroupId() {
                return this.$store.state.configurator.financeGroupId;
            },

            preSelectedAccessIsEmpty() {
                if (!this.preSelectedAccessories) {
                    return true;
                } else if (typeof this.preSelectedAccessories === 'object' && !Object.keys(this.preSelectedAccessories).length) {
                    return true;
                }
                return false;
            },

            changeOverlays() {
                const { editPartExchange, financeOverlay } = this;

                return {
                    editPartExchange,
                    financeOverlay
                }
            },

            isCheckout() {
                return this.$root.$refs.summaryStep;
            },

            orderButtonText() {
                return this.translateString('Start My Order');
            }
        },

        filters: {
            /**
             * filter which returns the lead time message depending on how many days the value is
             *
             * @param {Number} val
             *
             * @returns {string}
             */
            leadTimeFormat: {
                read(val) {
                    return `Delivery ${val} ${(val > 1 ? 'weeks' : 'week')}`;
                }
            }
        },

        methods: {
            translateString,

            closeFinancePopup() {
                this.financeOverlay = false;
                this.removeOverlayParamFromURL(this.OVERLAYSEARCHPARAMS.FINANCE);

                if (this.overlayParamIsPresented(this.OVERLAYSEARCHPARAMS.TRADEIN)) {
                    this.showEditPartExchange();
                }
            },

            closeTradeInPopup() {
                this.removeOverlayParamFromURL(this.OVERLAYSEARCHPARAMS.TRADEIN);

                if (this.overlayParamIsPresented(this.OVERLAYSEARCHPARAMS.FINANCE)) {
                    this.showFinanceOverlay();
                }
            },

            overlayParamIsPresented(paramValue = null) {
                const url = this.$root.parseURL().searchObject;
                let overlayParam = url.overlay;

                if (overlayParam) {
                    if (! overlayParam instanceof Array) {
                        overlayParam = overlayParam.split(',');
                    }

                    return paramValue === null || overlayParam.indexOf(paramValue) === 0;
                }

                return false;
            },

            addOverlayParamToUrl(paramValue) {
                const url = this.$root.parseURL().searchObject;

                if (!this.overlayParamIsPresented()) {
                    url[encodeURI(this.OVERLAYSEARCHPARAMS.OVERLAY)] = encodeURI(paramValue);

                    const cleanedUrl = this.$root.makeURLSearch(url);
                    window.history.pushState({}, document.title, `?${cleanedUrl}`);
                }
            },

            removeOverlayParamFromURL(paramValue) {
                const url = this.$root.parseURL().searchObject;
                let overlayParam = url.overlay;

                if (overlayParam && overlayParam instanceof Array) {
                    overlayParam.splice(overlayParam.indexOf(paramValue), 1);
                } else if (overlayParam) {
                    const values = overlayParam.split(',');
                    values.splice(values.indexOf(paramValue), 1);
                    overlayParam = values.join(',');
                }

                if (overlayParam) {
                    url.overlay = overlayParam;
                } else {
                    delete url.overlay;
                }

                const cleanedUrl = this.$root.makeURLSearch(url);
                window.history.pushState({}, document.title, `?${cleanedUrl}`);
            },

            showEditPartExchange() {
                this.editPartExchange = true;
                this.addOverlayParamToUrl(this.OVERLAYSEARCHPARAMS.TRADEIN);
                window.EventsBus.$emit('PartExchangeFilter::hasSavedPx');
                this.$broadcast('PartExchange::triggerEventTrackerCheck');
            },

            showFinanceOverlay() {
                this.financeOverlay = true;
                this.addOverlayParamToUrl(this.OVERLAYSEARCHPARAMS.FINANCE);
                this.setFinanceGroup(this.financeGroupId);
            },

            checkout() {
                sessionStorage.setItem('car_data', JSON.stringify(this.carData));
                sessionStorage.setItem('from_pdp', 'true');
                this.$dispatch('Main::configuratorAdvanceStep');
            },

            hideAccessories() {
                this.$dispatch('Main::configuratorStepBack');
            },

            removeAccessory(item) {
                this.$dispatch('Main::removeAccessory', item);
            },

            toggleOptionGroupHandler(index) {
                this.toggleOptionGroup(index);
            },

            openAllOptionGroupsMobile() {
                if (jQuery(window).innerWidth() <= 736) {
                    this.openAllOptionGroups();
                }
            },

            openAllOptionGroups() {
                for (let i = 0; i < this.carData.length; i++) {
                    if (this.groupsOpenStatus[i] !== true) {
                        this.toggleOptionGroup(i);
                    }
                }
            },

            /**
             * Get FQ finance method type and commit vuex mutation
             *
             * @returns {string}
             */
            getFinanceOptionMethodType() {
                let type = '';
                if (this.isHire) {
                    type = 'hire';
                }

                if (this.isLeasing) {
                    type = 'lease';
                }

                if (this.isPayInFull) {
                    type = 'cash';
                }

                this.$store.commit('setFinanceType', type);

                return type;
            },

            formatAdditionalFeeName(label) {
                return label.replace(/ /g, '-').toLowerCase();
            },

            toggle() {
                this.active = !this.active;

                if (this.active) {
                    jQuery('body').css({ overflow: 'hidden' });
                } else {
                    jQuery('body').css({ overflow: 'visible' });
                }
            },

            isProductPage() {
                return this.$root.$refs.productTopContainer;
            }
        },

        events: {
            'FinanceQuote::checkout'(toggle) {
                this.showCheckout = toggle;
            },

            'FinanceQuote::updateFinanceType'() {
                this.getFinanceOptionMethodType();
            },

            'FinanceQuote::showFinanceOverlay'() {
                this.showFinanceOverlay();
            },

            'FinanceQuote::updateUrlAfterClosingPopup'(val) {
                this.removeOverlayParamFromURL(val);
                if (this.overlayParamIsPresented(this.OVERLAYSEARCHPARAMS.TRADEIN)) {
                    this.showEditPartExchange();
                }
            },

            'FinanceOverlay::closeFinanceOverlay'() {
                this.closeFinancePopup();
            },

            'FinanceQuote::adjustMobileQuote'(val) {
                this.quotePopup = !!val;
            },

            'Quote::ResetPDPTradeIn'() {
                this.forceUpdate();
            }
        },

        watch: {
            'changeOverlays': {
                handler(val) {
                    if (!Object.keys(val).some((item) => val[item])) {
                        this.$dispatch('ProductPageOverlay::Overlay', false);
                    } else {
                        this.$dispatch('ProductPageOverlay::Overlay', true);
                    }
                },
                deep: true
            }
        },

        ready() {
            this.getFinanceOptionMethodType();

            if (this.overlayParamIsPresented(this.OVERLAYSEARCHPARAMS.FINANCE) && !this.isProductPage()) {
                this.showFinanceOverlay();
            } else if (this.overlayParamIsPresented(this.OVERLAYSEARCHPARAMS.TRADEIN)) {
                this.showEditPartExchange();
            }
        },

        created() {
            EventsBus.$on('FinanceQuote::updateUrlAfterClosingPopup', (val) => {
                this.$dispatch('FinanceQuote::updateUrlAfterClosingPopup', val);
            });
        },

        components: {
            appConfirmationModal,
            appFinanceOverlay
        }
    });
</script>
