<template>
    <div id="configurator">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <app-navigation v-show="currentDevice !== 'mobile'"
            @move-prev="stepBack"
            @move-next="moveToNextStep"
            @set-exterior="changeSlider('view360Exterior')"
            @set-interior="changeSlider('view360Interior')"

            :active-step="activeStep"
            :steps="steps"
            :is-exterior="sliderState.view360Exterior">
        </app-navigation>

        <div :class="['section-wrap', activeStep]">
            <div :class="['configurator-wrap', 'section', activeStep === 'details' ? 'active' : '']">
                <div class="car-hero">
                    <div class="image-zoom" @click="openImageZoom">
                        <span class="zoom-icon"></span>
                    </div>
                    <div class="image-type-switcher">
                        <div class="actions-wrapper row ext-int-buttons" v-if="currentDevice !== 'mobile'">
                            <button class="button button-narrow button-gray-light-2 col-3 shift-6 align-right"
                                :class="{'active': sliderState.view360Exterior}"
                                @click="changeSlider('view360Exterior')">
                                {{ 'Exterior' | translate }}
                            </button>
                            <button class="button button-narrow button-gray-light-2 col-3 align-right"
                                :class="{'active': !sliderState.view360Exterior}"
                                @click="changeSlider('view360Interior')">
                                {{ 'Interior' | translate }}
                            </button>
                        </div>
                    </div>
                    <div class="share-product-mobile">
                        <app-save-car
                            v-if="vehicle.isInWishlist !== undefined"
                            button-classes="button button-empty-dark"
                            :customer-name="product.customerName"
                            :product-name="product.title"
                            :product-title="product.name"
                            :product-subtitle="product.short_title"
                            :product-sku="product.sku"
                            :product-price="rockarPrice"
                            :save-wishlist-url="vehicle.saveWishlistUrl"
                            :my-account-url="product.myAccountUrl"
                            :is-in-wishlist="vehicle.isInWishlist"
                            :is-ajax-request="product.isWishlistAjax"
                            :show-as-link="false">
                        </app-save-car>
                    </div>

                    <div class="slide360" v-if="sliderState.view360Interior">
                        <app-configurator-carousel-images
                            :slides="intCarousel"
                            :options="imageSlidersConfig"
                        ></app-configurator-carousel-images>
                    </div>

                    <div class="slide360" v-if="sliderState.view360Exterior">
                        <app-configurator-carousel-images
                            :slides="extCarousel"
                            :options="imageSlidersConfig"
                        ></app-configurator-carousel-images>
                    </div>
                    <div v-if="zoomActiveImage">
                        <div class="popup image-zoom-popup">
                            <div class="popup-overlay"></div>
                            <div class="popup-container">
                                <div class="pdp-image-popup">
                                    <div class="image-type-switcher">
                                        <div class="popup-close-background">
                                            <div class="close-button popup-button-close popup-close" v-if="zoomActiveImage" @click="closeImageZoom" v-el:close-trigger></div>
                                        </div>
                                        <div class="actions-wrapper" v-if="currentDevice !== 'mobile'">
                                            <button class="button button-narrow button-gray-light-2"
                                                :class="{'active': sliderState.view360Exterior}"
                                                @click="changeSlider('view360Exterior')">
                                                {{ 'Exterior' | translate }}
                                            </button>
                                            <button class="button button-narrow button-gray-light-2"
                                                :class="{'active': !sliderState.view360Exterior}"
                                                @click="changeSlider('view360Interior')">
                                                {{ 'Interior' | translate }}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="slide360" v-if="sliderState.view360Interior">
                                        <app-configurator-carousel-images
                                            :slides="intCarousel"
                                            :options="imageSlidersConfig"
                                        ></app-configurator-carousel-images>
                                    </div>

                                    <div class="slide360" v-if="sliderState.view360Exterior">
                                        <app-configurator-carousel-images
                                            :slides="extCarousel"
                                            :options="imageSlidersConfig"
                                        ></app-configurator-carousel-images>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="image-type-switcher">
                        <div class="actions-wrapper actions-wrapper-mobile row">
                            <button class="button button-narrow button-gray-light-2 col-3 align-right"
                                    :class="{'active': sliderState.view360Exterior}"
                                    @click="changeSlider('view360Exterior')">
                                {{ 'Exterior' | translate }}
                            </button>
                            <button class="button button-narrow button-gray-light-2 col-3 align-right"
                                    :class="{'active': !sliderState.view360Exterior}"
                                    @click="changeSlider('view360Interior')">
                                {{ 'Interior' | translate }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="product-actions-mobile">
                    <app-product-top-container
                        :product="product"
                        :vehicles="this.saveCarVehicles()"
                        :options="options"
                        v-ref:product-top-container>
                        <div slot="productActions" class="vue-slot">
                            <slot name="productActions"></slot>
                        </div>
                    </app-product-top-container>
                </div>

                <app-top-finance-quote></app-top-finance-quote>

                <div class="sub-navigation desktop">
                    <button class="btn-back" @click="stepBack">
                        <span class="title">{{ 'Back to results' | translate }}</span>
                    </button>
                    <button
                        class="button-gray-light-2 btn-features btn-sub-navigation car-extras-tab btn-features"
                        :class="[isCarExtrasContentVisible ? 'car-extras-tab-active' : 'car-extras-tab-hover']"
                        @click="showOrHideCarExtrasContent">
                        <span class="title col-10">{{ 'Extras on this car' | translate }}</span>
                        <span class="view-more-arrow" :class="[isCurrentTabActive ? 'current-tab-caret-active' : '']"></span>
                    </button>
                    <button class="button-gray-light-2 btn-features btn-sub-navigation btn-standard-features" @click="clickStandardFeatureButton">
                        <span class="title">{{ 'Standard Features' | translate }}</span>
                    </button>
                    <button class="button-gray-light-2 btn-features btn-sub-navigation btn-technical-features" @click="clickTechnicalFeatureButton">
                        <span class="title">{{ 'Technical Features' | translate }}</span>
                    </button>
                </div>

                <div class="sub-navigation mobile">
                    <button class="btn-back" @click="stepBack"></button>
                    <button class="btn-continue" @click="advanceStep">
                        <span class="title">{{ 'Checkout' | translate }}</span>
                    </button>
                </div>

                <div class="configurator-lower-config">
                    <div class="car-extras-content desktop" v-if="isCarExtrasContentVisible" :car-data="carData">
                        <template v-for="(dataIndex, data) in carData">
                            <div v-if="data.group && data.items.length"
                                 :class="['finance-quote', 'grid', 'no-padding-top', 'no-padding-bottom', 'group-block']">
                                <div class="finance-quote-content">
                                    <table :class="['table', 'table-two']">
                                        <tbody class="row">
                                            <tr @click="toggleOptionGroupHandler(dataIndex)">
                                                <td>
                                                    <span class="group-title">{{ data.group }}</span>
                                                </td>
                                            </tr>
                                            <tr class="col-12" v-for="item in data.items">
                                                <td class="col-10">
                                                    <span :class="[!item.remove ? 'no-icon' : '']">{{ item.label | convertNCR }}</span>
                                                </td>
                                                <td class="col-2">
                                                    <span class="table-right">{{ !item.price ? '0.00' : item.price | numberFormat '0,0.00' true }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                        <table :class="['table', 'table-two']">
                            <tr class="col-12">
                                <td class="col-10">
                                    <span class="total-price-extras">Total Price</span>
                                </td>
                                <td class="col-2">
                                    <span class="table-right total-price-extras">{{ extrasTotal | numberFormat '0,0.00' true }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="color-slider" v-if="productType === 'configurable' && areColorsAvailable">
                        <div class="color-slider-container">
                            <app-carousel-color
                                @product-update="productUpdate"
                                @preconfigured-update="vehicleUpdate"
                                @update360="update360"
                                :slides="colors.slides"
                                :active="colors.active">
                            </app-carousel-color>
                        </div>
                    </div>

                <div class="configurator-mobile-actions-wrapper">
                    <button type="button" class="button button-empty finance-edit-button" @click="$dispatch('Main::showFinanceOverlay')">{{ 'Finance' | translate }}</button>
                    <div class="scroll-to-features-element-btns">
                        <button
                            :class="{'feature-button-active' : isStandardFeatureButtonActive, 'feature-btn-dropdown-enabled' : isTechFeatureButtonActive && isStandardFeatureButtonActive}"
                            type="button"
                            class="button button-gray-light-2 features-button"
                            @click="clickStandardFeatureButton">
                            {{ 'Standard Features ' | translate }}
                        </button>
                        <div class="technical-features-btn-wrapper">
                            <button
                                :class="{'feature-button-active' : isTechFeatureButtonActive, 'feature-btn-dropdown-enabled' : isTechFeatureButtonActive && isStandardFeatureButtonActive}"
                                type="button"
                                class="button button-gray-light-2 features-button"
                                @click="clickTechnicalFeatureButton">
                                {{ 'Technical Features' | translate }}
                            </button>
                        </div>
                    </div>
                </div>

                    <app-choose-vehicle-grid
                        :vehicles="vehicles"
                        :product="product"
                        :options="{choosePreConfiguredUrl: options.choosePreConfiguredUrl}"
                        :selected-car="selectedCar"
                        :part-exchange-notification="partExchangeNotification"
                        :ajax-loading.sync="ajaxLoading"
                        :product-type="productType"
                        v-show="productType === 'simple'"
                    ></app-choose-vehicle-grid>

                    <div id="my-build" class="my-build-technical-specifications my-build standard-features" v-if="standardFeatures || technicalSpecItems">
                        <div id="my-build-standard-specifications" class="my-build">
                            <p class="specification-title">{{ 'Standard features' | translate }}</p>
                            <app-accordion-group :open-first="false">
                                <div class="accordion-group">
                                    <app-accordion v-for="features in standardFeatures"
                                        :title="features.title"
                                        :scroll-on-show="false"
                                        class-name="accordion-light"
                                        type="right-down"
                                        :id="'standard_features_' + $index" track-by="$index">
                                        <li class="accordion-list standard-features">
                                            <div class="features-item" v-for="item in features.features" track-by="$index" v-html="item"></div>
                                        </li>
                                    </app-accordion>
                                </div>
                            </app-accordion-group>
                        </div>

                        <div id="my-build-technical-specifications" class="my-build technical-features">
                            <p class="specification-title">{{ 'Technical features' | translate }}</p>
                            <app-accordion-group>
                                <div class="accordion-group">
                                    <app-accordion v-for="section in technicalSpecItems"
                                        :title="section.title"
                                        :scroll-on-show="false"
                                        type="right-down"
                                        class-name="accordion-light"
                                        :id="'technical_spec_items_' + $index" track-by="$index">
                                        <li class="accordion-list" v-if="section.type === 'table'">
                                            <div class="technical-features">
                                                <div class="features-item section-subtitle" v-html="section.subtitle"></div>
                                                <div class="features-item" v-for="item in section.items" track-by="$index">
                                                    <div class="label" v-html="item.title"></div>
                                                    <div class="value" v-html="item.value"></div>
                                                </div>
                                            </div>
                                        </li>

                                        <li class="accordion-list" v-else>
                                            <div class="accordion-content">
                                                <div class="my-build-item-list">
                                                    <div class="my-build-item-block" :class="{'active': activeIndex === $index}" v-for="part in section.parts" @click="activeIndex = $index">
                                                        <div class="item-block-cover col-3">
                                                            <img :src="part.image" :alt="part.title" />
                                                        </div>
                                                        <div class="item-block-info col-9">
                                                            <h3>{{ part.title }}</h3>
                                                            <p>{{ part.price | numberFormat '0,0.00' true }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </app-accordion>
                                </div>
                            </app-accordion-group>
                        </div>
                    </div>
                </div>
            </div>

            <app-accessories
                :accessories="accessories"
                :add-accessories-url="addAccessoriesUrl"
                :product="product"
                :active-color="colors.active"
                :class-name="activeStep === 'accessories' ? 'active' : ''"
            ></app-accessories>

            <div :class="[
                'finance-quote-mobile-container',
                activeStep === 'details' ? 'finance-quote-mobile-container-details' : ''
            ]"></div>

            <div class="sub-navigation mobile">
                <button class="btn-back" @click="stepBack"></button>
                <button class="btn-continue" @click="advanceStep">
                    <span class="title">{{ getCheckoutCTAName() | translate }}</span>
                </button>
            </div>
        </div>

        <app-experience-popup
            class-name="experience-popup"
            :experience-popup-data="experiencePopupData">
        </app-experience-popup>
    </div>
</template>

<script>
import coreConfigurator from 'core/components/Configurator/Configurator';
import appSaveCar from 'mini/components/SaveCar';
import appChooseVehicleGrid from 'mini/components/Configurator/ChooseVehicleGrid';
import appTopFinanceQuote from 'mini/components/Configurator/TopFinanceQuote';
import appAccessories from 'mini/components/Configurator/Accessories';
import appNavigation from 'mini/components/Configurator/Navigation';
import UrlParser from 'mini/mixins/UrlParser';
import appProductTopContainer from 'mini/components/Configurator/ProductTopContainer';
import appExperiencePopup from 'mini/components/Configurator/ExperiencePopup';

export default coreConfigurator.extend({
    mixins: [UrlParser],

    props: {
        experiencePopupData: {
            required: true,
            type: Object
        }
    },

    data() {
        return {
            areColorsAvailable: false, // It will disable color container until functionality will not be implemented
            activeStep: 'details',
            isMobile: false,
            fullWidth: 0,
            imageSlidersConfig: {
                speed: 1,
            },
        }
    },

    computed: {
        currentDevice() {
            return this.$store.state.general.device;
        },
    },

    beforeCreate() {
        this.fullWidth = document.documentElement.clientWidth
    },

    beforeDestroy() {
        window.removeEventListener('resize', this.handleResize)
    },

    methods: {
        // rewrite of core function
        stepBack() {
            const index = this.steps.indexOf(this.activeStep);
            if (this.referralUrl) {
                window.location.href = this.referralUrl;
            } else if (index !== -1 && index !== 0) {
                this.activeStep = this.steps[index - 1];
            } else if (index === 0) {
                sessionStorage.setItem('CarFinder::redirectToResults', 'true');
                // Alter of the core function here to not used Document.referrer value as bmw used external redirect login
                window.location.href = sessionStorage.getItem('CarFinder::redirectToPdp') || this.options.backUrl;
            }
        },

        moveToNextStep() {
            const index = this.steps.indexOf(this.activeStep);

            if (index !== -1 && index < this.steps.length - 1) {
                this.activeStep = this.steps[index + 1];
            }
        },

        hideAccessoriesBlock() {
            this.$dispatch('Main::hideSubsection');
            this.$broadcast('ChooseVehicle::showAll');
            setTimeout(() => { // slick wont pick up slide changes right away, need a timeout
                this.$broadcast('CarouselColor::reInit');
            }, 1);
        },

        saveCarVehicles() {
            return [
                {
                    id: this.vehicle.id,
                    isInWishlist: this.vehicle.isInWishlist,
                    saveWishlistUrl: this.vehicle.saveWishlistUrl
                }
            ];
        },

        getCheckoutCTAName() {
            const index = this.steps.indexOf(this.activeStep);

            if ((this.steps.length - index) > 2) {
                return 'Continue';
            }

            return 'Checkout';
        },

        handleResize() {
            this.fullWidth = document.documentElement.clientWidth;
            this.isMobile = this.fullWidth < 737;
        },

        clickStandardFeatureButton() {
            this.$dispatch('Main::scrollToElement', 'my-build-standard-specifications')
        },

        clickTechnicalFeatureButton() {
            this.$dispatch('Main::scrollToElement', 'my-build-technical-specifications')
        }
    },

    ready() {
        window.addEventListener('resize', this.handleResize);

        this.$store.commit('setCurrentCategory', this.options.category);
        this.$store.commit('setBrand', this.options.productBrand);

        if (this.productType === 'simple') {
            this.selectedCar = this.product.id;
            this.vehicle = this.product;
        } else {
            this.selectedCar = this.product.childProductId;
        }

        this.$dispatch('Configurator::preSelectConfiguration', this.selectedCar);

        this.$nextTick(() => {
            this.sendProductDetails();
        });
    },

    created() {
        const url = this.$root.parseURL().searchObject;
        const requestedData = {
            pxMmCode: url.mmCode,
            pxVrmInput: url.vrm,
            pxRegistrationYear: url.registrationYear,
            pxMileage: url.tradeInMileage,
            pxCondition: url.condition,
            pxAdditionalInfo: url.additionalInfo,
            pxSettlement: url.settlement
        };

        this.$store.commit('setDeepLinkRequestParams', requestedData);

        // Remove parsed params from URL to allow arbitrary data changes
        ['mmCode', 'vrm', 'registrationYear', 'tradeInMileage', 'condition', 'additionalInfo', 'settlement', 'isCorporate']
            .forEach((param) => {
                delete url[param];
            });
        const cleanedUrl = this.$root.makeURLSearch(url);
        window.history.pushState({}, document.title, `?${cleanedUrl}`);

        EventsBus.$on('configurator::stepBack', () => {
            this.stepBack();
        });
    },

    components: {
        appSaveCar,
        appChooseVehicleGrid,
        appAccessories,
        appTopFinanceQuote,
        appNavigation,
        appProductTopContainer,
        appExperiencePopup
    }
});
</script>
