<template>
    <div v-show="$parent.steps[$parent.currentStep] === 'carFilter'"
         class="category-products col-9 col-md-8"
         v-el:category-products
    >
        <template v-if="$parent.steps[$parent.currentStep] === 'carFilter'">
            <p class="note-msg" v-if="productsListEmptyMessage && gridEnabled">{{ 'There are no products matching the selection.' | translate }}</p>
            <p class="note-msg" v-if="!gridEnabled">{{ 'Please select preferred payment option.' | translate }}</p>
            <div class="general-preloader" v-show="ajaxGlobalLoading"><div class="show-loading"></div></div>

            <slot></slot>

            <div class="car-filter-count mobile-borders">
                <span class="car-filter-count-heading">{{ $parent.CarFilter.getCarListProductCount() }}</span>
                <div class="car-filter-count-info-container">
                    <span class="car-filter-count-message">{{ 'Vehicle matching your search.' | translate }} </span>
                    <span class="car-filter-promotions-sub-message" v-if="$parent.CarFilter.getShortestLeadTime()">
                        {{ 'Delivery' | translate }} {{ $parent.CarFilter.getShortestLeadTimeMessage() }}
                    </span>
                </div>
            </div>

            <div class="car-filter-count mobile-borders" v-if="$parent.CarFilter.getCarListProductCountYouDrive() > 0">
                <span class="car-filter-count-heading">{{ $parent.CarFilter.getCarListProductCountYouDrive() }}</span>
                <div class="car-filter-count-info-container">
                    <span class="car-filter-count-message">{{ 'Matching Ex-Demos' | translate }} </span>
                    <span class="car-filter-promotions-sub-message" v-if="$parent.CarFilter.getShortestLeadTimeYouDrive()">
                    {{ 'Delivery' | translate }} {{ $parent.CarFilter.getShortestLeadTimeYouDriveMessage() }}</span>
                </div>
                <template v-for="section in $parent.CarFilter.getMenuFilters()":key="key">
                    <template v-if="section.code === 'visible_in'">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="toggle-car-filter" :checked="$parent.CarFilter.isYouDriveFilterChecked" @click="$parent.CarFilter.showYouDriveCars(section); ">
                            <label class="checkbox-filter" for="toggle-car-filter"><span class="checkbox-filter-demonstrators"></span></label>
                            <span class="checkbox-text">{{ 'Show' | translate }}</span>
                        </div>
                    </template>
                </template>
            </div>

            <div class="mobile-only category-products-filter-menu" @click="openMobileMenu()">
                <div class="text-wrapper">
                    <div class="filter-icon"></div>
                    <div class="filter-message">{{ 'Filters' | translate }}</div>
                </div>
            </div>

            <app-you-build-action-block
                    v-if="isEnabledYouBuildBlock"
                    :you-build-url="youBuildUrl"
                    :model-attribute="modelAttribute"
                    :you-build-products-count="youBuildProductsCount"
            >
                <template slot="message">{{ youBuildBlockMessage}}</template>
            </app-you-build-action-block>

            <div class="filtering-sort">
                <div class="applied-filters" v-if="appliedFilters.length > 0">
                    <ul>
                        <li><span v-if="isFilterVisible">{{ 'Filtering by:' | translate }}</span></li>
                        <li v-for="(index, filter) in appliedFilters">
                            <span>{{ filter.label }}</span>
                            <a href="javascript:void(0)" class="remove-filter" @click="removeFilter(filter)">(X)</a>
                            <span v-if="(appliedFilters.length !== index + 1)">&#44;&nbsp;</span> <!-- separator-->
                        </li>
                    </ul>
                </div>
                <div class="products-sort">
                    <app-sort
                        :sort-data-save-url="options.sortDataSaveUrl"
                        :sort-direction="options.sortDirection"
                    ></app-sort>
                </div>
            </div>

            <ul class="category-products-list" v-if="gridEnabled">
                <li v-for="product in productsList.products" :track-by="product.productId" :key="product.productId">
                    <app-product-pod
                        :product-index="$index"
                        :product-id="product.productId"
                        :sku="product.sku"
                        :product-url="product.url"
                        :title="product.title"
                        :subtitle="product.subtitle"
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
                        :offer-tag-data="product.offerTagData"
                        :finance-url="options.financeUrl"
                        :finance-params="financeParams"
                        :finance-slider-steps="financeSliderSteps"
                        :pay-in-full-payment="payInFullPayment"
                        :hire-payments="hirePayments"
                        :active-payment="activePayment"
                        :payment-save-url="options.paymentSaveUrl"
                        :compare="compare"
                        :compare-add-url="product.compareAddUrl"
                        :is-in-compare-list="product.isInCompareList"
                        :customer-email="options.customerEmail"
                        :customer-name="options.customerName"
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
                        :get-quote-url="getQuoteUrl"
                        :show-continue-shopping-cta="showContinueShoppingCta"
                        :customer-is-logged-in="customerIsLoggedIn"
                        :continue-shopping-url="continueShoppingUrl"
                        :customer-login-url="customerLoginUrl"
                        v-if="!product.type"
                    ></app-product-pod>
                    <app-widget-pod
                        :title="product.title"
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
    import appProductGrid from 'core/components/CarFinder/ProductGrid';
    import appProductPod from 'motorrad/components/CarFinder/ProductPod';
    import appWidgetPod from 'motorrad/components/CarFinder/WidgetPod'
    import appYouBuildActionBlock from 'motorrad/components/CarFinder/YouBuildActionBlock';

    export default appProductGrid.extend({
        props: {
            payInFullPayment: {
                required: true,
                type: Array
            },
            instalmentGroupId: {
                required: false,
                type: Number
            },

            /**
             * @property {Boolean} showGetQuoteCta
             */
            showGetQuoteCta: {
                type: Boolean,
                required: false,
                default: false
            },

            /**
             * @property {String} getQuoteUrl
             */
            getQuoteUrl: {
                type: String,
                required: false,
                default: '/sales/quote/getQuote'
            },

            /**
             * @property {Boolean} showContinueShoppingCta
             */
            showContinueShoppingCta: {
                type: Boolean,
                required: false,
                default: false
            },

            /**
             * @property {Boolean} customerIsLoggedIn
             */
            customerIsLoggedIn: {
                type: Boolean,
                required: false,
                default: false
            },

            /**
             * @property {String} continueShoppingUrl
             */
            continueShoppingUrl: {
                type: String,
                required: false,
                default: '/car-finder'
            },

            /**
             * @property {String} customerLoginUrl
             */
            customerLoginUrl: {
                type: String,
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
        },

        events: {
            'ProductGrid::balloonPercentageChange'() {
                const bpPercentage = this.$store.state.general.balloonPercentage,
                    instalmentId = this.instalmentGroupId;

                if (!isNaN(bpPercentage)
                    && instalmentId === this.activePayment.group_id) {
                    this.financeParams[instalmentId].balloonPercentage = bpPercentage;
                }
            }
        },

        data() {
            return {
                filtersToHide: ['visible_in']
            }
        },

        computed: {
            appliedFilters() {
                return this.carFilters.filter(filter => filter.hasSelected && !this.filtersToHide.includes(filter.code));
            }
        },

        methods: {
            sendProductImpressions(productsArray) {
                if (productsArray.length) {
                    let currPos = this.productsList.products.length - productsArray.length + 1;
                    const impressions = [];
                    productsArray.forEach((product, key) => {
                        impressions.push({
                            'name': product.title,
                            'id': product.sku,
                            'price': product.price,
                            'brand': this.brand,
                            'category': this.currentCarCategory,
                            'variant': product.subtitle,
                            'list': this.currentCarCategory,
                            'position': currPos++
                        });
                    });
                    pushEcommerceTags({
                        'event': 'productImpression',
                        'ecommerce': {
                            'currencyCode': 'ZAR',
                            impressions
                        }
                    });
                }
            }
        },

        components: {
            appProductPod,
            appYouBuildActionBlock,
            appWidgetPod
        }
    });
</script>
