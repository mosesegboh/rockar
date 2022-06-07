<template>
    <div class="get-quote">
        <div class="general-preloader" v-show="isAjaxLoading">
            <div class="show-loading"></div>
        </div>

        <div class="actions-wrapper">
            <button @click="getQuoteCta" class="button get-quote-icon-button">
                <span class="get-quote-cta-icon icon">{{ 'Get quote' | translate }}</span>
            </button>
        </div>

        <app-modal :show.sync="popupOpen" class-name="simple-popup">
            <div slot="content">
                <div class="get-quote-popup" v-if="showPopupStep('cta')">
                    <header class="modal-header">
                        {{ 'Send me a quote.' | translate }}
                    </header>
                    <div class="row">
                        <p>
                            {{ 'A quote for' | translate }}
                            <span>{{ quoteName }}</span>
                            {{ 'will be emailed to you.' | translate }}
                        </p>
                    </div>
                    <div class="row actions-row">
                        <button
                            type="button"
                            @click="getQuote"
                            class="button button-slate-gray"
                        >
                            {{ 'Email quote' | translate }}
                        </button>
                    </div>
                </div>

                <div class="get-quote-popup-success" v-if="showPopupStep('success')">
                    <header class="modal-header">
                        {{ 'Quote successfully sent:' | translate }}
                    </header>
                    <div class="row">
                        <p>
                            {{ `An indicative quote (subject to T's & C's) has been sent to` | translate }}
                            <span class="customer-email">
                                {{ customerEmail }}.
                            </span>
                        </p>
                        <p>
                            <span>{{ quoteName }}</span>
                            {{ `has also been added to the wishlist located in` | translate }}
                            <a :href="myAccountUrl" class="my-account-link">
                                {{ 'your account' | translate }}
                            </a>
                            {{ `for added convenience.` | translate }}
                        </p>
                        <p class="notes">
                            {{
                                `Please note: Requesting quotes and adding vehicles to your wishlist
                                does not reserve them on our system.` | translate
                            }}
                        </p>
                    </div>
                    <div class="row actions-row">
                        <template v-if="showContinueShoppingCta">
                            <a
                                v-if="redirectToContinueShopping"
                                :href="parsedContinueShoppingUrl"
                                class="button button-slate-gray"
                                @click="closePopup"
                            >
                                {{ 'Continue shopping' | translate }}
                            </a>
                            <button
                                v-if="!redirectToContinueShopping"
                                class="button button-slate-gray"
                                @click="closePopup"
                            >
                                {{ 'Continue shopping' | translate }}
                            </button>
                        </template>
                    </div>
                </div>

                <div class="get-quote-popup-failure" v-if="showPopupStep('failure')">
                    <div class="row">
                        <p>{{ errorMessage || 'An error occurred...' | translate }}</p>
                    </div>
                    <div class="row actions-row">
                        <a
                            v-if="showContinueShoppingCta"
                            :href="parsedContinueShoppingUrl"
                            @click="closePopup"
                            class="button button-slate-gray"
                        >
                            {{ 'Continue Shopping' | translate }}
                        </a>
                    </div>
                </div>
            </div>
        </app-modal>
    </div>
</template>

<script>
import appModal from 'bmw/components/Elements/Modal';
import ajaxHelper from 'bmw/components/Shared/AjaxHelper';

export default Vue.extend({
    mixins: [ajaxHelper],

    props: {
        /**
         * @property {(String|Number)} productId
         * The configurable product id
         */
        productId: {
            type: [String, Number],
            required: true
        },

        /**
         * @property {(String|Number|Boolean)} vehicleId
         * The simple product for saving to the wishlist
         * If false or no value given then the value is taken from the $store
         */
        vehicleId: {
            type: [String, Number, Boolean],
            required: false,
            default: false
        },

        /**
         * @property {String} customerEmail
         * The customer's email
         */
        customerEmail: {
            type: [String, Boolean],
            required: true
        },

        /**
         * @property {String} quoteName
         * The name to show for the quote
         */
        quoteName: {
            type: String,
            required: true
        },

        /**
         * @property {Boolean} showContinueShoppingCta
         * Whether or not to show "Continue Shopping" link
         */
        showContinueShoppingCta: {
            type: Boolean,
            required: false,
            default: false
        },

        /**
         * @property {Boolean} redirectToContinueShopping
         * Whether or not to redirect the user when they select "Continue Shopping"
         */
        redirectToContinueShopping: {
            type: Boolean,
            required: false,
            default: false
        },

        /**
         * @property {Boolean} customerIsLoggedIn
         * Boolean check whether the customer is logged in
         */
        customerIsLoggedIn: {
            type: Boolean,
            required: false,
            default: false
        },

        /**
         * @property {String} getQuoteUrl
         * The url to call for getQuote action
         */
        getQuoteUrl: {
            type: String,
            required: false,
            default: '/sales/quote/getQuote'
        },

        /**
         * @property {String} myAccountUrl
         * The url to go to my account my saved cars
         */
        myAccountUrl: {
            type: String,
            required: false,
            default: '/customer/my-account#my-saved-cars'
        },

        /**
         * @property {String} continueShoppingUrl
         * The url to go to continue shopping
         */
        continueShoppingUrl: {
            type: String,
            required: false,
            default: ''
        },

        /**
         * @property {String} customerLoginUrl
         * The url to login
         */
        customerLoginUrl: {
            type: String,
            required: false,
            default: '/customer/account/login'
        }
    },

    data() {
        return {
            popupOpen: false,
            popupStep: '',
            errorMessage: ''
        }
    },

    computed: {
        /**
         * Parsed Vehicle id
         * @return {(String,Number,null)}
         */
        parsedVehicleId() {
            return this.vehicleId
                || this.$store.state.configurator.selectedCar
                || null;
        },

        /**
         * Parsed Continue Shipping Url
         * @return {String}
         */
        parsedContinueShoppingUrl() {
            return this.continueShoppingUrl
                // This sessionStorage value is set when opening a product pod on results page
                || window.sessionStorage.getItem('CarFinder::redirectToPdp')
                || '/car-finder';
        }
    },

    methods: {
        /**
         * Customer has started the get quote sub journey
         */
        getQuoteCta() {
            if (!this.customerIsLoggedIn) {
                this.redirectToLogin();
            } else {
                this.openPopup();
            }
        },

        /**
         * Redirect to Login Page/
         */
        redirectToLogin() {
            window.location.href = this.customerLoginUrl;
        },

        /**
         * Open Popup
         */
        openPopup() {
            this.popupStep = 'cta';
            this.popupOpen = true;
        },

        /**
         * Close Popup
         */
        closePopup() {
            this.popupOpen = false;
            this.popupStep = '';
        },

        /**
         * Get Quote
         * @return {Promise<void>}
         */
        getQuote() {
            this.setLoader('getQuote');

            return this.$http({
                url: this.getQuoteUrl,
                method: 'POST',
                emulateJSON: true,
                data: {
                    vehicle_id: this.parsedVehicleId,
                    quote_name: this.quoteName,
                    product_id: this.productId
                }
            })
                .then(this.getQuoteSuccess)
                .catch(this.getQuoteFailure);
        },

        /**
         * Get Quote Success
         * @param {Object} data
         * @return {void}
         */
        getQuoteSuccess(data) {
            this.setLoader('getQuote', false);
            this.popupStep = 'success';
        },

        /**
         * Get Quote Failure
         * @param {Error} error
         * @return {void}
         */
        getQuoteFailure(error) {
            this.setLoader('getQuote', false);
            this.popupStep = 'failure';
        },

        /**
         * Show popup step
         * @param {String} step
         * @return {Boolean}
         */
        showPopupStep(step) {
            return this.popupStep === step;
        }
    },

    components: {
        appModal
    }
});
</script>
