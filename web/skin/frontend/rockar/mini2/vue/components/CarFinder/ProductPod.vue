<template>
    <div class="category-products-list-item product-pod" transition="opacity" :data-car-id="productId">
        <div class="pod-overlay-wrap">
            <div
                class="pod-overlay save-overlay"
                :class="{'active': saveCarOverlay, 'added-to-wishlist': isInWishlist}"
                @click="toggleSaveCarOverlay"
            >
                <p>{{ 'Wishlist' | translate }}</p>
            </div>
        </div>
        <div class="pod-caption mobile">
            <div class="caption">
                <div class="title" v-on:mouseup="openCar">
                    {{ title }}
                </div>
                <div class="subtitle">
                    <p>{{ subtitle }}<br><span v-if="isProductColorNameDisplayed">{{ productColorName }}</span></p>
                </div>
            </div>
        </div>
        <div class="pod-content">
            <div class="pod-images" :class="{'attributes-visible': attributeOverlay}">
                <app-offer-tags v-if="offerTagData && podImagesType !== 'interior'"
                                class="pod-offer-tag"
                                :offer-tag-data="offerTagData">
                </app-offer-tags>
                <app-product-pod-overlay
                    :attribute-overlay="attributeOverlay"
                    :car-options-visible="carOptionsVisible"
                    :attribute-type="attributeType"
                    :images="podExteriorInteriorImages"
                    :computed-key-features="computedKeyFeatures"
                    :custom-options="customOptions"
                    :custom-options-total="customOptionsTotal"
                    :product-id="productId"
                    :last-attribute-overlay-product-id="lastAttributeOverlayProductId">
                </app-product-pod-overlay>
                <div class="image-type-switcher">
                    <div class="actions-wrapper">
                        <div
                            class="switcher exterior-switch"
                            :class="{'active': podImagesType === 'default exterior'}"
                            @click="setPodImagesType('default exterior')"
                        ></div>
                        <div
                            class="switcher interior-switch"
                            :class="{'active': podImagesType === 'interior'}"
                            @click="setPodImagesType('interior')"
                        ></div>
                    </div>
                </div>
                <div class="carousel" v-bind:class="{'exterior': podImagesType === 'default exterior'}">
                    <a class="js-exterior-image" v-on:mouseup="openCarImage">
                        <div class="images-content">
                            <img
                                class="default-exterior"
                                :src="podExteriorDefaultImage[0].image"
                                :alt="title"
                                v-show="podImagesType === 'default exterior'"
                            >
                            <img
                                class="default-interior"
                                :src="podInteriorImage[0].image"
                                :alt="title"
                                v-show="podImagesType === 'interior'"
                            >
                        </div>
                    </a>
                </div>
                <app-pod-summary-specs
                    :car-attributes="carAttributes"
                    :fuel-type="carData.fuel_type">
                </app-pod-summary-specs>
                <button class="pod-specs-button"
                        :class="{'active': attributeOverlay}"
                        v-show="podImagesType !== 'interior'"
                        @click="attributeOverlayToggle">
                    {{ 'More Specifications' | translate }}
                </button>
                <button class="pod-specs-button mobile"
                        :class="{'active': attributeOverlay}"
                        v-show="podImagesType !== 'interior'"
                        @click="attributeOverlayToggle">
                    {{ 'Specifications' | translate }}
                </button>
            </div>
            <div class="right-wrapper">
                <div class="pod-attrs">
                    <div class="pod-caption">
                        <div class="caption">
                            <div class="title" v-on:mouseup="openCar">
                                {{ title }}
                            </div>
                            <div class="subtitle">
                                <p>{{ subtitle }}<br><span v-if="isProductColorNameDisplayed">{{ productColorName }}</span></p>
                            </div>
                        </div>
                    </div>
                    <app-attributes
                        :car-attributes="carAttributes">
                    </app-attributes>
                    <div class="pod-mileage" v-if="vehicleMileage">
                        <p class="pod-mileage-title">{{ 'Mileage' | translate }}</p>
                        <p class="pod-mileage-value">{{ vehicleMileage }}</p>
                    </div>
                </div>
                <div class="pod-data" v-el:pod-data>
                    <div class="pod-attribute attr-day" v-if="showGetQuoteCta">
                        <div class="price-wrapper">
                            <span>{{ 'Delivery within' | translate }}</span>
                            <span>{{ leadTimeRounded }}</span>
                            <span>{{ `week${leadTimeRounded == 1 ? '' : 's'}` | translate }}</span>
                        </div>
                    </div>
                    <div class="product-prices">
                        <div class="item-details-price-block">
                            <div class="item-details-price monthly-price" v-if="cashDeposit >= 0 && monthlyPrice > 0">
                                <div class="price-wrapper">
                                    <span class="label">{{ 'Per Month' | translate }}</span>
                                    <p class="price">
                                        <span class="price-value">{{ Math.round(monthlyPrice) | numberFormat '0,0' true }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="item-details-price rockar-price" v-if="!isHire">
                                <div class="price-wrapper">
                                    <span class="label">{{ 'Offer Price' | translate }}</span>
                                    <span class="price">{{ rockarPrice | numberFormat '0,0' true }}</span>
                                </div>
                            </div>
                            <div class="item-details-price initial-payment" v-else>
                                <div class="price-wrapper">
                                    <span class="label">{{ 'Initial Payment' | translate }}</span>
                                    <span class="price">{{ cashDeposit | numberFormat '0,0' true }}</span>
                                </div>
                            </div>
                            <div class="item-details-price cash-back" v-if="cashback > 0 && isHire">
                                <div class="price-wrapper">
                                    <span class="label">{{ 'Cash Back' | translate }}</span>
                                    <span class="price">{{ cashback | numberFormat '0,0' true }}</span>
                                </div>
                            </div>
                            <div class="item-details-price off-rrp" v-if="!isHire && saveOffRrp > 0">
                                <div class="price-wrapper">
                                    <span class="price">{{ saveOffRrp | numberFormat '0,0' true }}</span>
                                    <span class="label">{{ 'Saving off RRP' | translate }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pod-data-actions">
                        <div class="pod-data-action get-quote" v-if="showGetQuoteCta">
                            <app-get-quote-cta
                                :product-id="productId"
                                :customer-email="customerEmail"
                                :quote-name="quoteTitle"
                                :redirect-to-continue-shopping="redirectToContinueShopping"
                                :my-account-url="myAccountUrl"
                                :get-quote-url="getQuoteUrl"
                                :show-continue-shopping-cta="showContinueShoppingCta"
                                :customer-is-logged-in="customerIsLoggedIn"
                                :continue-shopping-url="continueShoppingUrl"
                                :customer-login-url="customerLoginUrl"
                            ></app-get-quote-cta>
                        </div>
                        <div class="pod-data-action payment-info">
                            <button class="pod-finance-button" @click="showFinanceOverlay">
                                {{ 'Payment Options' | translate }}
                            </button>
                        </div>
                    </div>
                    <div class="pod-actions">
                        <button class="pod-action action-compare" :class="{ 'remove': isInCompareList}" @click="saveUrl()">
                            {{ isInCompareList ? 'Remove' : 'Compare' | translate }}
                        </button>
                        <button class="pod-action action-buy" @click="openCar">
                            {{ 'View Vehicle' | translate }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <app-save-car
            :open="saveCarOverlay"
            :customer-name="customerName"
            :car-id="productId"
            :product-name="title"
            :product-title="title"
            :product-subtitle="subtitle"
            :product-sku="sku"
            :save-wishlist-url="saveWishlistUrl"
            :my-account-url="myAccountUrl"
            :is-in-wishlist="isInWishlist"
            :is-ajax-request="isAjaxRequest"
            :image="this.podExteriorDefaultImage[0].image"
            :remove-from-wishlist-url="removeFromWishlistUrl"
            @toggle-is-in-wishlist="toggleIsInWishlist"
            @open-save-car-overlay="openSaveCarOverlay"
            @close-save="saveCarOverlay = false">
        </app-save-car>

        <app-car-compare
            :open.sync="carCompareOverlay"
            :compare="compare"
            :compare-add-url="compareAddUrl"
            :compare-remove-url="compareRemoveUrl"
            :is-in-compare-list.sync="isInCompareList"
            @close-compare="carCompareOverlay = false"
        ></app-car-compare>

        <app-modal
            :show.sync="financeOverlay"
            :blur-background="false"
            v-if="financeOverlay"
            class="finance-popup finance-filters-overlay"
            :class="{'expired': sessionError}"
        >
            <div slot="content">
                <app-finance-overlay
                    :product-id="productId"
                    :finance-url="financeUrl"
                    :finance-params-origin="financeParams"
                    :finance-slider-steps="financeSliderSteps"
                    :pay-in-full-payment="payInFullPayment"
                    :hire-payments="hirePayments"
                    :active-payment="activePayment"
                    :payment-save-url="paymentSaveUrl"
                    :product-url="productUrl"
                    :instalment-group-id="instalmentGroupId"
                    :image="this.podExteriorDefaultImage[0].image"
                    :title="title"
                    :subtitle="subtitle"
                    calc-type="carfinder">
                </app-finance-overlay>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import appProductPod from 'dsp2/components/CarFinder/ProductPod';
    import appCarCompare from 'mini2/components/CarFinder/CarCompare';
    import appSaveCar from 'mini2/components/SaveCar';
    import appPodSummarySpecs from 'mini2/components/Elements/PodSummarySpecs';
    import appGetQuoteCta from 'mini2/components/Elements/GetQuoteCta';

    export default appProductPod.extend({
        components: {
            appSaveCar,
            appCarCompare,
            appPodSummarySpecs,
            appGetQuoteCta
        }
    });
</script>
