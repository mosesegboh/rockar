<template>
    <div class="finance-quote-wrapper">
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
                                    <td v-for="access in preSelectedAccessories">
                                        <app-confirmation-modal
                                                :confirmation-question="'Are you sure you want to remove this item?' | translate">
                                            <a class="remove" @click="removeAccessory(item)" v-if="item.remove && (item.id !== access.identifier)"></a>
                                        </app-confirmation-modal>
                                        <span :class="[!item.remove || item.id == access.identifier? 'no-icon' : '']">{{ item.label | convertNCR }}</span>
                                    </td>
                                    <td v-if="preSelectedAccessIsEmpty">
                                        <app-confirmation-modal
                                                :confirmation-question="'Are you sure you want to remove this item?' | translate">
                                            <a class="remove" @click="removeAccessory(item)" v-if="item.remove"></a>
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
                                        <div class="ved-info-modal">
                                            {{{data.info}}}
                                        </div>
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
                            <span class="variable-title">
                                {{ parseVariableTitleMethod(vars.variable_title) }}
                                <a class="fq-edit" href="#" v-if="vars.show_edit_link == 1"
                                   @click="showFinanceOverlay()">
                                    <span class="fq-edit-delimitor">-</span>
                                    {{ 'Edit' | translate }}</a>
                                <a class="fq-edit" href="#" v-if="vars.variable == 'part_exchange' && vars.value === 0"
                                   @click="showEditPartExchange()">
                                    <span class="fq-edit-delimitor">-</span>
                                    {{ 'Add' | translate }}
                                </a>
                                <a class="fq-edit" href="#" v-if="vars.variable == 'part_exchange' && vars.value != 0"
                                   @click="showEditPartExchange()">
                                    <span class="fq-edit-delimitor">-</span>
                                    {{ 'Edit' | translate }}
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
                    </div>
                </div>
            </div>

            <div class="finance-payments" v-if="!isHire">
                <div class="payment rockar-price">
                    <p class="payment-amount">{{ rockarPrice | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Offer price' | translate }}</p>
                </div>

                <div class="payment off-rrp" v-if="saveOffRrp">
                    <p class="payment-amount">{{ saveOffRrp | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'off RRP' | translate }}</p>
                </div>

                <div class="payment monthly-price" v-if="monthlyPrice != 0">
                    <p class="payment-amount">{{ monthlyPrice | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'A month' | translate }}</p>
                </div>
            </div>


            <div class="finance-payments" v-if="isHire">
                <div class="payment initial-payment">
                    <p class="payment-amount">{{ cashDeposit | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Initial payment' | translate }}</p>
                </div>

                <div class="payment cash-back" v-if="cashback > 0">
                    <p class="payment-amount">{{ cashback | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Cash Back' | translate }}</p>
                </div>

                <div class="payment monthly-price" v-if="monthlyPrice != 0">
                    <p class="payment-amount">{{ monthlyPrice | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'A month' | translate }}</p>
                </div>
            </div>

            <div class="finance-delivery" v-if="configurableLeadTime">
                <p class="lead-time">{{ configurableLeadTime | leadTimeFormat }}</p>
            </div>

            <div class="finance-quote-buttons checkout-button" v-if="canCheckout()">
                <button class="button btn-checkout button-slate-gray btn-mobile-large" @click="checkout()">{{'Checkout' | translate }}</button>
            </div>

            <app-modal :show.sync="financeOverlay" v-if="financeOverlay" @close-popup="closeFinancePopup" class="finance-popup">
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
                            :instalment-group-id="instalmentGroupId"
                    ></app-finance-overlay>
                </div>
            </app-modal>

            <app-modal :show.sync="editPartExchange" v-show="editPartExchange && partExchangeAdditional" @close-popup="closeTradInPopup">
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
                    ></app-configurator-part-exchange>
                </div>
            </app-modal>
        </div>
    </div>
</template>

<script>
    import FinanceQuote from 'bmw/components/Shared/FinanceQuote';
    import appConfiguratorPartExchange from 'bmw/components/Configurator/PartExchange';
    import appFinanceOverlay from 'bmw/components/FinanceOverlay';
    import appConfirmationModal from 'bmw/components/Elements/ConfirmationModal';
    import perfectScrollbar from 'perfect-scrollbar';
    import Stickyfill from '@rq/stickyfill';
    import UrlParser from 'bmw/mixins/UrlParser';
    import Constants from 'bmw/components/Shared/Constants';

    export default Vue.extend({
        mixins: [FinanceQuote, Constants, UrlParser],

        props: {
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
            },
            maintenanceDisabledNotification: {
                required: false,
                type: String
            },
            instalmentGroupId: {
                required: false,
                type: Number
            },
            configurableLeadTime: {
                required: false,
                type: Number
            }
        },

        data: () => ({
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
                'Line/Packages': 40,
                'Extra Options': 50,
                'Accessories': 60
            }
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
            closeFinancePopup() {
                this.removeOverlayParamFromURL(this.OVERLAYSEARCHPARAMS.FINANCE);

                if (this.overlayParamIsPresented(this.OVERLAYSEARCHPARAMS.TRADEIN)) {
                    this.showEditPartExchange();
                }
            },

            closeTradInPopup() {
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
            },

            showFinanceOverlay() {
                this.financeOverlay = true;
                this.addOverlayParamToUrl(this.OVERLAYSEARCHPARAMS.FINANCE);
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

            // This function fixes a glitch on IE11 where the scrollable height of the page is much larger than the content of the page
            // It fixes this by making the user scroll through the html tag rather than through the body (where the scroll height is broken)
            fixInternetExplorer() {
                const html = document.getElementsByTagName('html')[0];
                const body = document.getElementsByTagName('body')[0];
                // Test to check the bug is occurring
                if (html.scrollHeight > html.clientHeight && body.scrollHeight > body.clientHeight) {
                    html.style.overflow = 'auto';
                    body.style.overflow = 'hidden';
                    setInterval(() => {
                        // If the user manages to scroll the body somehow then reset their scroll
                        if (body.scrollTop) {
                            body.scrollTop = 0;
                        }
                    }, 100);
                }
            },

            hasStickySupport() {
                var hasSticky = false;
                // test for sticky native support
                var prefixes = ['', '-webkit-', '-moz-', '-ms-'],
                    block = document.createElement('div');

                for (var i = prefixes.length - 1; i >= 0; i--) {
                    try {
                        block.style.position = `${prefixes[i]}sticky`;
                    }
                        // eslint-disable-next-line
                    catch (e) {}
                    if (block.style.position !== '') {
                        hasSticky = true;
                        break;
                    }
                }
                return hasSticky;
            },

            stickyTopBottom(elem, options) {
                if (!this.hasStickySupport()) {
                    return;
                }
                options = jQuery.extend({
                    container: jQuery('body'),
                    top_offset: 0,
                    bottom_offset: 0,
                    use_on_mobile: false
                }, options);

                var el = jQuery(elem);
                el.addClass('sticky-top-bottom');
                var baseOffsetTop = parseInt(el.css('top'), 10);

                if (isNaN(baseOffsetTop)) {
                    baseOffsetTop = 0;
                }

                var viewportHeight = jQuery(window).height();

                jQuery(window).on('resize', (e) => {
                    viewportHeight = jQuery(window).height();
                });

                var currentTranslate = baseOffsetTop;
                var lastScrollTop = document.documentElement.scrollTop || document.body.scrollTop;

                jQuery(window).on('scroll', (e) => {
                    if (!options.use_on_mobile && this.isMobileResolution) {
                        return;
                    }
                    if (!el.height()) {
                        el = jQuery(el.selector);
                    }

                    var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                    var viewportTop = 0;
                    var viewportBottom = viewportTop + viewportHeight;
                    var effectiveViewportTop = viewportTop + baseOffsetTop + options.top_offset;
                    var effectiveViewportBottom = viewportBottom - options.bottom_offset;

                    var elementHeight = el.height();

                    var isScrollingUp = scrollTop <= lastScrollTop;
                    var elementFitsInViewport = elementHeight + effectiveViewportTop < viewportHeight;

                    var newTranslation = null;
                    if (isScrollingUp) {
                        if (currentTranslate < effectiveViewportTop) {
                            newTranslation = currentTranslate - (scrollTop - lastScrollTop);
                            if (newTranslation > effectiveViewportTop) {
                                newTranslation = effectiveViewportTop;
                            }
                        } else {
                            newTranslation = effectiveViewportTop;
                        }
                    } else if (elementFitsInViewport) {
                        newTranslation = baseOffsetTop + options.top_offset;
                    } else { // scrolling down
                        if (effectiveViewportBottom < elementHeight + currentTranslate) {
                            newTranslation = currentTranslate - (scrollTop - lastScrollTop);
                            if (newTranslation < effectiveViewportBottom - elementHeight) {
                                newTranslation = effectiveViewportBottom - elementHeight;
                            }
                        } else {
                            newTranslation = effectiveViewportBottom - elementHeight;
                        }
                    }
                    if (newTranslation != null) {
                        currentTranslate = newTranslation;
                        jQuery(el).css('top', `${currentTranslate}px`);
                    }

                    lastScrollTop = scrollTop;
                });
            },

            formatAdditionalFeeName(label) {
                return label.replace(/ /g, '-').toLowerCase();
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
            }
        },

        ready() {
            this.getFinanceOptionMethodType();
            this.openAllOptionGroupsMobile();
            this.handleFinanceQuoteMobileContainer();
            this.listenFinanceQuoteContentChange();
            this.initScrollbar();
            this.updateScrollbar();

            const stickyfill = Stickyfill(); // include library for browsers that do not provide native support
            stickyfill.add(document.getElementsByClassName('finance-quote-desktop-container')[0]);

            // If the browser is internet explorer 11
            if (!!window.MSInputMethodContext && !!document.documentMode) {
                this.fixInternetExplorer();
            }

            if (!this.isMobile) {
                this.stickyTopBottom(jQuery('.finance-quote-desktop-container'), {
                    container: jQuery('.inner-container'),
                });
            }

            if (this.overlayParamIsPresented(this.OVERLAYSEARCHPARAMS.FINANCE)) {
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
            appConfiguratorPartExchange,
            appConfirmationModal,
            appFinanceOverlay
        }
    });
</script>
