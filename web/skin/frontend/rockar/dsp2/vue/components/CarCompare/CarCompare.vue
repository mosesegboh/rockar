<template>
    <div id="car-compare-component" v-show="showCarCompare">
        <div class="toggle" :class="{ 'active': active }">
            <div class="toggle-button" @click="toggle()">
                <div class="icon"></div>
                <span>{{ active ? 'Close' : 'Open'  | translate }}</span>
            </div>
        </div>
        <div id="car-compare-wrapper" :class="{ 'active': active }">
            <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

            <div v-for="(index, item) in compareData.items" class="save-modal-wrapper" :key="index">
                <app-compare-save-car
                    :car-id="item.id"
                    :customer-name="customerName"
                    :product-name="item.name"
                    :product-title="item.title"
                    :product-subtitle="item.bodystyle"
                    :my-account-url="myAccountUrl"
                    :is-in-wishlist.sync="item.isSaved"
                    :is-ajax-request="isAjaxRequest"
                    :save-wishlist-url="item.saveUrl"
                    :remove-from-wishlist-url="item.removeFromWishlistUrl"
                    :image="item.image"
                    :ajax-loading.sync="ajaxLoading"
                ></app-compare-save-car>
            </div>

            <div class="car-compare">
                <div class="header">
                    <div class="selector">
                        <div class="selector-column left-column">
                            <div class="title">
                                <p>
                                    <span class="heading">{{ 'Compare List' | translate }}</span>
                                    <span class="clear" @click="openClearAllPopup()">{{ 'Clear All' | translate }}</span>
                                </p>
                            </div>
                        </div>
                        <div v-for="(index, item) in compareData.items" class="car-column" :key="index">
                            <div class="img-block">
                                <app-offer-tags
                                    :offer-tag-data="item.compare"
                                >
                                </app-offer-tags>
                                <a :href="item.productUrl" class="img-link">
                                    <img :src="item.image" :alt="item.title" />
                                </a>
                                <div class="remove-car">
                                    <a href="javascript:void(0)" @click="remove(item.removeUrl, item.sku)">
                                        <span class="close-icon"></span>
                                    </a>
                                </div>
                            </div>
                            <a :href="item.productUrl" class="title">{{ getProductTitle(item) }}</a>
                        </div>
                        <div class="add-car" v-if="compareData.canAdd"></div>
                        <span class="clear" @click="openClearAllPopup()">{{ 'Clear All' | translate }}</span>
                    </div>
                    <div class="choices">
                        <div class="step-back-column left-column">
                        </div>
                        <div v-for="(index, item) in compareData.items" class="car-column" :key="index">
                            <button v-if="item.productUrl" type="button" class="button dsp2-money" @click="setLocation(item.productUrl)">{{ 'Proceed to checkout' | translate}}</button>
                            <div class="save-button" @click="openCarSavePopup(item.id)">
                            <p :class="{'added-to-wishlist': item.isSaved}"><span>{{ 'Add to wishlist' | translate }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="details">
                    <div class="category category-price">
                        <div class="left-column">
                            <p class="category-title">{{ 'Price' | translate }}</p>
                        </div>
                        <div v-for="(index, item) in compareData.items" class="car-column" :key="index">
                            <div class="attribute-value price">
                                <p class="price-total-title">{{ 'Offer Price' | translate }}</p>
                                <p class="price-total" :class="{'price-main': item.cash}">{{ Math.round(item.price) | numberFormat '0,0' true }}</p>
                            </div>
                            <div class="attribute-value price" v-if="!item.cash">
                                <p class="price-per-month-title">{{ 'Per Month' | translate }}</p>
                                <p class="price-per-month price-main">{{ Math.round(item.monthlyPrice) | numberFormat '0,0' true }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="category category-extra">
                        <div class="left-column">
                            <p class="category-title">{{ 'Extra Features' | translate }}</p>
                        </div>
                        <div class="category-body category-body-with-images">
                            <div class="left-column">
                                <div class="category-subtitle-wrapper">
                                    <p class="category-subtitle">{{ 'Options' | translate }}</p>
                                </div>
                            </div>
                            <template v-for="(index, item) in compareData.items">
                                <div class="car-column" :key="index">
                                    <div class="attribute-value">
                                        <img :src="item.exteriorWithImages.image" :alt="this.decodeHtml(item.exteriorWithImages.value)">
                                        <p class="short-title">{{ this.decodeHtml(item.exteriorWithImages.value) }}</p>
                                    </div>
                                    <div class="attribute-value no-border">
                                        <img :src="item.wheelWithImages.image" :alt="this.decodeHtml(item.wheelWithImages.value)">
                                        <p class="short-title">{{ this.decodeHtml(item.wheelWithImages.value) }}</p>
                                    </div>
                                    <div class="interior-image"><img :src="item.interiorImage" :alt="'Interior Image' | translate"></div>
                                    <div class="attribute-value">
                                        <img :src="item.interiorWithImages.image" :alt="this.decodeHtml(item.interiorWithImages.value)">
                                        <p class="short-title">{{ this.decodeHtml(item.interiorWithImages.value) }}</p>
                                    </div>
                                    <div class="attribute-value no-border">
                                        <template v-if="item.trimFinisherWithImages">
                                            <img :src="item.trimFinisherWithImages.image" :alt="this.decodeHtml(item.trimFinisherWithImages.value)" v-if="item.trimFinisherWithImages.image">
                                            <p class="short-title">{{ this.decodeHtml(item.trimFinisherWithImages.value) }}</p>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="category-body">
                            <div class="left-column">
                                <div class="category-subtitle-wrapper">
                                    <p class="category-subtitle">{{ 'Line/Packages' | translate }}</p>
                                </div>
                            </div>
                            <template v-for="(index, item) in compareData.items">
                                <div class="car-column" :key="index">
                                    <template v-for="(index, extra) in item.linePackages">
                                        <div class="attribute-value" :key="index">
                                            <p class="short-title">{{ this.decodeHtml(extra.label) }}</p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div class="category-body">
                            <div class="left-column">
                                <div class="category-subtitle-wrapper">
                                    <p class="category-subtitle">{{ 'Extra Options' | translate }}</p>
                                </div>
                            </div>
                            <template v-for="(index, item) in compareData.items">
                                <div class="car-column" :key="index">
                                    <template v-for="(index, extra) in item.extraOptions">
                                        <div class="attribute-value" :key="index">
                                            <p class="short-title">{{ this.decodeHtml(extra.label) }}</p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="category category-technical" v-if="compareData.items.length > 0">
                        <div class="left-column">
                            <p class="category-title">{{ 'Technical Features' | translate }}</p>
                        </div>
                        <div class="category-body">
                            <div class="left-column">
                                <div v-for="(index, specs) in compareData.items[0].technicalSpecs" :key="index" class="category-subtitle-wrapper">
                                    <p class="category-subtitle">{{ specs.title }}</p>
                                    <div class="attribute-title-wrapper">
                                        <div v-for="(index, spec) in specs.items" :key="index" class="attribute-title">
                                            <p>{{ spec.title }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <template v-for="(index, item) in compareData.items">
                                <div class="car-column" :key="index">
                                    <div v-for="(index, specs) in item.technicalSpecs" :key="index">
                                        <p class="category-subtitle schrodingers-title">{{ specs.title }}</p>
                                        <div v-for="(index, spec) in specs.items" :key="index" class="attribute-value">
                                            <p>{{ this.decodeHtml(spec.value) || '&nbsp;' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="category category-standard">
                        <div class="left-column">
                            <p class="category-title">{{ 'Standard Features' | translate }}</p>
                        </div>
                        <template v-for="(index, item) in compareData.items">
                            <div class="car-column" :key="index">
                                <template v-for="(index, feature) in item.standardFeatures" track-by="$index">
                                    <div class="attribute-value" :key="index">
                                        <p class="short-title" :title="feature">{{ this.decodeHtml(feature) }}</p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mobile-wrapper">
                <div class="header">
                    <p>{{ 'Compare list' | translate }}</p>
                    <span class="clear" @click="openClearAllPopup()">{{ 'Clear All' | translate }}</span>
                </div>
                <div class="car-compare-mobile">
                    <div class="car-compare-mobile-top">
                        <div v-for="(index, item) in compareData.items" class="car-page" :key="index">
                            <div class="selector">
                                <div class="top-navigation">
                                    <p class="compare-length">{{ index + 1 }}/{{ carCompareLength }}</p>
                                </div>
                                <div class="img-block">
                                    <app-offer-tags
                                        :offer-tag-data="item.compare"
                                    >
                                    </app-offer-tags>
                                    <img :src="item.image" :alt="item.title" class="car-image"/>
                                </div>
                                <a :href="item.productUrl" class="title">{{ getProductTitle(item) }}</a>
                                <div class="choices">
                                    <div class="remove-button" @click="remove(item.removeUrl, item.sku)">
                                        <span class="remove-icon"></span>
                                    </div>
                                    <div class="checkout-button" v-if="item.productUrl" @click="setLocation(item.productUrl)">
                                        <span class="checkout-icon"></span>
                                    </div>
                                    <div class="save-button" @click="openCarSavePopup(item.id)">
                                        <span class="save-icon" :class="{ 'added-to-wishlist': item.isSaved }"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="car-compare-mobile-bottom-titles">
                        <div class="titles title price">{{ 'Price' | translate }}</div>
                        <div class="titles title extra">{{ 'Extra Features' | translate }}</div>
                        <div class="titles subtitle options">{{ 'Options' | translate }}</div>
                        <div class="titles subtitle linePackages">{{ 'Line/Packages' | translate }}</div>
                        <div class="titles subtitle extraOptions">{{ 'Extra Options' | translate }}</div>
                        <div class="titles title technical">{{ 'Technical Features' | translate }}</div>
                        <div class="technical-attributes" v-if="compareData.items.length > 0">
                            <div v-for="(index, specs) in compareData.items[0].technicalSpecs" :key="index">
                                <div class="titles attribute-title">{{ specs.title }}</div>
                                <div>
                                    <template v-for="(index, spec) in specs.items">
                                        <div class="titles attribute-subtitle" :key="index">{{ spec.title }}</div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="titles title standard">{{ 'Standard Features' | translate }}</div>
                    </div>

                    <div class="car-compare-mobile-bottom">
                        <div v-for="(index, item) in compareData.items" class="car-page" :key="index">
                            <div class="details">
                                <div class="category category-price">
                                    <div class="schrodingers-title"><p>{{ 'Price' | translate }}</p></div>
                                    <div class="attribute-value price" v-if="!item.cash">
                                        <p>{{ 'Per Month' | translate }}</p>
                                        <p class="price-per-month price-main">{{ Math.round(item.monthlyPrice) | numberFormat '0,0' true }}</p>
                                    </div>
                                    <div class="attribute-value price">
                                        <p>{{ 'Offer Price' | translate }}</p>
                                        <p class="price-total" :class="{ 'price-main': item.cash }">{{ Math.round(item.price) | numberFormat '0,0' true }}</p>
                                    </div>
                                </div>

                                <div class="category category-extra">
                                    <div class="schrodingers-title"><p>{{ 'Extra Features' | translate }}</p></div>
                                    <div class="category-body category-body-with-images">
                                        <div class="schrodingers-title"><p>{{ 'Options' | translate }}</p></div>
                                        <div class="car-column" :key="index">
                                            <div class="attribute-value">
                                                <img :src="item.exteriorWithImages.image" :alt="this.decodeHtml(item.exteriorWithImages.value)">
                                                <p class="short-title">{{ this.decodeHtml(item.exteriorWithImages.value) }}</p>
                                            </div>
                                            <div class="attribute-value">
                                                <img :src="item.wheelWithImages.image" :alt="this.decodeHtml(item.wheelWithImages.value)">
                                                <p class="short-title">{{ this.decodeHtml(item.wheelWithImages.value) }}</p>
                                            </div>
                                            <div class="interior-image"><img :src="item.interiorImage" :alt="'Interior Image' | translate"></div>
                                            <div class="attribute-value no-border">
                                                <img :src="item.interiorWithImages.image" :alt="this.decodeHtml(item.interiorWithImages.value)">
                                                <p class="short-title">{{ this.decodeHtml(item.interiorWithImages.value) }}</p>
                                            </div>
                                            <div class="attribute-value no-border">
                                                <template v-if="item.trimFinisherWithImages">
                                                    <img :src="item.trimFinisherWithImages.image" :alt="this.decodeHtml(item.trimFinisherWithImages.value)">
                                                    <p class="short-title">{{ this.decodeHtml(item.trimFinisherWithImages.value) }}</p>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="category-body category-body-line-packages">
                                        <div class="schrodingers-title"><p>{{ 'Line/Packages' | translate }}</p></div>
                                        <div class="car-column">
                                            <template v-for="(index, extra) in item.linePackages">
                                                <div class="attribute-value" :key="index">
                                                    <p>{{ this.decodeHtml(extra.label) }}</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="category-body category-body-extra-options">
                                        <div class="schrodingers-title"><p>{{ 'Extra Options' | translate }}</p></div>
                                        <div class="car-column">
                                            <template v-for="(index, extra) in item.extraOptions">
                                                <div class="attribute-value" :key="index">
                                                    <p>{{ this.decodeHtml(extra.label) }}</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="category category-technical">
                                    <div class="schrodingers-title"><p>{{ 'Technical Features' | translate }}</p></div>
                                    <div class="car-column" :key="index">
                                        <div v-for="(index, specs) in item.technicalSpecs" :key="index">
                                            <p class="category-subtitle schrodingers-title">{{ specs.title }}</p>
                                            <div
                                                class="technical-items"
                                                :class="{ 'm-20': moreThanOne }"
                                                v-for="(index2, spec) in specs.items"
                                                :key="index2"
                                            >
                                                <div class="attribute-title">
                                                    <p class="schrodingers-title">{{ spec.title }}</p>
                                                </div>
                                                <div class="attribute-value">
                                                    <p>{{ this.decodeHtml(spec.value) || '&nbsp;' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="category category-standard">
                                    <div class="schrodingers-title"><p>{{ 'Standard Features' | translate }}</p></div>
                                    <div class="car-column">
                                        <template v-for="(index, feature) in item.standardFeatures" track-by="$index">
                                            <div class="attribute-value" :key="index">
                                                <p :title="feature">{{ this.decodeHtml(feature) }}</p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import appCompareSaveCar from 'dsp2/components/CarCompare/CarCompareSaveCar';
    import appSelect from 'core/components/Elements/Select';
    import appOfferTags from 'dsp2/components/Elements/OfferTags';
    import EventTracker from 'dsp2/mixins/EventTracker';

    export default Vue.extend({
        mixins: [EventTracker],

        components: {
            appCompareSaveCar,
            appSelect,
            appOfferTags
        },

        props: {
            compareDataUrl: {
                required: true,
                type: String
            },

            compareDataProp: {
                required: false,
                type: Object,
                // eslint-disable-next-line
                default: () => { { items: [] } }
            },

            customerName: {
                required: true,
                type: String
            },

            myAccountUrl: {
                required: true,
                type: String
            },

            isAjaxRequest: {
                required: false,
                type: Boolean,
                default: false
            },

            showAsLink: {
                required: false,
                type: Boolean,
                default: false
            },

            addCarImg: {
                required: true,
                type: String
            },

            carFinderUrl: {
                required: true,
                type: String
            },

            youDriveUrl: {
                required: true,
                type: String
            },

            visibility: {
                required: false,
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                compareData: this.compareDataProp,
                active: false,
                saveCarOverlay: false,
                ajaxLoading: false,
                openSave: false,
                slickOptions: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    swipeToSlide: true,
                    focusOnSelect: true,
                    infinite: false,
                    arrows: true,
                    pauseOnFocues: false,
                    pauseOnHover: false,
                    prevArrow: '<div class="slick-nav slick-prev"></div>',
                    nextArrow: '<div class="slick-nav slick-next"></div>',
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: {
                                slidesToShow: 2
                            }
                        }
                    ]
                },
                mobileSliderElementTop: null,
                mobileSliderElementBottom: null,
                clientWidth: document.documentElement.clientWidth
            }
        },

        watch: {
            compareData() {
                this.mobileSliderElementBottom.slick('setOption', {}, true);
                this.mobileSliderElementTop.slick('setOption', {}, true);
                this.initTitles();
            },

            /**
             * Reacts to screen width changes
             * Reinit slick slider after desktop and tablet
             */
            clientWidth(newVal, preVal) {
                if (newVal < 1024) {
                    if (newVal < 426 && preVal >= 426) {
                        this.mobileSliderElementBottom.slick('reinit');
                        this.mobileSliderElementBottom.on('beforeChange', (e, slick, currentSlide, nextSlide) => {
                            if (nextSlide < 2) {
                                this.initTitles([nextSlide, nextSlide + 1]);
                            }
                        });
                    } else if (preVal >= 1024) {
                        this.mobileSliderElementBottom.slick('reinit');
                    }

                    this.initTitles();
                }
            },

            active(newVal) {
                /**
                 * Fire event for tracking purposes on expanding Car Compare overlay
                 */
                if (newVal && this.$parent.selected !== 'quote') {
                    this.fireEventForTracking(
                        this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                        `${this.getEventConstants().EVENTRACKERVALUES.COMPAREEXPAND}${this.eventTrackerVehicles}`
                    );
                }
            }
        },

        ready() {
            if (!this.compareData) {
                this.ajaxCompareData();
            }

            this.initTopSlider();
            this.initBottomSlider();

            window.addEventListener('resize', this.handleResize);

            if (window.sessionStorage.getItem('CarFinder::redirectToResultsCompare')) {
                this.active = true;
                window.sessionStorage.removeItem('CarFinder::redirectToResultsCompare');
            }
        },

        beforeDestroy() {
            window.removeEventListener('resize', this.handleResize);
        },

        methods: {
            decodeHtml(str) {
                return str.replace(/&#(\d+);/g, (match, dec) => String.fromCharCode(dec));
            },

            /**
             * Places centered static(non draggable) titles on top of draggable individual (for each column) once
             * Makes columns of same content type equal height
             */
            initTitles(range = [0, 1]) {
                const parent = jQuery(this.$el);

                this.setColumnHeights('.category-body-with-images', range);
                this.setColumnHeights('.category-body-line-packages', range);
                this.setColumnHeights('.category-body-extra-options', range);

                parent.find('.titles.title.price').offset(parent.find('.category-price > .schrodingers-title').offset())
                parent.find('.titles.title.extra').offset(parent.find('.category-extra > .schrodingers-title').offset());
                parent.find('.titles.subtitle.options').offset(parent.find('.category-body-with-images > .schrodingers-title').offset());
                parent.find('.titles.subtitle.linePackages').offset(parent.find('.category-body-line-packages > .schrodingers-title').offset());
                parent.find('.titles.subtitle.extraOptions').offset(parent.find('.category-body-extra-options > .schrodingers-title').offset());
                parent.find('.titles.title.technical').offset(parent.find('.category-technical > .schrodingers-title').offset());
                parent.find('.titles.title.standard').offset(parent.find('.category-standard > .schrodingers-title').offset());

                this.setAttributeTitles('.category-subtitle', '.titles.attribute-title');
                this.setAttributeTitles('.attribute-title', '.titles.attribute-subtitle');
            },

            /**
            * Set each compare date group equal to height of longest one
            */
            setColumnHeights(classname, range) {
                if (this.clientWidth < 1024 && this.clientWidth >= 426) {
                    range = [0, 1, 2];
                }

                const parent = jQuery(this.$el).find('.car-compare-mobile-bottom');
                let height = 0;

                parent.find(classname).each((i, obj) => parent.find(obj).height('auto'));

                parent.find(classname).each((i, obj) => {
                    if (range.includes(i) && height < parent.find(obj).height()) {
                        height = parent.find(obj).height();
                    }
                });

                parent.find(classname).each((i, obj) => parent.find(obj).height(height));
            },

            /**
             * Places centered static(non draggable) technical data sub-subtitles
             * on top of draggable individual (for each column) once
             */
            setAttributeTitles(positions, titles) {
                const parent = jQuery(this.$el).find('.car-compare-mobile-bottom');
                const a = parent.find('.category-technical').first();
                const b = a.find(positions);
                const c = jQuery(this.$el).find('.technical-attributes').find(titles);
                c.each((i, obj) => jQuery(this.$el).find(obj).offset(b.eq(i).offset()));
            },

            initTopSlider() {
                this.mobileSliderElementTop = jQuery(this.$el).find('.car-compare-mobile-top');
                this.mobileSliderElementTop.slick(Object.assign({}, this.slickOptions, { asNavFor: '.car-compare-mobile-bottom' }));
            },

            initBottomSlider() {
                this.mobileSliderElementBottom = jQuery(this.$el).find('.car-compare-mobile-bottom');
                this.mobileSliderElementBottom.slick(Object.assign({}, this.slickOptions, { asNavFor: '.car-compare-mobile-top', arrows: false }));
                this.mobileSliderElementBottom.on('beforeChange', (e, slick, currentSlide, nextSlide) => {
                    if (nextSlide < 2) {
                        this.initTitles([nextSlide, nextSlide + 1]);
                    }
                });
            },

            toggle() {
                this.active = !this.active;

                if (this.active) {
                    this.initTitles();
                    jQuery('body').css({ overflow: 'hidden' });
                } else {
                    jQuery('body').css({ overflow: 'visible' });
                }

                if (this.clientWidth >= 1024) {
                    this.$dispatch('ProductPageOverlay::carCompareActive', this.active);
                }
            },

            remove(url, sku = null) {
                this.ajaxLoading = true;

                this.$http({
                    url,
                    method: 'GET'
                }).then(
                    () => {
                        if (sku) {
                            this.$dispatch('Main::toggleInCompareListPOD', sku);
                        } else {
                            this.$dispatch('Main::clearCompare');
                        }

                        this.ajaxCompareData();
                    },
                    this.ajaxRemoveFail
                );

                return false;
            },

            ajaxRemoveFail(error) {
                this.ajaxLoading = false;
                console.error(error);
            },

            ajaxCompareData() {
                this.ajaxLoading = true;

                this.$http({
                    url: this.compareDataUrl,
                    method: 'GET'
                }).then(this.ajaxCompareDataSuccess, this.ajaxCompareDataFail);
            },

            ajaxCompareDataSuccess(data) {
                this.compareData = JSON.parse(data.data);
                this.ajaxLoading = false;
            },

            ajaxCompareDataFail(error) {
                console.error(error);
                this.ajaxLoading = false;
            },

            setLocation(url) {
                window.location.href = url;
            },

            openCarSavePopup(carId) {
                this.$broadcast('openSaveTrigger', carId);
            },

            standardFeatures(item) {
                return JSON.parse(item.standardFeatures);
            },

            handleResize() {
                this.clientWidth = document.documentElement.clientWidth;
            },

            getProductTitle(item) {
                return `${item.title} ${item.subTitle}`;
            },

            openClearAllPopup() {
                this.$parent.$broadcast('CarCompare::openClearPopup');
            }
        },

        computed: {
            eventTrackerVehicles() {
                return this.compareData.items.map(vehicles => vehicles.name);
            },

            showCarCompare() {
                let show = false;

                if (this.compareData) {
                    show = Object.keys(this.compareData.items).length > 0 && (this.visibility || this.carFinderStep > 0);

                    if (!show) {
                        this.active = false;
                        jQuery('body').css({ overflow: 'visible' });
                    }
                }

                this.$dispatch('Main::carCompareVisible', show);

                return show;
            },

            carCompareLength() {
                return Object.keys(this.compareData.items).length;
            },

            carFinderStep() {
                return this.$store.state.carFinder.step;
            },

            moreThanOne() {
                return Object.keys(this.compareData.items).length > 1;
            }
        },

        events: {
            'CarCompare::updateCompareData'() {
                this.ajaxCompareData();
            },

            'CarCompare::clearAll'(url) {
                this.remove(url);
            }
        }
    });
</script>
