<script>
    import coreCarFilter from 'core/components/CarFinder/CarFilter';
    import appRangeSlider from 'core/components/Elements/RangeSlider';
    import appFinanceFilterMenu from 'motorrad/components/CarFinder/FinanceFilterMenu';
    import appPartExchangeFilterMenu from 'motorrad/components/CarFinder/PartExchangeFilterMenu';
    import appFinanceFilter from 'motorrad/components/CarFinder/FinanceFilter';
    import translateString from 'core/filters/Translate';
    import appCarCompareLink from 'motorrad/components/CarFinder/CarCompareLink';
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
                    'bodystyle': this.translateString('Segment'),
                    'exterior': this.translateString('Colour')
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
                uniqueCounter: 0,

                // DSP 2.0 changes type of some attributes to swatches. But this breaks DSP 1.0
                // behavior so we revert their type to checkboxes until their theme will be updated.
                // See PEP-3701 for details.
                swatchesToCheckbox: ['exterior']
            }
        },

        methods: {
            translateString,

            getPrefixes() {
                return ['mot'];
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

                result.map(item => {
                    if (this.swatchesToCheckbox.indexOf(item.code) >= 0) {
                        item.frontend_display_type = 0;
                    }
                });

                result.sort((obj1, obj2) => (obj1.position < obj2.position ? -1 : 1));

                return result;
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
