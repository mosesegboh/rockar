<template>
    <div class="category-products-list-item product-pod" transition="opacity" :data-car-id="productId">

        <div class="pod-caption">
            <div class="caption">
                <div class="title">
                    <a v-on:mouseup="openCar">{{ title }}</a>
                </div>
                <div class="subtitle">
                    <p>{{ subtitle }}<br><span v-if="isProductColorNameDisplayed">{{ productColorName }}</span></p>
                </div>
            </div>
            <div class="pod-overlay-wrap">
                <div
                    class="pod-overlay save-overlay"
                    :class="{'active': saveCarOverlay, 'added-to-wishlist': isInWishlist}"
                    @click="toggleSaveCarOverlay"
                >
                    <p>{{ 'Wishlist' | translate }}</p>
                </div>
                <div class="pod-finance-overlay" @click="showFinanceOverlay">
                    <p>{{ 'Finance calculator' | translate }}</p>
                </div>
            </div>
        </div>
        <app-attributes
            :car-attributes="carAttributes">
        </app-attributes>
        <app-offer-tags v-if="offerTagData"
            class="mobile"
            :offer-tag-data="offerTagData">
        </app-offer-tags>

        <div class="pod-content">
            <div class="pod-images" :class="{'attributes-visible': attributeOverlay}">
                    <div class="product-pod-attributes-overlay" v-show="attributeOverlay">
                        <div class="attribute-close" @click="attributeOverlay = false"></div>
                        <div class="attribute-view-switcher" v-show="carOptionsVisible">
                            <template v-for="(section, total) in customOptionsTotal">
                                <button v-if="total !== false" class="view-switch"
                                      :class="{'active': attributeType === section}"
                                      @click="setAttributeType(section)"
                                >{{ section | translate }}
                                </button>
                            </template>
                        </div>
                        <div class="attribute-block">
                            <div class="attribute-section" v-el:attributes-list>
                                <ul class="feature-list" v-show="computedKeyFeatures.length">
                                    <li v-for="keyFeature in computedKeyFeatures" track-by="$index" class="attribute">
                                        <div class="attribute-label">{{ keyFeature.key }}</div>
                                        <div class="attribute-value" v-if="keyFeature.value">{{ keyFeature.value }}</div>
                                    </li>
                                </ul>
                                <ul class="attribute-list">
                                    <li v-for="option in customOptions">
                                       <div v-if="option['product_id'] == productId">
                                           <div v-if="option['option_type'] == attributeType">
                                               <div class="row">
                                                   <div class="option-title" v-html="option.title"></div>
                                                   <div class="option-price">{{option['price'] | numberFormat '0,0.00' true}}</div>
                                               </div>
                                           </div>
                                       </div>
                                    </li>
                                </ul>
                                <div class="row option-total-price">
                                    <div class="option-title">{{ 'Total specification summary' | translate }}</div>
                                    <div class="option-price">{{ customOptionsTotal[attributeType] | numberFormat '0,0.00' true }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="image-type-switcher">
                        <div class="actions-wrapper">
                            <button class="button button-narrow button-gray-light-2"
                                    :class="{'active': podImagesType === 'exterior'}"
                                    @click="setPodImagesType('exterior')"
                            >{{ 'Exterior' | translate }}</button>
                            <button class="button button-narrow button-gray-light-2"
                                    :class="{'active': podImagesType === 'interior'}"
                                    @click="setPodImagesType('interior')"
                            >{{ 'Interior' | translate }}</button>
                        </div>
                    </div>
                    <div class="carousel">
                        <!-- Desktop carousels -->
                    <a>
                        <app-carousel
                                v-el:exterior-images
                                v-show="podImagesType === 'exterior'"
                                :slides="podExteriorSliderImages"
                                :options="{
                                slidesToShow: 1,
                                initialSlide: 0,
                                centerMode: false,
                                mobileNavigation: 1,
                                dots: true,
                                lazyLoad: 'ondemand'
                            }"
                                :checkboxes="false"
                        ></app-carousel>

                        <app-carousel
                                v-el:interior-images
                                v-show="podImagesType === 'interior'"
                                :slides="podInteriorSliderImages"
                                :options="{
                                slidesToShow: 1,
                                initialSlide: 0,
                                centerMode: false,
                                mobileNavigation: 1,
                                dots: true,
                                lazyLoad: 'ondemand'
                            }"
                                :checkboxes="false"
                        ></app-carousel>

                        <!-- Mobile carousel -->
                        <app-carousel
                                class="mobile-slider"
                                :slides="podMobileSliderImages"
                                :options="{
                                slidesToShow: 1,
                                initialSlide: 0,
                                centerMode: false,
                                mobileNavigation: 1,
                                dots: false,
                                lazyLoad: 'ondemand'
                            }"
                                :checkboxes="false"
                        ></app-carousel>
                    </a>
                </div>
            </div>
            <div class="pod-data" v-el:pod-data>
                <div class="product-prices">
                    <div class="item-details-price-block">
                        <div class="item-details-price rockar-price" v-if="!isHire"
                             :class="{'offertag-wrapper': offerTagData.action_type === 'icon_text' && cashDeposit >= 0 && monthlyPrice > 0,
                             'offertag-wrapper-icon': offerTagData.action_type === 'icon' && cashDeposit < 0 && monthlyPrice < 0}">
                            <div class="price-wrapper">
                                <span class="price">{{ rockarPrice | numberFormat '0,0' true }}</span>
                                <span class="label">{{ 'Offer Price' | translate }}</span>
                            </div>
                        </div>

                        <div class="item-details-price initial-payment" v-else>
                            <div class="price-wrapper">
                                <span class="price">{{ cashDeposit | numberFormat '0,0' true }}</span>
                                <span class="label">{{ 'Initial Payment' | translate }}</span>
                            </div>
                        </div>

                        <div class="item-details-price cash-back" v-if="cashback > 0 && isHire">
                            <div class="price-wrapper">
                                <span class="price">{{ cashback | numberFormat '0,0' true }}</span>
                                <span class="label">{{ 'Cash Back' | translate }}</span>
                            </div>
                        </div>

                        <div class="item-details-price monthly-price" v-if="cashDeposit >= 0 && monthlyPrice > 0"
                             :class="{'offertag-wrapper': offerTagData}">
                            <div class="price-wrapper">
                                <span class="price">{{ Math.round(monthlyPrice) | numberFormat '0,0' true }}</span>
                                <span class="label">{{ 'A Month' | translate }}</span>
                            </div>
                        </div>

                        <div class="item-details-price off-rrp" v-if="!isHire && saveOffRrp > 0">
                            <div class="price-wrapper">
                                <span class="price">{{ saveOffRrp | numberFormat '0,0' true }}</span>
                                <span class="label">{{ 'Saving off RRP' | translate }}</span>
                            </div>
                        </div>

                        <div class="item-details" v-if="showGetQuoteCta">
                            <div class="price-wrapper">
                                <span>
                                    <span>{{ 'Delivery within' | translate }}</span>
                                    <span>{{ leadTimeRounded }}</span>
                                    <span>{{ `week${leadTimeRounded == 1 ? '' : 's'}` | translate }}</span>
                                </span>
                            </div>
                        </div>

                        <app-offer-tags v-if="offerTagData"
                            class="desktop"
                            :offer-tag-data="offerTagData">
                        </app-offer-tags>
                    </div>
                </div>

                <div class="pod-actions">
                    <div class="row">
                        <div class="pod-attribute attr-features"
                             :class="{'active': attributeOverlay}"
                             @click="attributeOverlay = !attributeOverlay">
                            <p>{{ 'Specification' | translate }}</p>
                        </div>

                        <div class="pod-attribute attr-compare" @click="saveUrl()">
                            <p>{{ 'Compare' | translate }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="pod-attribute get-quote" v-if="showGetQuoteCta">
                            <app-get-quote-cta
                                :product-id="productId"
                                :customer-email="customerEmail"
                                :quote-name="quoteTitle"
                                :my-account-url="myAccountUrl"
                                :get-quote-url="getQuoteUrl"
                                :show-continue-shopping-cta="showContinueShoppingCta"
                                :customer-is-logged-in="customerIsLoggedIn"
                                :continue-shopping-url="continueShoppingUrl"
                                :customer-login-url="customerLoginUrl"
                            ></app-get-quote-cta>
                        </div>
                        <div class="pod-attribute attr-day" v-if="!showGetQuoteCta">
                            <span>{{ 'Delivery' | translate }}</span>
                            <span class="attr-day-day">{{ leadTimeRounded }}</span>
                            <span v-if="leadTimeRounded == 1" class="attr-day-label">{{ 'week' | translate }}</span>
                            <span v-else class="attr-day-label">{{ 'weeks' | translate }}</span>
                        </div>

                        <div class="pod-attribute attr-buy" @click="openCar">
                            <p>{{ 'Buy now' | translate }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <app-save-car
                :open="saveCarOverlay"
                :customer-name="customerName"
                :product-name="title"
                :product-title="title"
                :product-subtitle="subtitle"
                :product-sku="sku"
                :save-wishlist-url="saveWishlistUrl"
                :my-account-url="myAccountUrl"
                :is-in-wishlist="isInWishlist"
                :is-ajax-request="isAjaxRequest"

                @close-save="saveCarOverlay = false">
        </app-save-car>

        <app-car-compare
                :open="carCompareOverlay"
                :compare="compare"
                :compare-add-url="compareAddUrl"
                :is-in-compare-list="isInCompareList"

                @close-compare="carCompareOverlay = false">
        </app-car-compare>

        <app-modal :show.sync="financeOverlay" v-if="financeOverlay" class="finance-popup">
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
                        calc-type="carfinder">
                </app-finance-overlay>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import appGetQuoteCta from 'mini/components/Elements/GetQuoteCta';
    import appTooltip from 'core/components/Elements/Tooltip';
    import appModal from 'core/components/Elements/Modal';
    import appFinanceOverlay from 'mini/components/FinanceOverlay';
    import appCarousel from 'core/components/CarFinder/Carousel';
    import appCarCompare from 'mini/components/CarFinder/CarCompare';
    import appSaveCar from 'mini/components/CarFinder/SaveCar';
    import appAttributes from 'mini/components/Elements/Attributes';
    import appOfferTags from 'mini/components/Elements/OfferTags';

    import perfectScrollbar from 'perfect-scrollbar';

    export default Vue.extend({
        components: {
            appGetQuoteCta,
            appFinanceOverlay,
            appCarousel,
            appCarCompare,
            appSaveCar,
            appModal,
            appAttributes,
            appOfferTags
        },

        props: {
            customOptions: {
                required: false,
                type: Array
            },
            productIndex: {
                required: true,
                type: Number
            },
            productId: {
                required: false,
                type: Number,
                default: -1
            },
            productUrl: {
                required: true,
                type: String
            },
            rockarPrice: {
                required: true,
                type: Number
            },
            monthlyPrice: {
                required: true,
                type: Number
            },
            cashDeposit: {
                required: true,
                type: Number
            },
            cashback: {
                required: true,
                type: Number
            },
            isHire: {
                required: true,
                type: Number
            },
            displayAnnual: {
                required: true,
                type: Boolean,
                default: false
            },
            saveOffRrp: {
                required: false,
                type: Number,
                default: 0
            },
            title: {
                required: false,
                type: String
            },
            subtitle: {
                required: false,
                type: String
            },
            tooltip: {
                required: false,
                type: String,
                default: null
            },
            carAttributes: {
                required: true,
                type: Object
            },
            keyFeatures: {
                required: false,
                type: Array
            },
            carData: {
                required: false,
                type: Object
            },
            leadTime: {
                required: true,
                type: Number
            },
            overlayText: {
                required: false,
                type: String,
                default: ''
            },
            customerName: {
                required: false,
                type: String,
                default: 'Guest'
            },
            customerEmail: {
                required: false,
                type: [String, Boolean],
                default: false
            },
            myAccountUrl: {
                required: false,
                type: String
            },
            isInWishlist: {
                required: false,
                type: Boolean,
                default: false
            },
            saveWishlistUrl: {
                required: true,
                type: String
            },
            isAjaxRequest: {
                required: true,
                type: Boolean,
                default: false
            },
            financeUrl: {
                required: true,
                type: String
            },
            financeParams: {
                required: true,
                type: Object
            },
            financeSliderSteps: {
                required: true,
                type: Object
            },
            payInFullPayment: {
                required: true,
                type: Array
            },
            hirePayments: {
                required: true,
                type: Array
            },
            activePayment: {
                required: true,
                type: Object
            },
            paymentSaveUrl: {
                required: false,
                type: String
            },
            compare: {
                required: true,
                type: Object
            },
            compareAddUrl: {
                required: true,
                type: String
            },
            isInCompareList: {
                required: true,
                type: Boolean,
                default: false
            },
            optionalFinalPayment: {
                required: true,
                type: Number
            },
            images: {
                required: true,
                type: Array
            },
            sku: {
                required: false,
                type: String,
                default: ''
            },
            productColorName: {
                required: false,
                type: String
            },
            isProductColorNameDisplayed: {
                required: true,
                type: Boolean,
                default: false
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

            offerTagData: {
                required: false,
                type: Object
            }
        },

        data() {
            return {
                paymentType: this.cashDeposit,
                show: true,
                toggleSection: -1,
                carAttributesList: [],
                financeOverlay: false,
                togglePodMenu: false,
                toggleAnnualCostMenu: false,
                attributeOverlay: false,
                saveCarOverlay: false,
                carCompareOverlay: false,
                podItemsMapping: {
                    luggage_capacity: {
                        class: 'attr-bags',
                        label: 'Luggage Capacity'
                    },
                    economy_combined: {
                        class: 'attr-mpg',
                        label: 'Mpg'
                    },
                    ec_combined_mpg: {
                        class: 'attr-ec-mpg',
                        label: 'EC-MPG'
                    },
                    warranty: {
                        class: 'attr-warranty',
                        label: 'Warranty'
                    },
                    seating_capacity: {
                        class: 'attr-seats',
                        label: 'Seats'
                    },
                    co2_emission: {
                        class: 'attr-tax',
                        label: 'CO2'
                    },
                    insurance_group: {
                        class: 'attr-insurance',
                        label: 'Insurance Group'
                    },
                    seats: {
                        class: 'attr-seats',
                        label: 'Seats'
                    },
                    emission: {
                        class: 'attr-emission',
                        label: 'Emission'
                    }
                },
                podImagesType: 'exterior', // or 'interior'
                attributeType: 'Exterior',
                carOptionsVisible: true // enable/disable 'interior' and 'exterior' switchers for car pod specifications
            }
        },

        computed: {
            productsList() {
                return this.$root.$refs.productGrid.productsList.products;
            },

            lessAnnual() {
                return this.productsList[this.productIndex].lessAnnual;
            },

            lessAnnualABS() {
                return Math.abs(this.lessAnnual);
            },

            leadTimeRounded() {
                return Math.ceil(this.leadTime);
            },

            computedKeyFeatures() {
                const keyFeatures = [];

                this.keyFeatures.forEach((keyFeature) => {
                    const feature = keyFeature.split(': ');
                    keyFeatures.push({
                        key: feature[0],
                        value: feature[1]
                    });
                });

                return keyFeatures;
            },

            /**
             * Mobile slider, get 1 image of each type
             *
             * @returns {Array}
             */
            podMobileSliderImages() {
                const images = [];

                images.push(this.podExteriorSliderImages[0]);
                images.push(this.podInteriorSliderImages[0]);

                return images;
            },

            podExteriorSliderImages() {
                return this.images.filter(item => item.title.toLowerCase() === 'exterior')[0].images;
            },

            podInteriorSliderImages() {
                return this.images.filter(item => item.title.toLowerCase() === 'interior')[0].images;
            },

            currentCategory() {
                return this.$store.state.general.currentCategory;
            },

            brand() {
                return this.$store.state.general.brand;
            },

            customOptionsTotal() {
                return this.customOptions && this.customOptions.length ? this.customOptions.reverse()[0] : 0;
            },

            financeGroupId() {
                return this.$store.state.finance.financeGroupId;
            },

            allowedMonthlyPayments() {
                const financeFilter = this.$root.$refs.financeFilter;
                // If there is no finance filter then allow data to show
                if (!financeFilter) {
                    return true;
                }
                const dissalowedOptions = [
                    'CASH'
                ];
                const paymentType = financeFilter.selectedFinanceGroupData.group_title;
                // If there is no payment type selected then allow data to show
                if (!paymentType) {
                    return true;
                }
                return !dissalowedOptions.includes(paymentType);
            },

            allowedRockarPrice() {
                const financeFilter = this.$root.$refs.financeFilter;
                // If there is no finance filter then allow data to show
                if (!financeFilter) {
                    return true;
                }
                const dissalowedOptions = [
                    'PCH',
                    'BCH'
                ];
                const paymentType = financeFilter.selectedFinanceGroupData.group_title;
                // If there is no payment type selected then allow data to show
                if (!paymentType) {
                    return true;
                }
                return !dissalowedOptions.includes(paymentType);
            },

            /**
             * Quote Title
             * @return {String}
             */
            quoteTitle() {
                return `${this.customerName}'s ${this.title}`;
            }
        },

        methods: {
            showFinanceOverlay() {
                this.financeOverlay = true;
            },

            openCar(mouseEvent) {
                this.sendProductView();

                if (mouseEvent.button === 0) {
                    // This is used to deal with bmw external login redirect, which result in incorrect Document.referrer value when going to PDP page.
                    sessionStorage.setItem('CarFinder::redirectToPdp', window.location.href);
                    window.location.href = this.productUrl;
                } else if (mouseEvent.button === 1) {
                    window.open(this.productUrl, '_blank');
                    window.focus();
                }
            },

            setPodImagesType(type) {
                this.podImagesType = type;
                this.$nextTick(() => {
                    this.$broadcast('Carousel::reInit');
                });
            },

            setAttributeType(type) {
                this.attributeType = type;
            },

            sendProductView() {
                const productClickObject = {
                    'event': 'productClick',
                    'ecommerce': {
                        'click': {
                            'actionField': { 'list': this.currentCategory },
                            'products': [{
                                'name': this.title,
                                'id': this.sku,
                                'price': this.rockarPrice,
                                'brand': this.brand,
                                'category': this.currentCategory,
                                'variant': this.subtitle,
                                'position': this.productIndex + 1
                            }]
                        }
                    }
                };

                pushEcommerceTags(productClickObject);
            },

            getFinanceParams() {
                if (!this.$root.$refs.financeFilter || !this.$root.$refs.financeFilter.activeFinanceGroupId) {
                    return undefined;
                }
                // Translate the data from one object into another one that uses different keys
                const keysTranslate = {
                    'mileage': 'annualMileage',
                    'term': 'payOver',
                    'deposit': 'initialPay',
                    'depositMultiple': 'depositMultiple'
                };
                Object.keys(keysTranslate).forEach(key => {
                    this.financeParams[this.$root.$refs.financeFilter.activeFinanceGroupId][key] = this.$root.$refs.financeFilter[keysTranslate[key]]
                });
            },

            saveUrl() {
                this.carCompareOverlay = true;
                sessionStorage.setItem('url', window.location.href);
            },

            /**
             * Closing and opening the popup and redirects if not logged in
             */
            toggleSaveCarOverlay() {
                if (this.customerName.toLowerCase() === 'guest') {
                    this.saveCarOverlay = false;
                    window.location.href = this.myAccountUrl;
                } else {
                    this.saveCarOverlay = !this.saveCarOverlay;
                }
            }
        },

        ready() {
            perfectScrollbar.initialize(
                this.$els.attributesList,
                {
                    suppressScrollX: true,
                    wheelPropagation: true
                }
            );
            this.getFinanceParams();
        },

        events: {
            'ProductPod::selectCar'(mouseEvent) {
                this.openCar(mouseEvent);
            },

            'ProductPod::isInWishlist'(value) {
                this.isInWishlist = value;
            }
        }
    });
</script>
