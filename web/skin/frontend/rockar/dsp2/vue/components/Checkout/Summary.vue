<template>
    <div class="checkout-summary">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <div class="step-header">
            {{ 'Summary' | translate }}
        </div>

        <div class="cosy-view" v-if="imageList">
            <app-configurator-carousel-images
                :slides='getImageList()'
                :options='carouselSettings'
                v-ref:cosy-view-carousel
            ></app-configurator-carousel-images>
        </div>

        <div class="car-detail">
            <app-accordion-group>
                <app-accordion
                    :title="extraFeatureTitle"
                    :scroll-on-show="false"
                    class-name="accordion-light"
                    type="right-down"
                    id="extra_features"
                >
                    <div v-for="(dataIndex, data) in clearCarExtras" :key="dataIndex">
                        <table v-if="data.group && data.items.length">
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="group-title">{{ data.group | translate }}</span>
                                    </td>
                                </tr>
                                <tr v-for="(index, item) in data.items" :key="index">
                                    <td>
                                        <span :class="{ 'no-icon': !item.remove }">{{ item.label | convertNCR }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </app-accordion>

                <app-accordion
                    title="Technical Features"
                    :scroll-on-show="false"
                    type="right-down"
                    class-name="accordion-light"
                    id="technical_features"
                >
                    <div v-for="(index, section) in technicalSpecItems" :key="index">
                        <table>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="group-title">{{ section.subtitle | translate }}</span>
                                    </td>
                                </tr>
                                <tr v-for="(index, item) in section.items" :key="index">
                                    <td>
                                        <span v-html="item.title"></span>
                                    </td>
                                    <td>
                                        <span class="table-right" v-html="item.value"></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </app-accordion>

                <app-accordion
                    :title="standardFeaturesTitle"
                    :scroll-on-show="false"
                    class-name="accordion-light"
                    type="right-down"
                    id="standard_features"
                >
                    <ul v-for="(index, features) in standardFeatures" :key="index">
                        <li class="accordion-list standard-features">
                            <div
                                class="features-item"
                                v-for="(index, item) in features.features"
                                :key="index"
                                track-by="$index"
                                v-html="item"
                            ></div>
                        </li>
                    </ul>
                </app-accordion>

                <app-accordion
                    title="Accessories"
                    :scroll-on-show="false"
                    class-name="accordion-light"
                    type="right-down"
                    id="accessories-list"
                    v-if="accessoriesData.length > 0"
                >
                    <ul v-for="(index, accessory) in accessoriesData" :key="index">
                        <li class="accordion-list accessories-list">
                            <div class="features-item">{{ accessory.label }}</div>
                        </li>
                    </ul>
                </app-accordion>
            </app-accordion-group>
        </div>

        <div class="summary-bottom">
            <validator name="summary">
                <div class="content">
                    <div class="disclaimers-wrapper" v-if="tradeIn || !isCashPayment">
                        <div v-if="tradeIn" class="quote-disclaimer">
                            <div>{{ quoteDisclaimer | translate }}</div>
                            <br>
                        </div>
                        <div v-if="!isCashPayment" class="legal-disclaimer">
                            <div v-html="legalDisclaimer | translate"></div>
                        </div>
                    </div>
                    <div class="actions-wrapper">
                        <div class="cta">
                            <div class="button-wrapper align-right">
                                <button
                                    class="button dsp2-money"
                                    :class="{ 'disabled': submitDisabled || !isValid }"
                                    @click="submit()"
                                    :disabled="submitDisabled || !isValid"
                                >
                                    <span>
                                        <span>{{ 'Place Order' | translate }}</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                        <div v-if="!tradeIn" class="checkbox">
                            <input type="checkbox" id="c1" v-model="accept" v-validate:terms="['required']" initial="off">
                            <label for="c1">
                                <span></span>
                                <p class="accept-terms-statement">{{ 'I accept the' | translate }}</p>
                                <a href="javascript:void(0);" @click.prevent="this.$root.openInModal('termsConditionsModal')" class="terms-conditions">{{ 'Terms & Conditions' | translate }}</a>
                            </label>
                            <div class="validation-advice" v-if="!$summary.terms.valid">{{ 'You should accept Terms & Conditions' | translate }}</div>
                        </div>
                    </div>
                </div>
            </validator>
        </div>
        <app-modal :show="!!this.orderPlaceErrorMessage" :show-close="false" class-name="simple-popup error-popup">
            <div slot="content">
                <h2 v-if="orderPlaceErrorMessageTitle">{{ orderPlaceErrorMessageTitle }}</h2>
                <h2 v-else>{{ 'Something went wrong!' | translate }}</h2>
                <div v-html="orderPlaceErrorMessage"></div>
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
    import VueValidator from 'vue-validator';
    Vue.use(VueValidator);

    import numeral from 'numeral';
    import appSelect from 'core/components/Elements/Select';
    import appModal from 'core/components/Elements/Modal';
    import appAccordion from 'core/components/Accordion';
    import appAccordionGroup from 'core/components/AccordionGroup';
    import appConfiguratorCarouselImages from 'core/components/Configurator/CarouselImages';
    import Constants from 'dsp2/components/Shared/Constants';
    import translateString from 'core/filters/Translate';
    import EventTracker from 'dsp2/mixins/EventTracker';

    export default Vue.extend({
        mixins: [Constants, EventTracker],

        props: {
            carImage: {
                required: true,
                type: String
            },

            resellerOverlayContent: {
                required: true,
                type: String
            },

            resellerDisagreeOverlayContent: {
                required: true,
                type: String
            },

            carTitle: {
                required: true,
                type: String
            },

            carDescription: {
                required: true,
                type: String
            },

            carExtras: {
                required: true,
                type: Array
            },

            orderPlaceUrl: {
                required: true,
                type: String
            },

            deliveryPrice: {
                required: true,
                type: String
            },

            partialDepositValue: {
                required: true,
                type: String
            },

            descriptionContent: {
                required: true,
                type: String
            },

            allowedCards: {
                required: true,
                type: Array
            },

            paymentBreakdown: {
                required: false,
                type: String
            },

            additionalCharges: {
                required: false,
                type: Array
            },

            minimumDepositAmount: {
                required: false,
                type: String
            },

            creditCardMapping: {
                required: false,
                type: Array
            },

            carFinderUrl: {
                required: false,
                type: String
            },

            technicalSpecItems: {
                required: false,
                type: Array,
                default() {
                    return [];
                }
            },

            standardFeatures: {
                required: false,
                type: Array,
                default() {
                    return [];
                }
            },

            legalDisclaimer: {
                required: true,
                type: String
            },

            imageList: {
                required: false,
                type: String,
                default: ''
            }
        },

        data() {
            return {
                ajaxLoading: false,
                reseller: false,
                resellerDisagree: false,
                deposit: 'partial',
                accept: false,
                acceptError: false,
                cardError: false,
                chosenCard: false,
                currencySymbol: window.currencySymbol,
                submitDisabled: false,
                newCarData: false,
                orderPlaceErrorMessage: false,
                orderPlaceErrorMessageTitle: false,
                outOfStock: false,
                invalidProductRedirectUrl: false,
                quoteDisclaimer: 'The content of this quote (including the pricing/charges therein) is not legally binding and is for informational purposes only. Errors and omissions are excluded.',
                carouselSettings: {
                    slidesToShow: 1,
                    initialSlide: 0,
                    mobileNavigation: 1,
                    dots: false,
                    lazyload: 'ondemand',
                    infinite: false,
                    mobileFirst: true,
                    centerMode: true,
                    centerPadding: '26%',
                    speed: 300,
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: {
                                centerPadding: '46.5%'
                            }
                        }
                    ]
                }
            }
        },

        computed: {
            isCashPayment() {
                const quote = this.$root.$refs.financeQuote;

                if (quote && quote.activePayment) {
                    return quote.payInFullPayment.find(payment => payment.group_id === quote.activePayment.group_id) !== undefined;
                }

                return true;
            },

            carData() {
                return this.newCarData ? this.newCarData : this.carExtras;
            },

            depositDisplayValue() {
                if (!this.isHire && !this.isCashPayment && this.deposit !== 'partial') {
                    return this.fullDepositDisplayValue;
                }

                return this.partialDepositValue;
            },

            fullDepositDisplayValue() {
                const quote = this.$root.$refs.financeQuote;

                return (quote && quote.financeParams) ? quote.payDeposit : 0;
            },

            additionalChargesAmount() {
                let chosenCardCode,
                    result = 0,
                    calculated = 0,
                    capFee = 0;

                this.creditCardMapping.forEach((el) => {
                    if (el.card_name === this.chosenCard) {
                        chosenCardCode = el.card_code
                    }
                });

                if (chosenCardCode) {
                    this.additionalCharges.forEach((el) => {
                        if (el.card_type === chosenCardCode) {
                            if (this.depositDisplayValue <= parseFloat(this.minimumDepositAmount)) {
                                return 0;
                            }

                            if (el.charge_type === 'percent') {
                                calculated = (parseFloat(this.depositDisplayValue) + parseFloat(this.deliveryPrice)) * parseFloat(el.charge) / 100;
                            } else {
                                calculated = el.charge;
                            }

                            if (parseFloat(el.cap_fee) > 0) {
                                capFee = parseFloat(el.cap_fee);
                                result = Math.min(capFee, calculated);
                            } else {
                                result = calculated;
                            }
                        }
                    });
                }

                return result;
            },

            depositFormattedPrice() {
                return this.currencySymbol + numeral(this.depositDisplayValue).format('0,0.00');
            },

            deliveryFormattedPrice() {
                return this.currencySymbol + numeral(this.deliveryPrice).format('0,0.00');
            },

            depositMultiple() {
                return this.$root.$refs.financeQuote ? this.$root.$refs.financeQuote.financeParams.depositMultiple : 0;
            },

            commissionFormattedPrice() {
                return this.currencySymbol + numeral(this.additionalChargesAmount).format('0,0.00');
            },

            totalFormattedPrice() {
                const totalPrice = parseFloat(this.depositDisplayValue) + parseFloat(this.deliveryPrice) + parseFloat(this.additionalChargesAmount);
                return this.currencySymbol + numeral(totalPrice).format('0,0.00');
            },

            isHire() {
                return this.$root.$refs.financeQuote ? this.$root.$refs.financeQuote.isHire : 0;
            },

            cardsList() {
                var cards = [];

                this.allowedCards.forEach((card) => {
                    cards.push({
                        title: card,
                        value: card
                    });
                });

                return cards;
            },

            isValid() {
                return (this.tradeIn || this.$summary.terms.modified) && this.$summary.valid;
            },

            displayResellerOverlay() {
                return (this.resellerOverlayContent.length > 0) && (this.resellerDisagreeOverlayContent.length > 0);
            },

            openStep() {
                return this.$parent.$parent.openStep;
            },

            sortedCarData() {
                this.carData.sort((a, b) => (a.sort_order - b.sort_order));

                return this.carData;
            },

            partExchange() {
                return this.$root.$refs.partExchange.$refs.partExchangeFilter;
            },

            tradeIn() {
                return this.partExchange.peId !== null && this.partExchange.peId !== undefined;
            },

            clearCarExtras() {
                this.carData.sort((a, b) => (a.sort_order - b.sort_order));

                return this.carData.filter(i => i.group !== 'Accessories');
            },

            accessoriesData() {
                const accessories = this.carData.filter(i => i.group === 'Accessories');

                return Array.isArray(accessories) && accessories.length ? accessories[0].items : [];
            },

            extraFeatureTitle() {
                return this.translateString('Extra Features');
            },

            standardFeaturesTitle() {
                return this.translateString('Standard Features');
            }
        },

        methods: {
            submit() {
                this.reseller = false;
                if (!this.accept && !this.tradeIn) {
                    this.acceptError = true;
                    return;
                }
                this.acceptError = false;
                this.cardError = false;
                this.submitDisabled = true;
                this.ajaxLoading = true;
                this.$http({
                    url: this.orderPlaceUrl,
                    method: 'POST',
                    emulateJSON: true,
                    params: {
                        deposit: this.isCashPayment || this.isHire ? 'partial' : this.deposit,
                        card: '',
                        px_id: this.partExchange.peId || 0
                    }
                }).then(this.orderPlaceSuccess, this.orderPlaceFail);
            },

            selectDisagreeAndContinue() {
                this.resellerDisagree = true;
                this.reseller = false;
            },

            orderPlaceSuccess(response) {
                // Login page is not in accordion group, indexes start with 0
                // So we must add 2 to compensate for that
                const stepIndex = this.openStep + 1,
                    optionSelectedObject = {
                        'event': 'checkoutOption',
                        'ecommerce': {
                            'checkout_option': {
                                'actionField': {
                                    'step': stepIndex, 'option': this.chosenCard
                                }
                            }
                        }
                    };

                pushEcommerceTags(optionSelectedObject);

                this.submitDisabled = false;
                this.ajaxLoading = false;
                window.location.replace(response.data.redirect);
            },

            orderPlaceFail(resp) {
                this.submitDisabled = false;
                this.ajaxLoading = false;

                if (resp.data.redirect && resp.status === 401) {
                    this.$root.loggedOutPopup(resp.data.redirect);

                    return;
                }

                if (resp.data.redirect && !resp.data.out_of_stock) {
                    window.location.href = resp.data.redirect;

                    return;
                }

                if (resp.data && resp.data.message) {
                    if (resp.data.slots_taken) {
                        this.openCalendarWithError(resp.data);
                    } else {
                        this.orderPlaceErrorMessage = resp.data.message;

                        if (resp.data.message_title) {
                            this.orderPlaceErrorMessageTitle = resp.data.message_title;
                        }

                        if (resp.data.out_of_stock) {
                            this.outOfStock = true;
                            this.invalidProductRedirectUrl = resp.data.redirect ? resp.data.redirect : this.carFinderUrl;
                        }
                    }
                }
            },

            parseVariablePaymentBreakdown(paymentBreakdown) {
                return this.$interpolate(paymentBreakdown);
            },

            openCalendarWithError(error) {
                this.$dispatch('Checkout::slotTaken', error);
            },

            chooseCard(data) {
                this.chosenCard = data.value;
            },

            showResellerOverlay() {
                this.reseller = true;
            },

            updateCarExtras() {
                if (sessionStorage.getItem('car_data') && sessionStorage.getItem('from_pdp') === 'true') {
                    this.newCarData = JSON.parse(sessionStorage.getItem('car_data'));
                }
            },

            processFailure() {
                if (this.outOfStock) {
                    window.location.href = sessionStorage.getItem('CarFinder::redirectToPdp') || this.invalidProductRedirectUrl;

                    return;
                }

                window.location.reload();
            },

            getImageList() {
                const result = [];
                const imageList = JSON.parse(this.imageList);

                imageList.forEach(item => {
                    result.push(item);
                });

                return result;
            },

            setCosyViewCarouselPosition() {
                this.$nextTick(() => {
                    jQuery(this.$refs.cosyViewCarousel.$el).slick('setPosition');
                });
            },

            /**
             * Fire event for tracking purposes on initial load of summary
             */
            fireEvent() {
                this.fireEventForTracking(
                    this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                    this.getEventConstants().EVENTRACKERVALUES.CHECKOUTSUMMARY
                );
            },

            translateString
        },

        events: {
            'Summary::deliveryPrice'(price) {
                this.deliveryPrice = price;
            },
        },

        watch: {
            openStep(newVal) {
                if (newVal === this.CHECKOUT_SUMMARY_STEP
                    && this.imageList
                    && this.$refs.cosyViewCarousel
                    && this.$refs.cosyViewCarousel.$el
                ) {
                    this.setCosyViewCarouselPosition();
                    this.fireEvent();
                } else if (newVal === this.CHECKOUT_SUMMARY_STEP) {
                    this.fireEvent();
                }
            }
        },

        ready() {
            EventsBus.$on('Checkout::UpdatedCarExtras', this.updateCarExtras);

            if (this.imageList
                && this.$refs.cosyViewCarousel
                && this.$refs.cosyViewCarousel.$el
            ) {
                this.setCosyViewCarouselPosition();
            }
        },

        components: {
            appSelect,
            appModal,
            appAccordion,
            appAccordionGroup,
            appConfiguratorCarouselImages
        }
    });
</script>
