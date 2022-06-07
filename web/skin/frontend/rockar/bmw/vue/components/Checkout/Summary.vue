<template>
    <div class="checkout-summary">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <div class="row">
            <div class="col-4">
                <img :src="carImage" :alt="carTitle">
            </div>

            <div class="col-8">
                <div class="car-detail">
                    <p class="car-title">{{ carTitle }}</p>
                    <p>{{ carDescription }}</p>
                    <template v-for="(optionIndex, option) in sortedCarData">
                        <div class="options-list-block" v-if="option.type !== 'additional_fee'">
                            <div class="options-block-info" v-if="!option.group">
                                <p class="h5">
                                    <span>{{ option.label }}</span>
                                </p>
                            </div>
                            <div class="options-block-info" v-if="option.group && option.items.length">
                                <p class="h5" @click="openOptionGroup(optionIndex)">
                                    <a :class="[groupsOpenStatus[optionIndex] ? 'collapse' : 'expand']">{{ groupsOpenStatus[optionIndex] ? '-' : '+' }}</a>
                                    {{ option.group }}
                                </p>
                                <p v-for="item in option.items" v-show="groupsOpenStatus[optionIndex]">
                                    <span>{{ item.label | convertNCR }}</span>
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="row">
            <validator name="summary">
                <div class="col-12 content">
                    <div v-if="!tradeIn">
                        <div class="deliver">
                            <p>{{ 'You\'ve chosen your BMW, now place your order to start the journey to Sheer Driving Pleasure.' | translate }}</p>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <input type="checkbox" id="c1" v-model="accept" v-validate:terms="['required']" initial="off">
                                <label for="c1">
                                    <span></span>
                                    <p class="accept-terms-statement">{{ 'I accept the' | translate }}</p>
                                    <a href="javascript:void(0);" @click.prevent="this.$root.openInModal('termsConditionsModal')" class="terms-conditions">
                                        {{'Terms & Conditions' | translate}}
                                    </a>
                                </label>
                                <div class="validation-advice" v-if="!$summary.terms.valid">{{ 'You should accept Terms & Conditions' | translate }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-if="tradeIn">
                        <div class="row">
                            <div class="col-12">
                                <p>{{ 'You\'ve chosen your vehicle, now get a custom quote mailed to you.' | translate }}</p>
                                <p>{{ `The content of this quote (including the pricing/charges therein) is not legally binding and is for informational purposes only.
                                    Errors and omissions are excluded.` | translate }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-if="!isCashPayment" class="row">
                        <div class="col-12">
                            <p>{{ 'Legal Disclaimer' | translate }}<br>
                                {{ `Finance available through BMW Financial Services (South Africa (Pty) Ltd., an Authorised Financial Services (FSP 4623) and
                                Registered Credit Provider NCRCP2341 (“BMW Financial Services”))` | translate }}<br>
                                {{ `Any information obtained on this website calculator will not constitute a finance quote as contemplated by the National
                                Credit Act (No. 34 of 2005) (“NCA”). Any information provided through the calculator and/or the website, regarding our
                                finance products are subject to change and/or may be amended at any time. This calculator is based on an indicative interest rate.
                                The actual interest rate offered by BMW Financial Services, should you apply for vehicle finance, is dependant on your individual credit
                                profile and may differ from the interest rate indicated in the calculator. The calculator is also based on the condition of your current
                                vehicle to be traded in as reported by you and any outstanding finance you have declared. Your monthly payment will alter if either of these change.
                                No responsibility for any loss suffered by any person acting or refraining from acting as a result of the use of this calculator and/or information
                                obtained through this calculator or the website is accepted by BMW Financial Services. BMW Financial Services does not warrant that the information
                                obtained may be free of errors. Terms and Conditions apply.` | translate }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4 shift-8">
                            <div class="button-wrapper align-right">
                                <button
                                    class="button place-order"
                                    :class="[isValid ? 'button-dark' : 'button-disabled']"
                                    @click="submit()"
                                    :disabled="this.submitDisabled || !this.isValid"
                                >
                                        <span>
                                            <span>{{ tradeIn ? 'Reserve your BMW' : 'Place Order' | translate }}</span>
                                        </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </validator>
        </div>
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
</template>

<script>
    import VueValidator from 'vue-validator';
    Vue.use(VueValidator);

    import numeral from 'numeral';
    import appSelect from 'core/components/Elements/Select';
    import appModal from 'core/components/Elements/Modal';

    export default Vue.extend({
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
                groupsOpenStatus: [true, true, true],
                chosenCard: false,
                currencySymbol: window.currencySymbol,
                submitDisabled: false,
                newCarData: false,
                orderPlaceErrorMessage: false,
                orderPlaceErrorMessageTitle: false,
                outOfStock: false,
                invalidProductRedirectUrl: false
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
                return this.$root.$refs.partExchangeFilter;
            },

            tradeIn() {
                return this.partExchange.peId !== null && this.partExchange.peId !== undefined;
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

            openOptionGroup(index) {
                this.groupsOpenStatus.$set(index, !this.groupsOpenStatus[index]);
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
            }
        },

        events: {
            'Summary::deliveryPrice'(price) {
                this.deliveryPrice = price;
            },
        },

        ready() {
            EventsBus.$on('Checkout::UpdatedCarExtras', this.updateCarExtras);
        },

        components: {
            appSelect,
            appModal
        }
    });
</script>
