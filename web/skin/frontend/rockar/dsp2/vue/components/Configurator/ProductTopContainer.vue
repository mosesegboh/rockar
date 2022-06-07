<template>
    <div class="page-title-wrapper category-products-list">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <div class="heading-container">
            <div class="wishlist-button desktop" @click="toggleSaveCarOverlay">
                <span class="icon-wishlist" :class="{ 'added-to-wishlist': vehicle.isInWishlist }"></span>
            </div>
            <div class="grid">
                <div
                    class="row product-detailed-view"
                    :class="{ 'product-new-view': carAttributes.km === null }"
                    v-el:product-detailed-view
                >
                    <img
                        class="cosy-background"
                        alt="cosy-background"
                        :src="bgImageUrl"
                        v-if="show360 && sliderState.view360Exterior && showExteriorBackground"
                    />
                    <div class="col-4">
                        <div class="product-headings product-pod">
                            <div class="vehicle-heading-desktop">
                                <div class="main-heading">{{ product.title }}</div>
                                <div class="sub-heading">{{ product.bodystyle }}</div>
                            </div>
                            <app-attributes
                                :car-attributes="carAttributes">
                            </app-attributes>
                            <div class="product-mileage-attribute" v-if="carAttributes.km">
                                <span class="mileage">{{ 'Mileage' | translate }}</span>
                                <span class="km">{{ carAttributes.km.value }}</span>
                            </div>
                            <div class="product-color-attribute">
                                <img :alt="exteriorColor.value" class="product-color-image" :src="exteriorColor.image"/>
                                <span class="product-color-title">{{ exteriorColor.value }}</span>
                            </div>
                            <app-pod-summary-specs
                                :car-attributes="carAttributes"
                                :fuel-type="carAttributes.fuel_type.value">
                            </app-pod-summary-specs>
                            <app-car-compare
                                :open.sync="carCompareOverlay"
                                :compare="compare"
                                :compare-add-url="product.compareAddUrl"
                                :compare-remove-url="product.compareRemoveUrl"
                                :is-in-compare-list.sync="product.isInCompareList"
                                @close-compare="carCompareOverlay = false"
                            ></app-car-compare>
                            <button
                                type="button"
                                name="button"
                                class="button dsp2-action compare-button action-compare"
                                :class="{ 'remove': product.isInCompareList }"
                                @click="addRemoveFromCompare()"
                            >
                                {{ product.isInCompareList ? 'Remove' : 'Compare' | translate }}
                            </button>
                            <button type="button" name="button" class="button dsp2-outline test-drive-button" @click="redirectToYouDrive()">
                              <span>{{ bookTestDriveText }}</span>
                            </button>
                        </div>
                    </div>
                    <div class="col-8">
                        <app-save-car
                            v-if="vehicle.isInWishlist !== undefined"
                            :open="saveCarOverlay"
                            :customer-name="product.customerName"
                            :car-id="product.id"
                            :product-name="product.title"
                            :product-title="product.name"
                            :product-subtitle="product.bodystyle"
                            :product-sku="product.sku"
                            :product-price="rockarPrice"
                            :save-wishlist-url="vehicle.saveWishlistUrl"
                            :my-account-url="product.myAccountUrl"
                            :is-in-wishlist="vehicle.isInWishlist"
                            :is-ajax-request="product.isWishlistAjax"
                            :remove-from-wishlist-url="product.removeFromWishlistUrl"
                            :image="vehicle.exteriorImage"
                            :show-as-link="false"
                            @toggle-is-in-wishlist="toggleIsInWishlist"
                            @open-save-car-overlay="openSaveCarOverlay"
                            @close-save="saveCarOverlay = false"
                        ></app-save-car>
                        <div class="product-headings product-pod vehicle-heading-mobile">
                            <div class="main-heading">{{ product.title }}</div>
                            <div class="sub-heading">{{ product.bodystyle }}</div>
                        </div>
                        <div class="car-hero" :class="{'cosy-background-mob': show360 && sliderState.view360Exterior, 'interior': sliderState.view360Interior}">
                            <div v-if="sliderState.view360Interior" class="slide360 interior">
                                <app-interior-three-sixty-view
                                    :pannellum-script="pannellumScript"
                                    :images="intThreeSixtyCarousel"
                                    :show="sliderState.view360Interior"
                                    v-if="showInterior360"
                                ></app-interior-three-sixty-view>

                                <app-configurator-carousel-images
                                    :slides="intCarousel"
                                    :options="imageSlidersConfig"
                                    v-if="!showInterior360"
                                    v-ref:interior-carousel
                                ></app-configurator-carousel-images>
                            </div>

                            <template v-if="show360">
                                <app-three-sixty-view
                                    :show="sliderState.view360Exterior"
                                    :three-sixty-script="threeSixtyScript"
                                    :images="extCarousel"
                                ></app-three-sixty-view>
                            </template>

                            <template v-if="!show360">
                                <div class="slide360" v-show="sliderState.view360Exterior">
                                    <app-configurator-carousel-images
                                        :slides="extCarousel"
                                        :options="imageSlidersConfig"
                                        v-ref:exterior-carousel
                                    ></app-configurator-carousel-images>
                                </div>
                            </template>

                            <div class="image-type-switcher">
                                <div class="actions-wrapper">
                                    <div
                                        class="switcher exterior-switch"
                                        :class="{'active': sliderState.view360Exterior}"
                                        @click="changeSlider('view360Exterior')"
                                    ></div>
                                    <div
                                        class="switcher interior-switch"
                                        :class="{'active': !sliderState.view360Exterior}"
                                        @click="changeSlider('view360Interior')"
                                    ></div>
                                </div>
                            </div>

                            <div class="wishlist-button" @click="toggleSaveCarOverlay">
                                <span class="icon-wishlist" :class="{'added-to-wishlist': vehicle.isInWishlist }"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import coreProductTopContainer from 'core/components/Configurator/ProductTopContainer';
import appSaveCar from 'dsp2/components/SaveCar';
import UrlParser from 'core/mixins/UrlParser';
import Constants from 'dsp2/components/Shared/Constants';
import appAttributes from 'dsp2/components/Elements/Attributes';
import appOfferTags from 'dsp2/components/Elements/OfferTags';
import appPodSummarySpecs from 'dsp2/components/Elements/PodSummarySpecs';
import appCarCompare from 'dsp2/components/CarFinder/CarCompare';
import appConfiguratorCarouselImages from 'core/components/Configurator/CarouselImages';
import appThreeSixtyView from 'dsp2/components/Elements/ThreeSixtyView';
import appInteriorThreeSixtyView from 'dsp2/components/Configurator/InteriorThreeSixtyView';
import EventTracker from 'dsp2/mixins/EventTracker';
import translateString from 'core/filters/Translate';

export default coreProductTopContainer.extend({
    mixins: [Constants, UrlParser, EventTracker],

    components: {
        appSaveCar,
        appAttributes,
        appOfferTags,
        appPodSummarySpecs,
        appCarCompare,
        appConfiguratorCarouselImages,
        appThreeSixtyView,
        appInteriorThreeSixtyView
    },

    data() {
        return {
            saveCarOverlay: false,
            carCompareOverlay: false,
            imageSlidersConfig: {
                slidesToShow: 1,
                initialSlide: 0,
                mobileNavigation: 1,
                dots: false,
                lazyLoad: 'ondemand',
                infinite: true,
                responsive: [],
                speed: 1
            },
            sliderState: {
                view360Interior: false,
                view360Exterior: true
            },
        }
    },

    props: {
        options: {
            required: true,
            type: Object
        },

        carAttributes: {
            required: true,
            type: Object,
            default() {
                return {}
            }
        },

        offerTagData: {
            required: false,
            type: Object
        },

        technicalSpecItems: {
            required: false,
            type: Array,
            default() {
                return [];
            }
        },

        ajaxLoading: {
            required: false,
            type: Boolean,
            default: false
        },

        compare: {
            required: true,
            type: Object
        },

        extCarousel: {
            required: false,
            type: Array,
            default() {
                return [];
            }
        },

        intCarousel: {
            required: false,
            type: Array,
            default() {
                return [];
            }
        },

        intThreeSixtyCarousel: {
            required: false,
            type: Array,
            default() {
                return [];
            }
        },

        bgImageUrl: {
            required: false,
            type: String,
            default: ''
        },

        exteriorColor: {
            required: true,
            type: Object
        },

        threeSixtyScript: {
            required: true,
            type: String
        },

        pannellumScript: {
            required: true,
            type: String
        }
    },

    computed: {
        vehicle() {
            let vehicle = {
                'title': '',
                'saveWishlistUrl': ''
            };

            this.vehicles.find((item) => {
                if (item.id === this.product.childProductId) {
                    vehicle = item;

                    return true;
                }
            });

            return vehicle;
        },

        show360() {
            return this.extCarousel.length === this.THREESIXTYIMAGECOUNT;
        },

        showInterior360() {
            return this.intThreeSixtyCarousel.length > 0;
        },

        eventTrackerVehicleTitle() {
            return `${this.product.title} ${this.product.short_title}`
        },

        showExteriorBackground() {
            return true;
        },

        bookTestDriveText() {
            return this.translateString('Book a test drive');
        }
    },

    watch: {
        'sliderState.view360Exterior'(value) {
            if (value && !this.show360) {
                if (this.$refs.exteriorCarousel.slides) {
                    jQuery(this.$refs.exteriorCarousel.$el).slick('setPosition');
                }
            }
        },

        'sliderState.view360Interior'(value) {
            if (value) {
                if (this.$refs.interiorCarousel && this.$refs.interiorCarousel.slides) {
                    jQuery(this.$refs.interiorCarousel.$el).slick('setPosition');
                }
            }
        }
    },

    methods: {
        translateString,

        redirectToYouDrive() {
            if (this.options.youDriveUrl) {
                window.location.href = this.options.youDriveUrl;
            }
        },

        toggleSaveCarOverlay() {
            if (this.product.customerName.toLowerCase() === 'guest') {
                this.saveCarOverlay = !this.saveCarOverlay;
            }

            if (this.product.customerName.toLowerCase() !== 'guest' && this.vehicle.isInWishlist === false) {
                this.ajaxLoading = true;
                this.$broadcast('SaveCar::addToWishlist');
            }

            if (this.product.customerName.toLowerCase() !== 'guest' && this.vehicle.isInWishlist === true) {
                this.saveCarOverlay = !this.saveCarOverlay;
                this.$broadcast('SaveCar::removeFromWishlist');
            }
        },

        toggleIsInWishlist(value) {
            this.vehicle.isInWishlist = value;
        },

        openSaveCarOverlay() {
            this.saveCarOverlay = true;
            this.ajaxLoading = false;
        },

        addRemoveFromCompare() {
            this.carCompareOverlay = true;
            this.$broadcast('CarCompare::triggerAddRemove');

            if (!this.product.isInCompareList) {
                this.fireEventForTracking(
                    this.getEventConstants().PAGEDESCRIPTION.TRIGGERS,
                    `${this.getEventConstants().TRIGGERTRACKERVALUES.ADDTOCOMPARE}${this.eventTrackerVehicleTitle}`
                );
            }
        },

        changeSlider(state) {
            this.sliderState.view360Interior = false;
            this.sliderState.view360Exterior = false;
            this.sliderState[state] = true;
        },

        windowResizeListener() {
            // We don't need to do anything if vehicle is demo
            if (this.carAttributes.km) {
                return;
            }

            this.$els.productDetailedView
                .style
                .setProperty('--product-right-margin', `${document.body.clientWidth}px`);
        }
    },

    ready() {
        jQuery('body').addClass('on-light');

        this.windowResizeListener();
        window.addEventListener('resize', this.windowResizeListener);

        this.fireEventForTracking(
            this.getEventConstants().PAGEDESCRIPTION.VIEWS,
            `${this.getEventConstants().EVENTRACKERVALUES.PDPDETAILS}${this.eventTrackerVehicleTitle}`
        );
    },

    beforeDestroy() {
        window.removeEventListener('resize', this.windowResizeListener)
    },

    events: {
        'Product::toggleInCompareList'(sku) {
            if (this.product.sku === sku) {
                this.product.isInCompareList = !this.product.isInCompareList;
            }
        },

        'Product::updateWishlistProp'(data) {
            if (data.productId === this.product.id) {
                this.vehicle.isInWishlist = data.isInWishlist;
            }
        },

        'Product::clearCompare'() {
            this.product.isInCompareList = false;
        }
    }
});
</script>
