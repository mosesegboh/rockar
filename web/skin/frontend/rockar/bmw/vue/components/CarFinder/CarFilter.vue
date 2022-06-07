<script>
    import coreCarFilter from 'core/components/CarFinder/CarFilter';
    import appRangeSlider from 'core/components/Elements/RangeSlider';
    import appFinanceFilterMenu from 'bmw/components/CarFinder/FinanceFilterMenu';
    import appPartExchangeFilterMenu from 'bmw/components/CarFinder/PartExchangeFilterMenu';
    import appFinanceFilter from 'bmw/components/CarFinder/FinanceFilter';
    import translateString from 'core/filters/Translate';
    import appCarCompareLink from 'core/components/CarFinder/CarCompareLink';
    import appModal from 'core/components/Elements/Modal';
    import appScrollbox from 'core/components/Elements/Scrollbox';

    export default coreCarFilter.extend({

        data() {
            return {
                productCount: '',
                productCountYouDrive: '',
                ajaxLoading: false,
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
                    'variant': this.translateString('Model Variant')
                },
                attributeFiltersChecked: '',
                visible_in_filter_values: {
                    not_visible: 0,
                    catalog_only: 1,
                    youdrive_only: 2,
                    catalog_and_youdrive: 3
                },
                filtersBlacklist: [
                    'black_event_stock_filter'
                ],
                currentSection: false,
                openFilter: '', // Stores open filter code
                matchingVehiclesMessage: 'Vehicles matching your search',
                modelShortestLeadTime: {},
                uniqueCounter: 0
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
                this.financeFilterMenu.resetFinanceFilter();
                this.changeTitle();
                this.stepBackClicked = false;
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

                result.sort((obj1, obj2) => (obj1.position < obj2.position ? -1 : 1));

                return result;
            },
        },

        events: {
            'CarFilter::closeFilter'() {
                this.closeFilterPopup();
            }
        },

        components: {
            appPartExchangeFilterMenu,
            appFinanceFilterMenu,
            appFinanceFilter,
            appCarCompareLink,
            appModal,
            appScrollbox,
            appRangeSlider
        }
    });
</script>
