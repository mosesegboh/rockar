<template>
    <div id="full-configurator">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <div class="you-build-logo mobile">
            <span></span>{{ 'Youbuild' | translate }}
        </div>

        <div class="row">
            <div class="col-12">
                <app-product-top-container
                        :product="product"
                        :vehicles="saveCarVehicles"
                        v-ref:product-top-container
                ></app-product-top-container>
            </div>
            <div class="col-lg-9 col-md-12 configurator-window">
                <div class="you-build-content-wrapper">
                    <div class="col-3 col-3-no-margin configurator-actions"
                         :class="{'summary-category-active': state === 4}">
                        <div class="you-build-logo desktop">
                            <span></span>{{ 'Youbuild' | translate }}
                        </div>

                        <template v-for="(vIndex, view) in views">
                            <button class="constructor-button" :class="[state == vIndex ? 'button-active' : 'button-unactive']" @click="switchView(vIndex, view)">
                                <span>{{ view.name }}</span>
                            </button>

                            <div class="sub-categories" v-if="state === vIndex && vIndex !== 4">
                                <ul>
                                    <li v-for="(cIndex, category) in configuration.sections[vIndex].categories">
                                        <div @click="updateSection(cIndex, configuration.sections[vIndex].type)"
                                            class="sub-category desktop"
                                            :class="{'active' : activeSection === cIndex}">
                                            {{ category.title | decodeHtml }}
                                        </div>
                                        <div class="sub-category mobile">
                                            <app-sub-section-menu-item
                                                @toggle-feature="toggleFeature"
                                                :is-slider="getIsSubSectionSlider(vIndex)"
                                                :access-settings="getAccessSettings(vIndex)"
                                                :init-categories="sectionsList.accessories.categories"
                                                :media="configuration.media"
                                                :active="cIndex"
                                                :features="category.features"
                                                :category-title="category.title"></app-sub-section-menu-item>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </template>
                    </div>

                    <div class="col-9 configurator-slider col-sm-12 col-md-12" v-show="state <= 1">
                        <div class="images-wrapper">
                            <div class="active-image">
                                <div class="image-zoom" @click="openSliderZoom()">
                                    <span class="zoom-icon"></span>
                                </div>
                            </div>
                            <div class="images-slider">
                                <div class="slider slider-ext" :class="{'active': imageType === 'ext'}">
                                    <app-carousel-images
                                            :id="'ext-preview'"
                                            :slides="imagesCarouselSlidesExt"
                                            :options="{
                                                slidesToShow: 1,
                                                initialSlide: 0,
                                                responsive: [{
                                                    breakpoint: getMobileBreakpoint,
                                                    settings: {
                                                        arrows: false
                                                    }
                                                }]
                                            }"
                                            :slide-width="1400"
                                            :slide-height="700"
                                    ></app-carousel-images>
                                </div>
                                <div class="slider slider-int" :class="{'active': imageType === 'int'}">
                                    <app-carousel-images
                                            :id="'int-preview'"
                                            :slides="imagesCarouselSlidesInt"
                                            :options="{
                                                slidesToShow: 1,
                                                initialSlide: 0,
                                                responsive: [{
                                                    breakpoint: getMobileBreakpoint,
                                                    settings: {
                                                        arrows: false
                                                    }
                                                }]
                                            }"
                                            :slide-width="1400"
                                            :slide-height="700"
                                    ></app-carousel-images>
                                </div>
                                <span class="mobile-rotate-icon"></span>
                            </div>

                            <div class="full-screen-images-slider"
                                 v-if="zoomSliderActive"
                                 :style="{visibility: zoomSliderVisibility}"
                                 :class="{'mobile-landscape': isLandscape}"
                            >
                                <div class="slider-inner-wrapper">
                                    <div class="image-type-switcher">
                                        <div class="actions-wrapper">
                                            <button class="button button-narrow button-gray-light-2"
                                                    :class="{'active': fullScreenImageType === 'ext'}"
                                                    @click="fullScreenImageType = 'ext'"
                                            >{{ 'Exterior' | translate }}</button>
                                            <button class="button button-narrow button-gray-light-2"
                                                    :class="{'active': fullScreenImageType === 'int'}"
                                                    @click="fullScreenImageType = 'int'"
                                            >{{ 'Interior' | translate }}</button>
                                        </div>
                                    </div>

                                    <div class="close-button" @click="closeSliderZoom()" v-show="!isLandscape"></div>

                                    <div class="sliders-wrapper">
                                        <div class="slider slider-ext" :class="{'active': fullScreenImageType === 'ext'}">
                                            <app-carousel-images
                                                    :id="'full-screen-ext'"
                                                    :slides="imagesCarouselSlidesExt"
                                                    :options="fullScreenSliderOptions"
                                                    :full-screen="true"
                                                    :active="(state === 0 || state === 1)"
                                                    :type="'exterior'"
                                                    v-ref:zoom-slider-element-ext
                                            ></app-carousel-images>
                                        </div>
                                        <div class="slider slider-int" :class="{'active': fullScreenImageType === 'int'}">
                                            <app-carousel-images
                                                    :id="'full-screen-int'"
                                                    :slides="imagesCarouselSlidesInt"
                                                    :options="fullScreenSliderOptions"
                                                    :full-screen="true"
                                                    :active="(state === 0 || state === 1)"
                                                    :type="'interior'"
                                                    v-ref:zoom-slider-element-int
                                            ></app-carousel-images>
                                        </div>
                                    </div>
                                    <span class="mobile-rotate-icon"></span>
                                </div>
                            </div>
                        </div>

                        <div class="finance-summary-wrapper">
                            <app-top-finance-quote></app-top-finance-quote>
                        </div>

                        <div class="features-carousel desktop">
                            <app-features-carousel-list
                                    v-if="state == 0"

                                    @change-image="changeImage"
                                    @toggle-feature="toggleFeature"

                                    type="ext"
                                    :visited-sections="visitedSections.ext"
                                    :media="configuration.media"
                                    :init-categories="sectionsList.exterior.categories"
                                    v-ref:features-carousel-list>
                            </app-features-carousel-list>

                            <app-features-carousel-list
                                    v-if="state == 1"

                                    @change-image="changeImage"
                                    @toggle-feature="toggleFeature"

                                    type="int"
                                    :visited-sections="visitedSections.int"
                                    :media="configuration.media"
                                    :init-categories="sectionsList.interior.categories"
                                    v-ref:features-carousel-list>
                            </app-features-carousel-list>
                        </div>
                    </div>

                    <app-options
                            v-if="state == 2"

                            @toggle-feature="toggleFeature"

                            type="opt"
                            :media="configuration.media"
                            :init-categories="sectionsList.options.categories"
                            :access-settings="configuration.accessSettings.options"

                            v-ref:options>
                    </app-options>

                    <app-accessories
                            v-if="state == 3"

                            @toggle-feature="toggleFeature"

                            type="acc"
                            :media="configuration.media"
                            :init-categories="sectionsList.accessories.categories"
                            :access-settings="configuration.accessSettings.accessories"

                            v-ref:accessories>
                        <div slot="accessories-important-information"><slot name="accessories-important-information"></slot></div>
                    </app-accessories>

                    <app-summary
                            v-show="state == 4"

                            @change-view="switchView"
                            @checkout="goToCheckout"
                            @prev-view="prevView"

                            :media="configuration.media"
                            :sections="configuration.sections"
                            :product="product"
                            :technical-spec-items="technicalSpecItems"
                            :is-instore="isInstore"
                            :save-wishlist-url="configuration.saveWishlistUrl"
                            :is-in-wishlist="configuration.isInWishlist"
                            :standard-features="standardFeatures"
                            :base-price="basePrice">
                    </app-summary>
                </div>

                <app-modal :show.sync="showPopup" class="save-configurations-popup">
                    <div slot="content">
                        <p class="save-configurations-title">
                            {{ 'You are about to clear your configuration' | translate }}
                        </p>

                        <p class="save-configurations-text">
                            {{ 'If you return back to previous page you will lose your current configured features.
                            Alternatively by clicking on SAVE CONFIGURATION your configured car will be saved.
                            You will be able to view your configuration in \'MY ACCOUNT\' and continue where you previously left off.' | translate }}
                        </p>

                        <p class="save-configurations-text">
                            {{ 'You are about to clear your configuration.' | translate }}
                        </p>

                        <div class="row">
                            <div class="col-3">
                                <button class="button-medium button-empty-light cancel-button" @click="cancelRedirect()">
                                    <span>
                                        {{ 'Cancel' | translate }}
                                    </span>
                                </button>
                            </div>
                            <div class="col-9 align-right">
                                <button class="button-empty-light button-medium" @click="discardAndRedirect()">
                                    <span>
                                        {{ 'Discard' | translate }}
                                    </span>
                                </button>
                                <button class="button-medium" @click="openSavePopup()">
                                    <span>
                                        {{ 'Save Configuration' | translate }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </app-modal>

                <app-modal :show.sync="showConflicts">
                    <div slot="content">
                        <div class="conflict-window-container">
                            <h3 v-if="selected">
                                {{ 'To add this feature' | translate }}
                            </h3>

                            <h3 v-else>
                                {{ 'To remove this feature' | translate }}
                            </h3>

                            <div class="conflict-pad">
                                <p v-if="selected">
                                    {{ 'Choosing' | translate }} {{{ conflictFeatures.selected }}}
                                    {{ 'will result in the following changes to your configuration' | translate }}
                                </p>
                                <p v-else>
                                    {{ 'Removing' | translate }} {{{ conflictFeatures.selected }}}
                                    {{ 'will result in the following changes to your configuration' | translate }}
                                </p>

                                <h4 class="conflict-window-title" v-if="conflictFeatures.included.length">
                                    {{ 'Adding' | translate }}
                                </h4>

                                <div v-for="feature in conflictFeatures.included">
                                    <ul class="with-bullets">
                                        <li>
                                            <span>
                                                {{{ feature.description }}}
                                            </span>

                                            <span>
                                                ({{ feature.section }})
                                            </span>
                                        </li>
                                    </ul>
                                </div>

                                <h4 class="conflict-window-title" v-if="conflictFeatures.excluded.length">
                                    {{ 'Removing' | translate }}
                                </h4>

                                <div v-for="feature in conflictFeatures.excluded">
                                    <ul class="with-bullets">
                                        <li>
                                            <span>
                                                {{{ feature.description }}}
                                            </span>

                                            <span>
                                                ({{ feature.section }})
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <p v-if="conflicts" class="conflict-price-change">
                            {{ 'Change to cost of car will be:' }}
                            {{ conflicts.conflicts.priceChange | numberFormat '0,0' true }}
                        </p>
                    </div>
                </app-modal>

                <div class="row" v-if="showPartExchangeNotification">
                    <div class="part-exchange-notification col-9">
                        {{ partExchangeNotification | convertNCR }}
                    </div>
                </div>

                <div class="sub-navigation desktop">
                    <button class="btn-back" @click="openPopup()">
                        <span class="title">{{ 'Step Back' | translate }}</span>
                    </button>
                    <button class="button-gray-light-2 btn-features btn-sub-navigation btn-standard-features" @click="$dispatch('Main::scrollToElement', 'standard-specifications')">
                        <span class="title">{{ 'Standard Features' | translate }}</span>
                    </button>
                    <button class="button-gray-light-2 btn-features btn-sub-navigation btn-technical-features" @click="$dispatch('Main::scrollToElement', 'technical-specifications')">
                        <span class="title">{{ 'Technical Features' | translate }}</span>
                    </button>
                </div>

                <div class="sub-navigation mobile">
                    <button class="btn-back" @click="openPopup()">
                    </button>
                    <button class="btn-continue">
                        <span class="title" @click="goToCheckout(productId)">{{'Checkout' | translate }}</span>
                    </button>
                </div>

                <div class="configurator-mobile-actions-wrapper">
                    <button type="button" class="button button-empty finance-edit-button" @click="$refs.financeQuote.showFinanceOverlay()">{{ 'Finance' | translate }}</button>
                    <button type="button" class="button button-gray-light-2 features-button" @click="$dispatch('Main::scrollToElement', 'standard-specifications')">{{ 'Features' | translate }}</button>
                </div>

                <div class="finance-quote-mobile-container"></div>

                <div class="features-accordion-list">
                    <div class="standard-features" v-if="standardFeatures || technicalSpecItems">
                        <div id="standard-specifications" class="features-list">
                            <p class="specification-title">{{ 'Standard features' | translate }}</p>
                            <app-accordion-group :open-first="false">
                                <div class="accordion-group">
                                    <app-accordion v-for="features in standardFeatures" :title="features.title" class-name="accordion-light" type="right-down" :id="'standard_features_' + $index" track-by="$index">
                                        <li class="accordion-list standard-features">
                                            <div class="features-item" v-for="item in features.features" track-by="$index">
                                                {{{ item }}}
                                            </div>
                                        </li>
                                    </app-accordion>
                                </div>
                            </app-accordion-group>
                        </div>

                        <div id="technical-specifications" class="technical-features features-list">
                            <p class="specification-title">{{ 'Technical features' | translate }}</p>
                            <app-accordion-group>
                                <div class="accordion-group">
                                    <app-accordion v-for="section in technicalSpecItems" :title="section.title" type="right-down" class-name="accordion-light" :id="'technical_spec_items_' + $index" track-by="$index">
                                        <li class="accordion-list" v-if="section.type === 'table'">
                                            <div class="technical-features">
                                                <div class="features-item section-subtitle">{{{ section.subtitle }}}</div>
                                                <div class="features-item" v-for="item in section.items" track-by="$index">
                                                    <div class="label">{{{ item.title }}}</div>
                                                    <div class="value">{{{ item.value }}}</div>
                                                </div>
                                            </div>
                                        </li>

                                        <li class="accordion-list" v-else>
                                            <div class="accordion-content">
                                                <div class="my-build-item-list">
                                                    <div class="my-build-item-block" :class="{ active: activeIndex == $index }" v-for="part in section.parts" @click="activeIndex = $index">
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

            <div class="col-lg-3 col-md-12 finance-quote finance-quote-desktop-container">
                <app-full-configurator-finance-quote
                    @next-step="nextView()"

                    :car-data='carData'
                    :lead-time='financeData.lead_time'
                    :product-id="productId"
                    :finance-variables='financeVariables'
                    :finance-params-origin='financeParamsOrigin'
                    :finance-slider-steps='financeSliderSteps'
                    :pay-in-full-payment='payInFullPayment'
                    :active-payment='activePayment'
                    :rockar-price='financeData.rockar_price'
                    :save-off-rrp='financeData.save_off_rrp'
                    :monthly-price='financeData.monthly_price'
                    :finance-url='financeData.finance_url'
                    :payment-save-url='financeData.payment_save_url'
                    :part-exchange='partExchange'
                    :car-conditions='carConditions'
                    :calc-type='calcType'
                    :part-exchange-additional='partExchangeAdditional'
                    :part-exchange-saved="partExchangeSaved"
                    :saved-px="savedPx"
                    :additional-info='additionalInfo'
                    :hire-payments='hirePayments'
                    :is-hire='isHire'
                    :cash-deposit='financeData.cash_deposit'
                    :cashback='financeData.cashback'
                    :states-locked="statesLocked"
                    v-ref:finance-quote>
                </app-full-configurator-finance-quote>
            </div>
        </div>
    </div>
</template>

<script>
    import appAccordionGroup from 'core/components/AccordionGroup';
    import appAccordion from 'core/components/Accordion';

    import appSlider from 'core/components/FullConfigurator/Slider';

    import appFeaturesCarouselList from 'core/components/FullConfigurator/FeaturesCarouselList';
    import appSubSectionMenuItem from 'core/components/FullConfigurator/SubSectionMenuItem';

    import appOptions from 'core/components/FullConfigurator/Options.vue';
    import appAccessories from 'core/components/FullConfigurator/Accessories.vue';

    import appSummary from 'core/components/FullConfigurator/Summary';
    import appFullConfiguratorFinanceQuote from 'motorrad/components/FullConfigurator/FinanceQuote';
    import appCarouselImages from 'core/components/FullConfigurator/CarouselImages';

    import appModal from 'core/components/Elements/Modal';
    import appTopFinanceQuote from 'core/components/Configurator/TopFinanceQuote';
    import appProductTopContainer from 'core/components/Configurator/ProductTopContainer';
    import appImageZoom from 'core/components/Configurator/ImageZoom';

    import appJlrImage from 'core/utils/JlrImage';

    export default Vue.extend({
        props: {
            product: {
                required: true,
                type: Object
            },

            configuration: {
                required: true,
                type: Object
            },

            views: {
                required: true,
                type: Array
            },

            showPopup: {
                required: false,
                type: Boolean
            },

            exitButtonTitle: {
                required: true,
                type: String
            },

            addToCartUrl: {
                required: true,
                type: String
            },

            productUrl: {
                required: true,
                type: String
            },

            configurableSku: {
                required: true,
                type: String
            },

            basePrice: {
                required: true,
                type: Number
            },

            checkoutUrl: {
                required: true,
                type: String
            },

            addAccessoriesUrl: {
                required: true,
                type: String
            },

            addFeatureUrl: {
                required: true,
                type: String
            },

            standardFeatures: {
                required: false,
                type: Array,
                default() {
                    return [];
                }
            },

            technicalSpecItems: {
                type: Array,
                default() {
                    return [];
                }
            },

            formKey: {
                required: true,
                type: String
            },

            financeData: {
                required: true,
                type: Object
            },

            carConditions: {
                type: Array,
                required: false
            },

            calcType: {
                required: true,
                type: String
            },

            partExchangeAdditional: {
                type: Object,
                required: false
            },

            partExchangeSaved: {
                required: false,
                type: Boolean,
                default: false
            },

            savedPx: {
                required: false,
                default: false
            },

            additionalInfo: {
                required: true,
                type: Array
            },

            hirePayments: {
                required: true,
                type: Array
            },

            isHire: {
                required: true,
                type: Number
            },

            isInstore: {
                required: true,
                type: Boolean
            },

            payInFullPayment: {
                required: true,
                type: Array
            },

            activePayment: {
                required: true,
                type: Object
            },

            financeSliderSteps: {
                required: true,
                type: Object
            },

            financeParamsOrigin: {
                required: true,
                type: Object
            },

            financeVariables: {
                required: true,
                type: Array
            },

            productId: {
                required: true,
                type: Number
            },

            carData: {
                required: true,
                type: Array
            },

            partExchange: {
                type: Object,
                required: false
            },

            state: {
                type: Number,
                required: false,
                default: 0
            },

            selectedCar: {
                type: Number,
                required: true
            },

            partExchangeNotification: {
                required: false,
                type: String
            },

            brand: {
                required: false,
                type: String,
                default: ''
            },

            category: {
                required: false,
                type: String,
                default: ''
            }
        },

        data() {
            return {
                conflicts: {
                    features: false,
                    conflicts: false
                },
                imageType: 'ext',
                jlrImage: null,
                media: null,
                ajaxLoading: false,
                addedAccessories: null,
                showAccessories: false,
                showHeader: true,
                optionsOpen: false,
                openActionsSwitcher: false,
                tempState: null,
                redirectUrl: null,
                form: null,
                selectedFeatureTitle: null,
                selectedFeatureCategory: null,
                showConflicts: false,
                selected: false,
                statesLocked: false,
                openSaveConfiguration: false,
                visitedSections: {
                    ext: [],
                    int: []
                },
                showPartExchangeNotification: false,
                zoomSliderActive: false,
                zoomSliderVisibility: 'hidden',
                isLandscape: false,
                activeSection: 0,
                fullScreenSliderOptions: {
                    slidesToShow: 1,
                    initialSlide: 0,
                    responsive: []
                },
                fullScreenImageType: 'ext',
                referrerUrl: ''
            }
        },

        computed: {
            conflictFeatures() {
                var features = {
                    included: [],
                    excluded: [],
                    selected: null
                };

                if (this.conflicts.features) {
                    var conflictItems = this.conflicts.features;

                    Object.keys(conflictItems).forEach((key) => {
                        switch (conflictItems[key].action) {
                            case 'included':
                                features.included.push(conflictItems[key]);
                                break;

                            case 'excluded':
                                features.excluded.push(conflictItems[key]);
                                break;

                            case 'selected':
                                features.selected = `${conflictItems[key].description}
                              (${conflictItems[key].section})`;
                                break;

                            default:
                                return;
                        }
                    });
                }
                return features;
            },

            sectionsList() {
                var sections = {
                    exterior: null,
                    interior: null,
                    options: null,
                    accessories: null,
                    type: null
                };

                this.configuration.sections.forEach((section) => {
                    switch (section.id) {
                        case 'jdxexterior':
                        case 'exterior':
                            sections.exterior = section;
                            sections.exterior.type = 'ext';
                            break;
                        case 'jdxinterior':
                        case 'interior':
                            sections.interior = section;
                            sections.interior.type = 'int';
                            break;
                        case 'jdxother':
                        case 'options':
                            sections.options = section;
                            sections.options.type = 'opt';
                            break;
                        case 'jdxother2':
                        case 'accessories':
                            sections.accessories = section;
                            sections.accessories.type = 'acc';
                            break;
                        default:
                            return;
                    }
                });

                return sections;
            },

            productData() {
                return this.$store.state.general.product;
            },

            pageBrand() {
                return this.$store.state.general.brand;
            },

            /**
             * Prepare data in correct format for ProductTopContainer component
             */
            saveCarVehicles() {
                return [
                    {
                        id: this.productId,
                        isInWishlist: this.configuration.isInWishlist,
                        saveWishlistUrl: this.configuration.saveWishlistUrl
                    }
                ];
            },

            imagesCarouselSlides() {
                return Object.keys(this.configuration.media[this.imageType]).map(key => this.configuration.media[this.imageType][key]);
            },

            imagesCarouselSlidesExt() {
                return Object.keys(this.configuration.media.ext).map(key => this.configuration.media.ext[key]);
            },

            imagesCarouselSlidesInt() {
                return Object.keys(this.configuration.media.int).map(key => this.configuration.media.int[key]);
            },

            getMobileBreakpoint() {
                return this.$root.RESPONSIVE_BREAKPOINTS.MOBILE;
            },

            rockarPrice() {
                return this.$store.state.finance.rockarPrice;
            }
        },

        methods: {
            changeImage(type, image) {
                this.imageType = type;
                EventsBus.$emit('change-image', image);
            },

            scrollTop() {
                this.$nextTick(() => {
                    jQuery('html, body').animate({
                        scrollTop: jQuery('#full-configurator').offset().top
                    }, 400);
                });
            },

            toggleFeature(feature, selected) {
                this.selected = selected;
                this.ajaxLoading = true;
                this.selectedFeatureTitle = feature.title;
                this.selectedFeatureCategory = typeof feature.category_title !== 'undefined' ? feature.category_title : '';
                var apiURL = selected ? feature.url.add : feature.url.remove;

                this.$http({
                    url: this.addFeatureUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        form_key: this.formKey,
                        product: this.product.id,
                        query: apiURL
                    }
                }).then(this.processUrlSuccess, this.processUrlFail);
            },

            switchView(index) {
                if (this.state === index) return;
                if (this.statesLocked && this.state + 1 < index) return;

                this.ajaxLoading = true;
                this.tempState = index;

                this.$http({
                    url: this.addFeatureUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        form_key: this.formKey,
                        product: this.product.id,
                        query: this.configuration.urlKey,
                        isWishlistAjax: this.product.isWishlistAjax
                    }
                }).then(this.processUrlSuccess, this.processUrlFail).then(() => {
                    this.scrollTop();
                    this.activeSection = 0;
                });

                if (index === 4) {
                    EventsBus.$emit('update-summary-images');
                }
            },

            nextView() {
                var nextState = this.state + 1;
                this.switchView(nextState, this.views[nextState]);
            },

            prevView() {
                var prevState = this.state - 1;
                this.switchView(prevState, this.views[prevState]);
            },

            processUrlSuccess(data) {
                if (data.data.success) {
                    data = data.data;

                    this.ajaxLoading = false;
                    this.configuration.urlKey = data.urlKey;
                    this.configuration.media = data.media;
                    this.configuration.price = data.price;
                    this.configuration.isInWishlist = data.isInWishlist;
                    this.configuration.saveWishlistUrl = data.saveWishlistUrl;
                    if (data.techData) {
                        this.technicalSpecItems = data.techData.techSpecsTable;
                    }

                    data.sections.forEach((value, index) => {
                        value.categories.forEach((category, cIndex) => {
                            this.configuration.sections[index].categories.$set(cIndex, category);
                        })
                    });

                    if (data.conflicts) {
                        this.conflicts = data.conflicts;
                        this.showConflicts = true;
                    }

                    if (this.tempState !== null) {
                        this.state = this.tempState;
                        this.tempState = null;

                        if (this.state === this.views.length - 1) {
                            this.statesLocked = false;
                        }
                    }

                    window.history.replaceState({}, '', data.configurationUrl);
                } else {
                    this.processUrlFail(data);
                }

                this.$broadcast('FinanceQuote::FullConfiguratorForceUpdateFinanceQuote', this.views[this.state].name);
                this.$broadcast('SaveCar::changeIsInWishlist', data.isInWishlist);

                this.$nextTick(() => {
                    // Needed to refresh slick sliders, otherwise slides can get messed up (overlapping, stacking)
                    jQuery(window).trigger('resize');
                });

                data = null; // Free up memory
            },

            processUrlFail(data) {
                this.ajaxLoading = false;
                console.error(data);
            },

            openPopup() {
                this.clearForm();
                //  On some instances such as IE11 `document.referrer` is sometime empty
                this.redirectUrl = document.referrer || this.referrerUrl;
                this.showPopup = true;
            },

            discardAndRedirect() {
                if (this.form) {
                    this.form.submit();
                } else {
                    window.location = this.redirectUrl;
                }
            },

            cancelRedirect() {
                this.showPopup = false;
            },

            clearForm() {
                this.form = null;
            },

            goToCheckout(productId) {
                sessionStorage.setItem('from_pdp', 'false');
                this.ajaxLoading = true;
                this.$http({
                    url: this.addToCartUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        form_key: this.formKey,
                        product: productId,
                        isFullConfigurator: 1
                    }
                }).then(this.goToCheckoutSuccess, this.goToCheckoutFail);
            },

            goToCheckoutSuccess() {
                const productFeatureObject = {
                    'event': 'addToCart',
                    'ecommerce': {
                        'currencyCode': 'GBP',
                        'add': {
                            'products': [{
                                'name': this.product.title,
                                'id': this.product.sku,
                                'price': this.rockarPrice,
                                'quantity': 1,
                                'brand': this.pageBrand,
                                'category': this.productData.category,
                                'variant': this.product.short_title,
                            }]
                        }
                    }
                };

                pushEcommerceTags(productFeatureObject);
                window.location.href = this.checkoutUrl;
            },

            goToCheckoutFail(data) {
                this.ajaxLoading = false;
                console.error(data);
            },

            openSavePopup() {
                this.showPopup = false;
                this.$broadcast('SaveCar::openSavePopup');
            },

            sendProductDetails() {
                const productFeatureObject = {
                    'event': 'productDetails',
                    'ecommerce': {
                        'detail': {
                            'actionField': { 'list': this.productData.category },
                            'products': [{
                                'name': this.product.title,
                                'id': this.productData.configurableSku,
                                'price': this.rockarPrice,
                                'brand': this.pageBrand,
                                'category': this.productData.category,
                                'variant': this.product.short_title,
                            }]
                        }
                    }
                };

                pushEcommerceTags(productFeatureObject);
            },

            /**
             * Open image zoom overlay
             */
            openSliderZoom() {
                this.zoomSliderActive = true;

                // Workaround for slick slider showing offset due to incorrect slide width
                this.$nextTick(() => {
                    this.$broadcast('CarouselImages::setSliderPosition');
                    this.zoomSliderVisibility = 'visible';
                });
            },

            /**
             * Close image zoom overlay
             */
            closeSliderZoom() {
                this.zoomSliderActive = false;
                this.zoomSliderVisibility = 'hidden';
                this.fullScreenImageType = this.imageType;
            },

            updateSection(index, type) {
                switch (type) {
                    case 'ext':
                    case 'int':
                        this.$refs.featuresCarouselList.active = index;
                        this.$refs.featuresCarouselList.switchView(index, type);
                        this.$refs.featuresCarouselList.activeView.view = type;
                        break;
                    case 'opt':
                        this.$refs.options.changeVisible(index);
                        break;
                    case 'acc':
                        this.$refs.accessories.changeVisible(index);
                        break;
                    default:
                        break;
                }

                this.activeSection = index;
            },

            getIsSubSectionSlider(index) {
                return index === 0 || index === 1;
            },

            getAccessSettings(index) {
                const accessSettings = this.configuration.accessSettings;

                if (index === 2) {
                    return accessSettings.options;
                } else if (index === 3) {
                    return accessSettings.accessories;
                }
            },

            /**
             * Check if landscape mode
             *
             * @returns {boolean}
             */
            getIsLandscape() {
                return window.innerWidth > window.innerHeight && this.$root.isMobile();
            },
        },

        events: {
            'FullConfigurator::checkout'() {
                this.goToCheckout(this.productId);
            },

            'ChooseVehicle::showPartExchangeNotification'() {
                if (this.$store.state.general.pxShowNotification) {
                    this.showPartExchangeNotification = true;

                    setTimeout(() => {
                        this.showPartExchangeNotification = false;
                        this.$store.commit('setPxShowNotification', false);
                    }, 5000)
                }
            }
        },

        watch: {
            'configuration.urlKey'(newV) {
                this.$store.commit('setPxFcConfiguration', newV);
            },

            'imageType'(newV) {
                this.fullScreenImageType = newV;
            },

            'isLandscape'(newV) {
                if (newV) {
                    this.openSliderZoom();
                } else {
                    this.closeSliderZoom();
                }
            }
        },

        ready() {
            if (this.configuration.techData) {
                // Substitute base product's data with selected configuration's ones
                this.technicalSpecItems = this.configuration.techData.techSpecsTable;
            }

            // ignore social icons
            jQuery('body a:not([class^="fsl"],[class^="button"])').on('click', (e) => {
                this.clearForm();
                var target = e.currentTarget;

                if (!jQuery(target).parents('#full-configurator').length) {
                    this.redirectUrl = target.href;
                    this.showPopup = true;
                    e.preventDefault();
                }
            });

            jQuery('form').on('submit', (e) => {
                e.preventDefault();
                this.form = e.currentTarget;
                var input = jQuery(this.form).find('input');
                if (input.hasClass('my-saved-cars-name')) {
                    this.discardAndRedirect();
                } else if (!input.hasClass('valid')) {
                    this.clearForm();
                } else {
                    this.showPopup = true;
                }
            });

            this.isLandscape = this.getIsLandscape();

            jQuery(window).on('resize', () => {
                this.isLandscape = this.getIsLandscape();
            });

            this.$store.commit('setSelectedCar', this.product.id);

            this.$store.commit('setConfigurableProductSku', this.configurableSku);
            this.$store.commit('setProductPrice', this.basePrice);
            this.$store.commit('setBrand', this.brand);
            this.$store.commit('setCurrentCategory', this.category);
            this.$store.commit('setProductCategory', this.category);
            this.$store.commit('setPxFcConfiguration', this.configuration.urlKey);
            this.$root.$refs.financeQuote = this.$refs.financeQuote;

            this.$nextTick(() => {
                this.sendProductDetails();
            });

            this.referrerUrl = document.referrer;
        },

        components: {
            appAccordionGroup,
            appAccordion,
            appModal,
            appSlider,
            appFeaturesCarouselList,
            appOptions,
            appAccessories,
            appSummary,
            appFullConfiguratorFinanceQuote,
            appTopFinanceQuote,
            appProductTopContainer,
            appCarouselImages,
            appImageZoom,
            appSubSectionMenuItem
        }
    });
</script>
