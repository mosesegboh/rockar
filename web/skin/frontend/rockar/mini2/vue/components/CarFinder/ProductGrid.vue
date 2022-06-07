<template>
    <div v-if="$parent.steps[$parent.currentStep] === 'carFilter' && !productGridHidden"
         class="category-products col-9 col-md-8"
         v-el:category-products
    >
        <div class="mobile-only category-products-filter-menu">
            <div class="heading-wrapper">
                <div class="car-filter-count-heading">
                    <span>{{ $parent.CarFilter.getCarListProductCount() }}</span>
                    {{ 'Results' | translate }}
                </div>
                <div class="text-wrapper" @click="openMobileMenu()">
                    <div class="filter-icon"></div>
                    <div class="filter-message">{{ 'Filters' | translate }}</div>
                </div>
                <app-results-filter
                    @remove="removeFilter"
                    :applied-filters="appliedFilters"
                    :is-filter-visible="isFilterVisible"
                    :options="options"
                    :filters="filters"
                >
                </app-results-filter>
            </div>
        </div>

        <template v-if="$parent.steps[$parent.currentStep] === 'carFilter'">
            <p class="note-msg" v-if="productsListEmptyMessage && gridEnabled">{{ 'There are no products matching the selection.' | translate }}</p>
            <p class="note-msg" v-if="!gridEnabled">{{ 'Please select preferred payment option.' | translate }}</p>
            <div class="general-preloader" v-show="ajaxGlobalLoading"><div class="show-loading"></div></div>

            <slot></slot>

            <div class="car-filter-count mobile-borders" v-if="$parent.CarFilter.getCarListProductCountYouDrive() > 0">
                <span class="car-filter-count-heading">{{ $parent.CarFilter.getCarListProductCountYouDrive() }}</span>
                <div class="car-filter-count-info-container">
                    <span class="car-filter-count-message">{{ 'Matching Ex-Demos' | translate }} </span>
                    <span class="car-filter-promotions-sub-message" v-if="$parent.CarFilter.getShortestLeadTimeYouDrive()">
                    {{ 'Delivery' | translate }} {{ $parent.CarFilter.getShortestLeadTimeYouDriveMessage() }}</span>
                </div>
                <template v-for="(index, section) in $parent.CarFilter.getMenuFilters()">
                    <template v-if="section.code === 'visible_in'">
                        <div class="checkbox-wrapper" :key="index">
                            <input
                                type="checkbox"
                                id="toggle-car-filter"
                                :checked="$parent.CarFilter.isYouDriveFilterChecked"
                                @click="$parent.CarFilter.showYouDriveCars(section);"
                            >
                            <label class="checkbox-filter" for="toggle-car-filter"><span class="checkbox-filter-demonstrators"></span></label>
                            <span class="checkbox-text">{{ 'Show' | translate }}</span>
                        </div>
                    </template>
                </template>
            </div>

            <app-you-build-action-block
                v-if="isEnabledYouBuildBlock"
                :you-build-url="youBuildUrl"
                :model-attribute="modelAttribute"
                :you-build-products-count="youBuildProductsCount"
            >
                <template slot="message">{{ youBuildBlockMessage}}</template>
            </app-you-build-action-block>

            <app-results-filter
                @remove="removeFilter"
                :applied-filters="appliedFilters"
                :is-filter-visible="isFilterVisible"
                :options="options"
                :filters="filters"
            >
            </app-results-filter>

            <ul class="category-products-list" v-if="gridEnabled">
                <li v-for="product in productsList.products" :track-by="product?.productId" :key="product?.productId">
                    <app-product-pod
                        :product-index="$index"
                        :product-id="product?.productId"
                        :sku="product.sku"
                        :product-url="product.url"
                        :title="product.title"
                        :subtitle="product.bodystyle"
                        :rockar-price="product.price"
                        :monthly-price="product.monthlyPrice"
                        :optional-final-payment="product.optionalFinalPayment"
                        :cash-deposit="product.cashDeposit"
                        :cashback="product.cashback"
                        :is-hire="product.isHire"
                        :save-off-rrp="product.saveOffRrp"
                        :display-annual="product.displayAnnual"
                        :tooltip="product.tooltip"
                        :images="product.images"
                        :overlay-text="product.overlayText"
                        :car-attributes="product.carAttributes"
                        :key-features="product.keyFeatures"
                        :is-in-wishlist="product.isInWishlist"
                        :save-wishlist-url="product.saveWishlistUrl"
                        :remove-from-wishlist-url="product.removeFromWishlistUrl"
                        :offer-tag-data="product.offerTagData"
                        :finance-url="options.financeUrl"
                        :finance-params="financeParams"
                        :finance-slider-steps="financeSliderSteps"
                        :pay-in-full-payment="payInFullPayment"
                        :hire-payments="hirePayments"
                        :active-payment="activePayment"
                        :active-payment-name="activePaymentName"
                        :payment-save-url="options.paymentSaveUrl"
                        :compare="compare"
                        :compare-add-url="product.compareAddUrl"
                        :compare-remove-url="product.compareRemoveUrl"
                        :is-in-compare-list="product.isInCompareList"
                        :customer-name="options.customerName"
                        :customer-email="options.customerEmail"
                        :my-account-url="options.myAccountUrl"
                        :is-ajax-request="options.isAjaxRequest"
                        :marketing-rule="product.marketingRule"
                        :car-data="product.carData"
                        :lead-time="product.leadTime"
                        :custom-options="product.customOptions"
                        :product-color-name="product.productColorName"
                        :is-product-color-name-displayed="isColorNameDisplayed"
                        :instalment-group-id="instalmentGroupId"
                        :show-get-quote-cta="showGetQuoteCta"
                        :show-continue-shopping-cta="showContinueShoppingCta"
                        :redirect-to-continue-shopping="redirectToContinueShopping"
                        :get-quote-url="getQuoteUrl"
                        :customer-is-logged-in="customerIsLoggedIn"
                        :continue-shopping-url="continueShoppingUrl"
                        :customer-login-url="customerLoginUrl"
                        :last-attribute-overlay-product-id="lastAttributeOverlayProductId"
                        :wishlist-items="wishlistItems"
                        v-if="!product.type"
                    ></app-product-pod>
                    <app-widget-pod
                        :title="product.short_description"
                        :product-url="product.url"
                        :monthly-price="product.monthlyPrice"
                        :images="product.images"
                        :rockar-price="product.price"
                        :lead-time="product.leadTime"
                        :update-you-build-step-url="options.updateYouBuildStepUrl"
                        v-if="product.type">
                    </app-widget-pod>
                </li>
            </ul>
            <div v-if="productsList.page.nextPageUrl && gridEnabled">
                <div id="item-grid-ajax-loader" class="ig-preloader" v-show="ajaxLoading">&nbsp;</div>
            </div>
        </template>
    </div>
</template>
<script>
    import appProductGrid from 'dsp2/components/CarFinder/ProductGrid';
    import appProductPod from 'mini2/components/CarFinder/ProductPod';
    import appResultsFilter from 'mini2/components/CarFinder/ResultsFilter';

    export default appProductGrid.extend({
        components: {
            appProductPod,
            appResultsFilter
        }
    });
</script>
