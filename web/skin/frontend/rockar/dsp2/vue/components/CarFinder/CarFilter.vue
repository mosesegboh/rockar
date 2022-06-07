<template>
    <div id="car-filter"
         class="car-filter-wrapper col-3 col-md-4"
         v-show="$parent.steps[$parent.currentStep] === 'carFilter' && (showMobileMenu || modelDeselected.show)"
    >
        <div v-show="$parent.steps[$parent.currentStep] === 'carFilter' && (showMobileMenu || modelDeselected.show)">
            <div class="general-preloader" v-show="showLoading">
                <div class="show-loading"></div>
            </div>

            <app-part-exchange-block
                :expire-date="expireDate"
                :temp-px="tempPx"
                :saved-px="savedPx"
                :est-value-disclaimer="estValueDisclaimer"
                v-ref:part-exchange-block
            ></app-part-exchange-block>

            <div class="car-filter-list" v-el:car-filter-list>
                <div class="filters-list-inner-container">
                    <template v-for="(index, section) in getMenuFilters()">
                        <div class="car-filter-item" :class="'car-filter-' + section.code" :key="index">
                            <input type="checkbox"
                                   name="car-filter-open"
                                   class="car-filter-open"
                                   :id="'input-'+section.code"
                                   :checked="isFilterOpen(section.code)"
                                   :value="section.code"
                                   @click="toggleFilter(section.code)"
                            >
                            <label :for="'input-' + section.code"
                                   class="car-filter-title">
                                {{ getSectionTitle(section) }}
                            </label>
                            <div class="car-filter-blocks" :class="{'car-filter-opened': isFilterOpen(section.code), 'car-filter-swatch': section.frontend_display_type === 1}">
                                <app-scrollbox
                                    v-if="isFilterOpen(section.code) && (showMobileMenu || modelDeselected.show)"
                                    scrollbox-width="370px"
                                    :allow-horizontal="false"
                                >
                                    <div class="car-filter-block car-filter-slider-block" v-if="section.frontend_display_type === 3">
                                        <app-range-slider
                                            :min="section.range_slider_data.min"
                                            :max="section.range_slider_data.max"
                                            :step="section.range_slider_data.step"
                                            :active="[section.range_slider_data.current.min, section.range_slider_data.current.max]"
                                            :active-on-slide="true"
                                            :identifier="section.code"
                                            :range="true"
                                            :is-custom-event-slide-identifier="true"
                                            custom-event="updateRangeSliderData"
                                            custom-event-slide="updateRangeSliderDataSlide"
                                            :custom-event-slide-use-object="true"
                                        ></app-range-slider>
                                        <div class="range-value-container" v-if="section.code != 'price'">
                                            <div class="range-values range-min">
                                                <div class="value-wrapper">
                                                    {{section.range_slider_data.current.min}}
                                                </div>
                                            </div>
                                            <div class="range-values range-max">
                                                <div class="value-wrapper">
                                                    {{section.range_slider_data.current.max}}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="range-value-container" v-if="section.code == 'price'">
                                            <div class="range-values range-min">
                                                <div class="value-wrapper">
                                                    {{section.range_slider_data.current.min | numberFormat}}
                                                </div>
                                            </div>
                                            <div class="range-values range-max">
                                                <div class="value-wrapper">
                                                    {{section.range_slider_data.current.max | numberFormat}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Switchers -->
                                    <div class="car-filter-block" v-if="section.frontend_display_type === 4">
                                        <div class="car-filter-switcher-blocks"
                                             v-for="(index, filter) in getOfferTagFilters(section.options)" :key="index">
                                            <div class="car-filter-switcher-block">
                                                <label class="switch">
                                                    <input
                                                        type="checkbox"
                                                        :id="getFilterOptionDomId(filter)"
                                                        :true-value="true"
                                                        :false-value="false"
                                                        :checked="filter.state"
                                                        v-model="filter.state"
                                                        v-on:change="updateFilters(false, section.code)"
                                                    />
                                                    <span class="slider"></span>
                                                </label>
                                                <p>{{ filter.title }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <template v-for="(index, filter) in section.options">
                                        <div class="car-filter-block"
                                            v-if="section.frontend_display_type !== 4 && (!(!filter.state && (filter.count <= 0))) || (filter.count <= 0 && filter.isValidWithOutStock)"
                                            :key="index"
                                        >
                                            <!-- If not radio behaviour -->
                                            <input v-if="section.frontend_display_type !== 2"
                                               type="checkbox"
                                               :id="getFilterOptionDomId(filter)"
                                               :disabled="!((filter.count > 0) || (filter.count <= 0 && filter.isValidWithOutStock))"
                                               :true-value="true"
                                               :false-value="false"
                                               :checked="filter.state"
                                               v-model="filter.state"
                                               v-on:change="updateFilters(false, section.code)"
                                            />
                                            <!-- Radio input behaviour -->
                                            <input v-else
                                               type="checkbox"
                                               :name="section.code"
                                               :id="getFilterOptionDomId(filter)"
                                               :disabled="!((filter.count > 0) || (filter.count <= 0 && filter.isValidWithOutStock))"
                                               @click="toggleOneFilterValue(section, filter)"
                                               :true-value="true"
                                               :false-value="false"
                                               :checked="filter.state"
                                            />
                                            <label :for="getFilterOptionDomId(filter)"
                                                   v-if="(!(!filter.state && (filter.count <= 0))) || (filter.count <= 0 && filter.isValidWithOutStock)"
                                                   :class="{ 'car-filter-swatch-label': section.frontend_display_type === 1 }">
                                                <!-- Not swatches -->
                                                <div v-if="section.frontend_display_type !== 1">
                                                    <span></span>
                                                    {{ filter.title }}
                                                </div>
                                                <!-- Swatches (checkboxes with images) -->
                                                <!-- @todo: Get replace 'model-wrapper' with something more generic -->
                                                <div class="model-wrapper" v-else>
                                                    <div class="model-data">
                                                        <div class="model-image">
                                                            <img :src="filter.image"/>
                                                            <span></span>
                                                        </div>
                                                        <div class="title">{{ filter.title }}</div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </template>
                                </app-scrollbox>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="car-filter-buttons">
                    <button class="button dsp2-money" @click="applyMobileFilters()">
                        <span class="title">{{ 'View results' | translate}}</span>
                    </button>
                    <div class="reset-all-filters" @click.prevent="openInModal('resetFilterModal')">
                        <p>{{ 'Reset Filters' | translate }}</p>
                    </div>
                </div>
            </div>
            <app-modal :show.sync="updateError" class-name="simple-popup">
                <div slot="content" class="valuation-result">
                    <h2>{{ 'Something went wrong!' | translate }}</h2>
                    <p>{{ 'Please try again later or contact administrator' | translate }}</p>
                    <div class="valuation-result-continue row">
                        <div class="valuation-result-continue-block col-12">
                            <button type="button" name="button" class="button button-empty"
                                    @click.prevent="updateError = false">{{ 'Back' | translate }}
                            </button>
                        </div>
                    </div>
                </div>
            </app-modal>

            <app-modal :show.sync="modelDeselected.show" class-name="simple-popup">
                <div slot="content" class="valuation-result">
                    <h2>{{ 'Model was deselected!' | translate }}</h2>
                    <p v-if="modelDeselected.models.length > 1">{{ 'Your selected attribute' | translate }}
                        <template v-if="modelDeselected.filter">“{{ modelDeselected.filter }}”</template>
                        {{ 'is not available for models' | translate }}
                        “{{ modelDeselected.models.join(', ') }}”,
                        {{ 'which have been deselected' | translate }}
                    </p>
                    <p v-else>{{ 'Your selected attribute' | translate }}
                        <template v-if="modelDeselected.filter">“{{ modelDeselected.filter }}”</template>
                        {{ 'is not available for model' | translate }}
                        “{{ modelDeselected.models[0] }}”,
                        {{ 'which has been deselected' | translate }}
                    </p>
                    <div class="valuation-result-continue row">
                        <div class="valuation-result-continue-block col-12">
                            <button type="button" name="button" class="button"
                                    @click.prevent="modelDeselected.show = false">{{ 'Ok' | translate }}
                            </button>
                        </div>
                    </div>
                </div>
            </app-modal>

            <div class="row reset-filters-row">
                <app-modal v-ref:reset-filter-modal class-name="simple-popup">
                    <div slot="content">
                        <p class="reset-filter-title">{{ resetFilterTitleText }}</p>
                        <p>{{ 'Are you sure you want to reset all your filters?' | translate }}</p>
                        <div class="reset-filter-buttons">
                            <button type="button" name="button" class="button dsp2-outline"
                                    @click.prevent="resetCarFinderFilters(true)">
                                <span>{{ resetFilterApproveText }}</span>
                            </button>
                            <button type="button" name="button" class="button dsp2-money"
                                    @click.prevent="resetCarFinderFilters(false)">
                                <span>{{ resetFilterRefuseText }}</span>
                            </button>
                        </div>
                    </div>
                </app-modal>
            </div>
        </div>
    </div>
</template>
<script>
    import coreCarFilter from 'core/components/CarFinder/CarFilter';
    import appRangeSlider from 'core/components/Elements/RangeSlider';
    import appFinanceFilterMenu from 'dsp2/components/CarFinder/FinanceFilterMenu';
    import appFinanceFilter from 'dsp2/components/CarFinder/FinanceFilter';
    import translateString from 'core/filters/Translate';
    import appCarCompareLink from 'core/components/CarFinder/CarCompareLink';
    import appModal from 'core/components/Elements/Modal';
    import appScrollbox from 'dsp2/components/Elements/Scrollbox';
    import appPartExchangeBlock from 'dsp2/components/PartExchange/PartExchangeBlock/PartExchangeBlock';

    export default coreCarFilter.extend({

        data() {
            return {
                productCount: '',
                productCountYouDrive: '',
                ajaxLoading: false,
                ajaxLoadingStack: [false],
                updateFiltersObject: false,
                updateError: false,
                modelDeselected: { show: false, models: [], filter: false },
                appendTitle: 0,
                defaultTitle: this.$parent.title,
                carouselAttributes: ['model', 'range'],
                financeFilters: {},
                selectedFilters: [],
                canChangeTitle: false,
                showMobileMenu: false,
                windowWidth: 0,
                stepBackClicked: false,
                mapping: {
                    'bodystyle': this.translateString('Body Type'),
                    'fuel_type': this.translateString('Fuel Type'),
                    'visible_in': this.translateString('visible'),
                    'variant': this.translateString('Variant')
                },
                attributeFiltersChecked: '',
                filtersBlacklist: [
                    'black_event_stock_filter',
                    'bmw_model_matrix_carousel',
                    'bmw_series'
                ],
                currentSection: false,
                openFilterMap: {}, // Filter code to state map of opened filters
                matchingVehiclesMessage: 'Vehicles matching your search',
                modelShortestLeadTime: {},
                uniqueCounter: 0,
                lastQueryState: '',
                lastPageUrl: ''
            }
        },

        props: {
            estValueDisclaimer: {
                required: true,
                type: String
            },

            expireDate: {
                required: false,
                type: String,
                default: ''
            },

            tempPx: {
                required: false,
                type: Object,
                default() {
                    return {
                        vrm: '',
                        mileage: 0,
                        capId: 0,
                        model: '',
                        title: '',
                        derivative: '',
                        registrationYear: ''
                    };
                }
            },

            savedPx: {
                required: false,
                type: Object,
                default() {
                    return {
                        part_exchange_value: '',
                        cap_extended: {
                            product_name: ''
                        }
                    };
                }
            },

            isPxRemoved: {
                required: false,
                type: Boolean,
                default: false
            }
        },

        methods: {
            translateString,

            getPrefixes() {
                return ['bmw'];
            },

            resetFilters() {
                this.canChangeTitle = false;
                this.filters.forEach((section) => {
                    section.options.forEach((filter) => {
                        // if step back was clicked
                        if (this.stepBackClicked) {
                            filter.state = [`${this.getPrefixes()[0]}_model_matrix_carousel`].includes(section.code) && filter.state;
                            return undefined;
                        }
                        // default logic
                        filter.state = false;
                    });

                    if (section.range_slider_data !== undefined) {
                        section.range_slider_data.current = section.range_slider_data.default;
                    }
                });
                this.changeTitle();
                this.stepBackClicked = false;
            },

            loadNextPage(pageUrl, string, scrollTop = true) {
                if (!this.ajaxLoading && this.ProductGrid.isItOKToLoadNextPage() && !this.ProductGrid.hideProductGrid) {
                    /**
                     * Scroll to the loader if a new page is being loaded
                     * Otherwise scroll to the top if page is reset by filters
                     */
                    if (pageUrl !== this.ProductGrid.productsList.page.currentPageResetUrl) {
                        this.ProductGrid.scrollToLoaderElement();
                    } else {
                        if (scrollTop) {
                            window.scrollTo(0, 0);
                        }
                    }

                    this.ProductGrid.productsListEmptyMessage = false;
                    this.updateFilters(pageUrl);
                    this.changeTitle(string);
                }
            },

            applyMobileFilters() {
                this.showMobileMenu = !this.showMobileMenu;
                this.$root.$refs.productGrid.hideProductGrid = false;
                this.getFinanceFilter.applyFilter();
                jQuery('body').css('overflow', 'auto');
            },

            /**
             *  This method does the same thing as reset filters except without resetting finance
             */
            resetFiltersExceptFinance() {
                this.canChangeTitle = false;
                this.filters.forEach((section) => {
                    section.options.forEach((filter) => {
                        // if step back was clicked
                        if (this.stepBackClicked) {
                            filter.state = [`${this.getPrefixes()[0]}_model_matrix_carousel`].includes(section.code) && filter.state;
                            return undefined;
                        }
                        // default logic
                        filter.state = false;
                    });
                });
                this.changeTitle();
                this.stepBackClicked = false;
            },

            /**
             * Gets whether a section code is a model filter
             */
            isModelFilter(sectionCode) {
                const modelsWithPrefixes = [];
                this.getPrefixes().forEach(
                    prefix => modelsWithPrefixes.push(`${prefix}_model_matrix_carousel`)
                );
                return modelsWithPrefixes.includes(sectionCode);
            },

            menuVisibility() {
                if (this.$root.isSafari()) {
                    this.showMobileMenu = !jQuery('.mobile-only').is(':visible');
                } else {
                    const width = window.innerWidth;
                    this.showMobileMenu = width > this.$root.RESPONSIVE_BREAKPOINTS.MIN_MOBILE || this.$root.$refs.productGrid.hideProductGrid;
                    this.windowWidth = width;
                }
            },

            /**
             * rewritten to hide empty filters and reorder filters
             */
            getMenuFilters() {
                const result = [];

                this.filters.map(filter => {
                    // If not in blacklist and options exist then display in FE
                    const count = filter.options.reduce((val, option) => option.count + val, 0);

                    if (this.filtersBlacklist.indexOf(filter.code) === -1 && count > 0) {
                        result.push(filter);
                    }
                });

                result.sort((obj1, obj2) => (parseInt(obj1.position) < parseInt(obj2.position) ? -1 : 1));

                return result;
            },

            getOfferTagFilters(filters) {
                const result = filters
                    .slice()
                    .filter(filter => filter.count > 0 || filter.count <= 0 && filter.isValidWithOutStock);

                result.sort((obj1, obj2) => (obj1.priority - obj2.priority || obj1.rule_id - obj2.rule_id));

                return result.slice(0, 5);
            },

            updateFilters(pageUrl = false, filterName = false, sort = false) {
                const groupId = this.$root.$refs.financeFilter.activePayment.group_id;
                const filterData = this.$root.$refs.financeFilter.financeGroupsParams[groupId];

                if (filterName) {
                    this.modelDeselected.filter = filterName;
                }

                const hasVrm = this.tempPx ? (!!this.tempPx.vrm && !!this.savedPx.vrm) : false;
                const hasSavedPx = this.financeFilter ? this.financeFilter.showPxInfoBlock && hasVrm : hasVrm;

                const data = { isAjax: 1, financeFilters: filterData, hasSavedPx };

                this.filters.forEach((section) => {
                    const code = section.code;
                    const range = section.range_slider_data;
                    data[code] = [];

                    if (range !== undefined) {
                        data[code].push([range.current.min, range.current.max]);
                    }

                    section.options.forEach((filter) => {
                        if (filter.state) {
                            data[code].push(parseInt(filter.id));
                        }
                    });
                });

                const queryData = JSON.stringify(data);

                const _url = pageUrl === false ? this.filtersUpdateUrl : pageUrl;

                if (this.lastQueryState !== queryData || this.lastPageUrl !== _url || sort) {
                    this.ajaxLoadingStack.push(true);
                    this.ProductGrid.ajaxGlobalLoading = true;
                    this.$http({
                        url: _url,
                        method: 'POST',
                        emulateJSON: true,
                        data
                    }).then(this.updateFiltersSuccess, this.updateFiltersFail);

                    this.lastQueryState = queryData;
                    this.lastPageUrl = _url;
                }
            },

            updateFiltersSuccess(resp) {
                this.ProductGrid.ajaxGlobalLoading = false;
                try {
                    const products = JSON.parse(resp.data.products);
                    const filters = JSON.parse(resp.data.filters);

                    this.updateTitle(filters);
                    this.updateFiltersCount(filters);

                    // Update vuex with new filters
                    this.$store.commit('setCarFilters', filters);

                    this.ProductGrid.productsUpdated(products);
                    if (products.youBuildBlockMessage) {
                        this.ProductGrid.youBuildBlockMessage = products.youBuildBlockMessage;
                    }

                    if (products.youBuildProductsCount || products.youBuildProductsCount === 0) {
                        this.ProductGrid.youBuildProductsCount = products.youBuildProductsCount;
                    }

                    EventsBus.$emit('CarFinder::UpdateUrl');
                    this.deselectModel();
                    this.changeTitle();

                    this.$dispatch('CarFinder::reRenderCarousel');
                    this.$nextTick(() => {
                        this.ajaxLoadingStack.pop();
                    });
                } catch (e) {
                    this.updateFiltersFail(e);
                    this.ajaxLoadingStack.pop();
                }
            },

            updateFiltersFail(error) {
                console.error('Car Filter:', error);
                this.updateError = true;
                this.ajaxLoadingStack.pop();
            },

            toggleOneFilterValue(section, filter) {
                Object.keys(section.options).forEach((i) => {
                    const option = section.options[i];
                    section.options[i].state = (filter.id === option.id && filter.state !== true);
                });

                this.updateFilters(false, section.code);
            },

            /**
             * Returns status of current filter
             * @param filterCode
             * @returns {Boolean}
             */
            isFilterOpen(filterCode) {
                return !!this.openFilterMap[filterCode];
            },

            /**
             * Toggle filter off if same filter is clicked again.
             *
             * @param filterCode
             */
            toggleFilter(filterCode) {
                const newOpenFilterMap = {};

                Object.entries(this.openFilterMap).forEach(([key, value]) => {
                    newOpenFilterMap[key] = value;
                });

                newOpenFilterMap[filterCode] = !this.openFilterMap[filterCode];
                this.openFilterMap = newOpenFilterMap;
            },

            resetCarFinderFilters(status) {
                if (status) {
                    this.$broadcast('FinanceFilterMenu::resetCheckboxes');
                    this.openFilter = '';
                    this.$parent.softResetFilters();
                }

                this.$refs.resetFilterModal.show = false;
                this.$root.$refs.productGrid.hideProductGrid = true;
            }
        },

        created() {
            EventsBus.$on('CarFinder::update', () => {
                this.getFinanceFilter.applyFilter();
            });

            this.openFilterMap = this.getMenuFilters().reduce((acc, curr) => {
                acc[curr.code] = true;

                return acc;
            }, {});
        },

        events: {
            'CarFilter::closeFilter'() {
                this.closeFilterPopup();
            }
        },

        computed: {
            showLoading() {
                return this.ajaxLoading || this.ajaxLoadingStack.some(i => i === true);
            },

            resetFilterApproveText() {
                return this.translateString('Yes, reset all my filters');
            },

            resetFilterRefuseText() {
                return this.translateString('No, keep my filters');
            },

            resetFilterTitleText() {
                return this.translateString('Reset all filters');
            }
        },

        components: {
            appFinanceFilterMenu,
            appFinanceFilter,
            appCarCompareLink,
            appModal,
            appScrollbox,
            appRangeSlider,
            appPartExchangeBlock
        }
    });
</script>
