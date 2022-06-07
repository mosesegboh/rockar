<template>
    <div class="my-saved-cars">
        <div class="my-saved-cars-wrapper">
            <div class="preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
            <div class="row">
                <div class="col-3 car-cover">
                    <img :src="carCoverExterior" :alt="carTitle">
                    <div class="saved-cars-cover-actions" v-if="carImagesExist">
                        <button class="button button-narrow open-showcase" @click="openShowcase()">{{ 'Showcase' | translate }}</button>
                    </div>
                    <div class="showcase" v-show="showcase.show" transition="fade">
                        <div class="showcase-background" @click="closeShowcase()"></div>

                        <div class="showcase-wrapper">
                            <div class="showcase-image">
                                <button class="arrow-prev fc-arrow" @click="prevImage()" v-if="hasMultipleImages"></button>

                                <img id="my-saved-cars-image" :src="showcase.image">

                                <button class="arrow-next fc-arrow" @click="nextImage()" v-if="hasMultipleImages"></button>

                                <div class="close-background">
                                    <a class="showcase-close" @click="closeShowcase()"></a>
                                </div>

                                <div class="showcase-actions">
                                    <a class="showcase-select" :class="{ active: showcase.type === 'exterior' }" @click="changeType('exterior')">
                                        {{ 'Exterior' | translate }}
                                    </a>

                                    <a class="showcase-select" :class="{ active: showcase.type === 'interior' }" @click="changeType('interior')">
                                        {{ 'Interior' | translate }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-3 saved-cars-info">
                    <p class="h-common user-title">{{ userTitle }}</p>
                    <p class="block-title h-common h-small">{{ carTitle }}</p>
                </div>

                <div class="col-5 shift-1 saved-cars-price">
                    <p class="block-title h-common h-small" :class="[ carStock < 1 ? 'out-of-stock' : '']">{{ 'Price on' | translate }}
                        {{ carAddedDateFormatted }} <span v-if="carStock < 1"><br>{{ 'Now out of stock' | translate }}</span></p>
                    <div class="row">
                        <div class="col-4 saved-cars-price-block" v-if="!financeData.isHire">
                            <p class="payment-amount">{{ getOfferPrice | numberFormat '0,0' true }}
                                <span class="action-badge info light" v-if="financeData.monthlyPrice === 0" @click="showFinanceOverlay()"></span>
                            </p>
                            <p class="payment-label">{{ 'Offer price' | translate }}</p>
                        </div>

                        <div class="col-4 saved-cars-price-block" v-if="financeData.saveOffRrp > 0 && !financeData.isHire">
                            <p class="payment-amount">{{ financeData.saveOffRrp | numberFormat '0,0' true }}</p>
                            <p class="payment-label">{{ 'off RRP' | translate }}</p>
                        </div>

                        <div class="col-4 saved-cars-price-block" v-if="financeData.cashDeposit > 0 && financeData.isHire">
                            <p class="payment-amount">{{ financeData.cashDeposit | numberFormat '0,0' true }}
                                <span class="action-badge info light" v-if="financeData.monthlyPrice === 0" @click="showFinanceOverlay()"></span>
                            </p>
                            <p class="payment-label">{{ 'Initial Payment' | translate }}</p>
                        </div>

                        <div class="col-4 saved-cars-price-block" v-if="financeData.cashback > 0 && financeData.isHire">
                            <p class="payment-amount">{{ financeData.cashback | numberFormat '0,0' true }}
                                <span class="action-badge info light" @click="showFinanceOverlay()"></span>
                            </p>
                            <p v-else class="payment-label">{{ 'Cashback' | translate }}</p>
                        </div>

                        <div class="col-4 saved-cars-price-block" v-if="financeData.monthlyPrice !== 0 && carStock >= 1">
                            <p class="payment-amount">{{ financeData.monthlyPrice | numberFormat '0,0' true }}
                                <span class="action-badge info light" @click="showFinanceOverlay()"></span>
                            </p>
                            <p v-if="financeData.isHire" class="payment-label">{{ 'A month' | translate }}</p>
                            <p v-else class="payment-label">{{ 'Per month' | translate }}</p>
                        </div>

                    </div>

                    <div class="row saved-cars-price-action">
                        <button class="button-default button-narrow col-6" @click="goTo(carRedirectUrl)" v-if="carStock >= 1">{{ 'Proceed to Checkout' | translate }}</button>
                        <button class="button-default button-narrow col-5" @click="goTo(carFinderUrl)" v-if="carStock < 1">{{ 'Find Other' | translate }}</button>
                        <app-confirmation-modal :confirmation-question="'Do you really want to Remove?' | translate">
                            <button class="button-empty-light button-narrow col-5" @click="removeCar">{{ 'Remove' | translate }}</button>
                        </app-confirmation-modal>
                    </div>
                </div>
            </div>

            <app-extras :car-extras='carExtras'></app-extras>

            <app-modal :show.sync="financeOverlay" v-if="financeOverlay" width="80%" >
                <div slot="content">
                    <app-finance-overlay
                        :product-id='financeData.productId'
                        :finance-url='financeData.financeUrl'
                        :finance-params-origin='financeData.financeParams'
                        :finance-slider-steps='financeData.financeSliderSteps'
                        :pay-in-full-payment='financeData.payInFullPayment'
                        :hire-payments='financeData.hirePayments'
                        :active-payment='financeData.activePayment'
                        :payment-save-url='financeData.paymentSaveUrl'
                        :product-url='financeData.carRedirectUrl'
                        :update-finance-quote='true'
                        :calc-type='financeData.calcType'
                        :use-short-version='true'
                    ></app-finance-overlay>
                </div>
            </app-modal>
        </div>

        <div class="my-saved-cars-mobile-wrapper">
            <div class="hero-title">
                <p class="user-title">{{ userTitle }}</p>
            </div>

            <div class="hero-image">
                <img :src="carCoverExterior" :alt="carTitle">
                <div class="out-of-stock" v-if="carStock < 1">
                    <div class="out-of-stock-label">
                        {{ 'Out of stock' | translate }}
                    </div>
                </div>
            </div>

            <div class="car-content">
                <div class="car-header">
                    <div class="car-header-title">
                        <p class="block-title h2">{{ carTitle }}</p>
                    </div>
                    <div class="car-header-action">
                        <app-confirmation-modal :confirmation-question="'Do you really want to Remove?' | translate">
                        <button class="button-empty-light" @click="removeCar">{{ 'Remove' | translate }}</button>
                        </app-confirmation-modal>
                    </div>
                </div>

                <app-extras :car-extras='carExtras'></app-extras>

                <div class="car-prices">
                    <div class="price-title">{{ 'Price on' | translate }} {{ carAddedDateFormatted }}</div>
                    <div class="price-section">
                        <div class="price-block" v-if="!financeData.isHire">
                            <p class="payment-amount">{{ getOfferPrice | numberFormat '0,0' true }}</p>
                            <p class="payment-label">{{ 'Offer Price' | translate }}</p>
                        </div>
                        <div class="price-block" v-else>
                            <p class="payment-amount">{{ financeData.cashDeposit | numberFormat '0,0' true }}</p>
                            <p class="payment-label">{{ 'Initial Payment' | translate }}</p>
                        </div>

                        <div class="price-block" v-if="financeData.saveOffRrp > 0 && !financeData.isHire">
                            <p class="payment-amount">{{ financeData.saveOffRrp | numberFormat '0,0' true }}</p>
                            <p class="payment-label">{{ 'off RRP' | translate }}</p>
                        </div>
                        <div class="price-block" v-if="financeData.cashback > 0 && financeData.isHire">
                            <p class="payment-amount">{{ financeData.cashback | numberFormat '0,0' true }}</p>
                            <p class="payment-label">{{ 'Cashback' | translate }}</p>
                        </div>

                        <div class="price-block" v-if="financeData.monthlyPrice !== 0 && carStock >= 1">
                            <p class="payment-amount">{{ financeData.monthlyPrice | numberFormat '0,0' true }}</p>
                            <p class="payment-label">{{ 'A month' | translate }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="save-action">
                <button class="button" @click="goTo(carRedirectUrl)" v-if="carStock >= 1">{{ 'Proceed to Checkout' | translate }}</button>
                <button class="button" @click="goTo(carFinderUrl)" v-if="carStock < 1">{{ 'Out of Stock - Find Another' | translate }}</button>
            </div>
        </div>
    </div>
</template>

<script>
    import coreMySavedCars from 'core/components/MyAccount/MySavedCars';
    import appFinanceOverlay from 'mini/components/FinanceOverlay';
    import appExtras from 'mini/components/MyAccount/MySavedCarsExtras';

    export default coreMySavedCars.extend({
        props: {
            wishlistItemPrice: {
                required: true,
                type: Number
            }
        },

        computed: {
            /**
             * Temporary fix to show saved wishlist item price if product is out of stock
             */
            getOfferPrice() {
                return this.carStock >= 1 ? this.financeData.rockarPrice : this.wishlistItemPrice;
            }
        },

        components: {
            appFinanceOverlay,
            appExtras
        }
    });
</script>
