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
                <app-offer-tags v-if="offerTagData"
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
                <div class="image-type-switcher" v-if="showImageSwitcher">
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
                     @click="attributeOverlayToggle">
                    {{ 'More Specifications' | translate }}
                </button>
                <button class="pod-specs-button mobile"
                    :class="{'active': attributeOverlay}"
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
                            {{ viewVehicleText }}
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
    import appGetQuoteCta from 'dsp2/components/Elements/GetQuoteCta';
    import appTooltip from 'core/components/Elements/Tooltip';
    import appModal from 'dsp2/components/Elements/Modal';
    import appFinanceOverlay from 'dsp2/components/FinanceOverlay';
    import appCarousel from 'core/components/CarFinder/Carousel';
    import appCarCompare from 'dsp2/components/CarFinder/CarCompare';
    import appSaveCar from 'dsp2/components/SaveCar';
    import appAttributes from 'dsp2/components/Elements/Attributes';
    import appPodSummarySpecs from 'dsp2/components/Elements/PodSummarySpecs';
    import appOfferTags from 'dsp2/components/Elements/OfferTags';
    import translateString from 'core/filters/Translate';
    import appProductPodOverlay from 'dsp2/components/CarFinder/ProductPodOverlay';
    import EventTrackerFinanceOverlay from 'dsp2/mixins/EventTrackerFinanceOverlay';
    import EventTracker from 'dsp2/mixins/EventTracker';

    export default Vue.extend({
        mixins: [EventTrackerFinanceOverlay, EventTracker],

        components: {
            appGetQuoteCta,
            appFinanceOverlay,
            appCarousel,
            appCarCompare,
            appSaveCar,
            appModal,
            appAttributes,
            appPodSummarySpecs,
            appOfferTags,
            appProductPodOverlay
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
                type: String,
                default: ''
            },
            subtitle: {
                required: false,
                type: String,
                default: ''
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
            removeFromWishlistUrl: {
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
            compareRemoveUrl: {
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
            offerTagData: {
                required: false,
                type: Object
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

            activePaymentName: {
                type: String,
                required: true
            },

            lastAttributeOverlayProductId: {
                type: Number,
                required: true
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
                podImagesType: 'default exterior', // or 'interior'
                attributeType: 'Extra Options',
                carOptionsVisible: true, // enable/disable 'interior' and 'default exterior' switchers for car pod specifications
                sessionError: false
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

            podExteriorDefaultImage() {
                return this.images.filter(item => item.title.toLowerCase() === 'default exterior')[0].images;
            },

            podInteriorImage() {
                return this.images.filter(item => item.title.toLowerCase() === 'interior')[0].images;
            },

            podExteriorInteriorImages() {
                return this.images.filter(item => item.title.toLowerCase() !== 'default exterior');
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
            },

            priceNote() {
                return `*${this.translateString('Based on')} ${this.activePaymentName}`;
            },

            /**
             * Return demo vehicle mileage
             * @return {String}
             */
            vehicleMileage() {
                const { km: { value = false } = {} } = this.carAttributes;

                return value;
            },

            showImageSwitcher() {
                return true;
            },

            viewVehicleText() {
                return this.translateString('View Vehicle');
            }
        },

        methods: {
            showFinanceOverlay() {
                this.financeOverlay = true;
                this.setFinanceGroup(this.financeGroupId);
            },

            closeFinanceOverlay() {
                this.financeOverlay = false;
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
                if (this.podImagesType !== type) {
                    this.podImagesType = type;
                    this.$nextTick(() => {
                        this.$broadcast('Carousel::reInit');
                    });
                }
            },

            setAttributeType(type) {
                const { title } = type || {}

                this.attributeType = title || type;
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
                this.$broadcast('CarCompare::triggerAddRemove');
                sessionStorage.setItem('url', window.location.href);
                if (!this.isInCompareList) {
                    this.fireEventForTracking(
                        this.getEventConstants().PAGEDESCRIPTION.TRIGGERS,
                        `${this.getEventConstants().TRIGGERTRACKERVALUES.ADDTOCOMPARE}${this.title} ${this.subtitle}`
                    );
                }
            },

            /**
             * Closing and opening the popup and redirects if not logged in
             */
            toggleSaveCarOverlay() {
                if (this.customerName.toLowerCase() === 'guest') {
                    this.saveCarOverlay = !this.saveCarOverlay;
                }

                if (this.customerName.toLowerCase() !== 'guest' && this.isInWishlist === false) {
                    this.$dispatch('CarFinder::ajaxLoad', true);
                    this.$broadcast('SaveCar::addToWishlist');
                }

                if (this.customerName.toLowerCase() !== 'guest' && this.isInWishlist === true) {
                    this.saveCarOverlay = !this.saveCarOverlay;
                    this.$broadcast('SaveCar::removeFromWishlist');
                }
            },

            attributeOverlayToggle() {
                this.attributeOverlay = !this.attributeOverlay;

                if (this.attributeOverlay) {
                    this.$dispatch('ProductPod::attributeOverlayToggle', this.productId);
                }
            },

            toggleIsInWishlist(value) {
                this.isInWishlist = value;
            },

            openSaveCarOverlay() {
                this.saveCarOverlay = true;
                this.$dispatch('CarFinder::ajaxLoad', false);
            },

            openCarImage(mouseEvent) {
                this.$dispatch('ProductPod::selectCar', mouseEvent);
            },

            translateString
        },

        ready() {
            this.getFinanceParams();

            EventsBus.$on('ProductPod::closeAttributeOverlay', () => {
                this.attributeOverlay = false;
            });
        },

        events: {
            'ProductPod::selectCar'(mouseEvent) {
                this.openCar(mouseEvent);
            },

            'ProductPodOverlay::attributeOverlayToggle'() {
                this.attributeOverlayToggle();
            },

            'ProductPodOverlay::setAttributeType'(data) {
                this.setAttributeType(data)
            },

            'Product::toggleInCompareList'(sku) {
                if (this.sku === sku) {
                    this.isInCompareList = !this.isInCompareList;
                }
            },

            'FinanceOverlay::closeFinanceOverlay'() {
                this.closeFinanceOverlay();
            },

            'ProductPod::onDataFail'() {
                this.sessionError = true;
            },

            'ProductPod::onDataSuccess'() {
                this.sessionError = false;
            },

            'Product::updateWishlistProp'(data) {
                if (data.productId === this.productId) {
                    this.isInWishlist = data.isInWishlist;
                }
            },

            'Product::clearCompare'() {
                this.isInCompareList = false;
            }
        }
    });
</script>
