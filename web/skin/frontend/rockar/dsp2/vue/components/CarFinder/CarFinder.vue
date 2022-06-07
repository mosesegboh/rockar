<template>
    <div id="car-finder">
        <div class="general-preloader" v-show="ajaxLoading || CarFilter.showLoading"><div class="show-loading"></div>
        </div>
        <div class="filters-header-mobile" v-show="currentStep !== 0 && this.$root.$refs.carFilter.showMobileMenu" @click="closeFilters()">
            {{ 'Filters' | translate }}
            <span class="close">&nbsp;</span>
        </div>
        <slot name="model-filter-slot"></slot>
        <slot name="car_filter"></slot>
        <slot></slot>
        <div class="find-car-wrapper"
             :class="{'extra-small-window': extraSmallWindow, 'footer-margin': currentStep > 0 && this.CarFilter.productCount === 1}"
             v-el:navigation-buttons-wrapper>
            <div class="navigation-outer-container">
                <button class="button dsp2-money"
                        :class="{disabled: disabledNext}"
                        v-show="currentStep !== steps.length - 1"
                        @click="nextStep(false)"
                        :disabled="disabledNext"
                >
                    {{ 'Find my BMW' | translate }}
                </button>
            </div>
        </div>
        <div class="car-finder-container" v-show="currentStep !== steps.length - 1">
        </div>
    </div>
</template>

<script>
    import appModal from 'core/components/Elements/Modal';
    import Constants from 'core/components/Shared/Constants';
    import UrlParser from 'dsp2/mixins/UrlParser';
    import translateString from 'core/filters/Translate';
    import EventTracker from 'dsp2/mixins/EventTracker';

    export default Vue.extend({
        mixins: [Constants, UrlParser, EventTracker],

        components: {
            appModal
        },

        props: {
            compare: {
                required: true,
                type: Object
            },

            isInStoreDevice: {
                required: false,
                type: Boolean
            },

            productsList: {
                required: true,
                type: Object
            },

            options: {
                required: true,
                type: Object
            },

            carConditions: {
                type: Array,
                required: false
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

            additionalInfo: {
                required: true,
                type: Array
            },

            carFilters: {
                required: true,
                type: Array
            },

            modelAttribute: {
                required: false,
                type: String
            },

            currentSavedStep: {
                required: false,
                type: String,
                default: 'landing'
            }
        },

        data() {
            return {
                steps: ['landing', 'carFilter'],
                currentStep: 0,
                levitateNavigation: false,
                editPartExchange: false,
                ajaxLoading: false,
                financeConfirmationPopup: false,
                extraSmallWindow: false,
                isMobile: false,
                outstandingFinanceSettlement: 0,
                modelButtonTitle: 'Next Step'
            }
        },

        watch: {
            carFilters: {
                handler(newVal) {
                    this.$store.commit('setCarFilters', newVal);
                },

                deep: true
            },

            currentStep: {
                handler(newVal) {
                    this.$store.commit('setCarFilterStep', newVal);
                    this.changeHeaderNavigationClass(newVal);
                }
            }
        },

        computed: {
            PartExchangeFilter() {
                return this.$root.$refs.partExchange.$refs.partExchangeFilter;
            },

            FinanceFilter() {
                return this.$root.$refs.financeFilter;
            },

            ModelFilter() {
                return this.$root.$refs.modelFilter;
            },

            CarFilter() {
                return this.$root.$refs.carFilter;
            },

            ProductGrid() {
                return this.$root.$refs.productGrid;
            },

            isPayInFull() {
                return this.$root.$refs.financeFilter.isPayInFull;
            },

            FinanceFilterMenu() {
                return this.CarFilter.financeFilterMenu;
            },

            disabledNext() {
                return !this.modelFilterValid;
            },

            modelFilterValid() {
                return this.ModelFilter.modelFilterValid;
            },

            modelFilter() {
                return this.$store.state.carFinder.carFilters.filter(item => item.code === this.modelAttribute);
            },

            youBuild() {
                return this.$root.$refs.youBuildCategory;
            },

            PxVuex() {
                return this.$store.state.general.PX;
            },

            deepLinkParams() {
                return this.$store.state.general.deepLinkRequestParams;
            },

            getActiveModels() {
                return this.ModelFilter.getActiveModelTitles;
            }
        },

        events: {
            'CarFinder::productsUpdated'(products) {
                this.ProductGrid.productsList.products = products.products;
            },

            'CarFinder::resetFilters'() {
                this.FinanceFilter.ajaxLoading = true;
                this.goToStep(0);
                this.FinanceFilter.hardResetFilters(() => {
                    this.PartExchangeFilter.skipPXWithOutPX();
                    this.FinanceFilter.ajaxLoading = false;
                    this.$store.commit('setNotificationMessage', { message: this.translateString('All filters are reset.'), type: 'success', timeout: 5000 });
                });
                this.CarFilter.resetFilters();
                this.CarFilter.resetUrl();

                this.FinanceFilter.$parent.show = true;
                this.CarFilter.$parent.show = false;
                this.FinanceFilter.manualFilterDisable = true;
            },

            'CarFinder::updateFilters'() {
                this.$broadcast('CarFilter::updateFilters');
            },

            'CarFinder::updateCurrentStepInSession'(step, url = false) {
                this.updateCurrentStepInSession(step, url);
            },

            'CarFinder::balloonPercentageChange'() {
                this.$broadcast('ProductGrid::balloonPercentageChange');
            },

            'CarFinder::reRenderCarousel'() {
                this.$broadcast('ModelFilter::reRenderCarousel');
            },

            'CarFinder::ajaxLoad'(value) {
                this.ajaxLoading = value;
            },

            'CarFinder::sortProducts'() {
                this.$root.$refs.carFilter.updateFilters(false, false, true);
            }
        },

        methods: {
            productsUpdated(data) {
                this.productsList.products = data.products;
                this.$nextTick(() => {
                    this.$emit('CarFinder::reRenderCarousel');
                });
            },

            translateString,

            resetUrl() {
                history.replaceState({}, '', window.location.href.split('?')[0]);
            },

            resetInStoreDevice() {
                if (this.isInStoreDevice) {
                    if (!this.skip) {
                        this.resetUrl();
                        this.$emit('CarFinder::resetFilters');
                    }
                }
            },

            nextStep(passed, forceNext = false, forceUpdate = true) {
                if (forceNext) {
                    this.currentStep++;

                    if (forceUpdate === true) {
                        this.updateCurrentStep();
                    }
                    return;
                }

                if (!passed) {
                    switch (this.currentStep) {
                        case 0: // Finance steps
                            this.FinanceFilter.applyFilter();
                            this.currentStep++;
                            this.updateCurrentStep();
                            this.CarFilter.updateFilters();
                            this.fireTriggerEvent(this.getActiveModels)
                            this.fireEvent(this.currentStep);
                            break;

                        default:
                            this.currentStep++;
                            this.updateCurrentStep();
                            break;
                    }
                } else {
                    this.currentStep++;
                    this.updateCurrentStep();
                }

                window.scrollTo(0, 0);
            },

            prevStep() {
                this.CarFilter.stepBackClicked = true;
                switch (this.currentStep) {
                    case 1:
                        this.FinanceFilter.resetAcceptedFinance();
                        this.CarFilter.resetFiltersExceptFinance();
                        this.CarFilter.updateFilters();
                        this.currentStep = 0;
                        this.fireEvent(this.currentStep);
                        break;
                    default:
                        this.currentStep--;
                        break;
                }
                this.updateCurrentStep();
                this.CarFilter.stepBackClicked = false;
                window.scrollTo(0, 0);
            },

            goToStep(id) {
                if (id <= this.steps.length && id >= 0 && this.currentStep > id) {
                    this.currentStep = id;

                    switch (id) {
                        case 0:
                            this.FinanceFilter.resetAcceptedFinance();
                            break;
                    }

                    this.updateCurrentStep();
                    window.scrollTo(0, 0);
                }
            },

            updateCurrentStep(newCurrentStep = false) {
                let promise = {};
                const currentStep = newCurrentStep || this.currentStep;

                switch (currentStep) {
                    case 0:
                        this.$broadcast('FinanceOptions::playVideo');
                        // Finance Selection
                        promise = this.updateCurrentStepInSession(this.STEPS.SET_YOUR_BUDGET);
                        break;
                    case 1:
                        this.$broadcast('FinanceOptions::stopVideo');
                        this.ModelFilter.getLatestActiveItem();
                        // Results page
                        promise = this.updateCurrentStepInSession(this.STEPS.RESULTS_PAGE);
                        break;
                }

                return promise;
            },

            updateCurrentStepInSession(step, url = false) {
                if (!url) {
                    url = this.options.updateStepUrl;
                }

                if (url) {
                    this.ajaxLoading = true;
                    const promise = this.$http({
                        url,
                        data: {
                            currentStep: step
                        }
                    });

                    promise.then(this.updateCurrentStepInSessionSuccess, this.updateCurrentStepInSessionFail);

                    return promise;
                }
            },

            updateCurrentStepInSessionSuccess(response) {
                if (response && response.data !== undefined && response.data.redirect !== undefined) {
                    window.location.href = response.data.redirect;
                }
                this.ajaxLoading = false;
            },

            updateCurrentStepInSessionFail() {
                this.ajaxLoading = false;
            },

            showEditPartExchange() {
                this.editPartExchange = false;
            },

            softResetFilters() {
                this.CarFilter.resetFilters();
                this.CarFilter.resetUrl();
                this.ModelFilter.resetToDefault();
                this.FinanceFilter.clearPreviousSettings();
                this.FinanceFilter.resetFilters();
                this.FinanceFilter.acceptFilter();
            },

            acceptFilter() {
                this.financeConfirmationPopup = false;
            },

            onResize(width, height) {
                this.extraSmallWindow = width < this.$root.RESPONSIVE_BREAKPOINTS.TABLET && height <
                    this.$root.RESPONSIVE_BREAKPOINTS.MIN_MOBILE_HEIGHT;
                this.isMobile = width <= this.$root.RESPONSIVE_BREAKPOINTS.MIN_MOBILE;
            },

            /**
             * Get product results step index
             *
             * @returns {number}
             */
            getResultsStep() {
                return 1;
            },

            pauseVideos() {
                jQuery(this.$el).find('.finance-video').each((index, element) => {
                    const iframes = jQuery(element).find('iframe');
                    if (!iframes.length) {
                        return undefined;
                    }
                    iframes.each((index, element) => {
                        element.contentWindow.postMessage(
                            JSON.stringify({ method: 'pause' }), '*'
                        );
                    });
                });
            },

            updateUrl() {
                let newUrl = '';
                const financeParams = this.FinanceFilter.financeParams;

                newUrl += `step=${this.steps[this.currentStep]}`;

                this.CarFilter.filters.forEach((value) => {
                    value.options.forEach((option) => {
                        if (option.state) {
                            const separator = (newUrl.length) ? '&' : '';
                            newUrl += `${separator}${value.code}[]=${option.id}`;
                        }
                    });
                });

                Object.keys(financeParams).forEach((key) => {
                    const separator = (newUrl.length) ? '&' : '';
                    newUrl += `${separator}${key}=${financeParams[key]}`;
                });

                history.replaceState({}, '', `?${newUrl}`);
            },

            changeHeaderNavigationClass(currentStep) {
                if (currentStep > 0) {
                    jQuery('body').addClass('on-light');
                } else {
                    jQuery('body').removeClass('on-light');
                }
            },

            closeFilters() {
                this.$root.$refs.productGrid.hideProductGrid = false;
                this.$root.$refs.carFilter.showMobileMenu = false;
            },

            /**
             * Fire event for tracking purposes on initial load of landing page and results page
             */
            fireEvent(currentStep) {
                switch (currentStep) {
                    case 0:
                        this.fireEventForTracking(
                            this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                            this.getEventConstants().EVENTRACKERVALUES.LANDINGPAGE
                        );
                        break;

                    case 1:
                        this.fireEventForTracking(
                            this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                            this.getEventConstants().EVENTRACKERVALUES.RESULTSPAGE
                        );
                        break;

                    default:
                        return false;
                }
            },

            /**
             * Fire event for tracking purposes on BMW CTA with selected Model Ids
             */
            fireTriggerEvent(modelIds) {
                this.fireEventForTracking(
                    this.getEventConstants().PAGEDESCRIPTION.TRIGGERS,
                    `${this.getEventConstants().TRIGGERTRACKERVALUES.SELECTEDMODELS}${modelIds}`
                );
            }
        },

        created() {
            const url = this.$root.parseURL().searchObject;
            const requestedData = {
                balloonPercentage: url.balloonPercentage,
                deposit: url.deposit,
                depositMultiple: url.depositMultiple,
                financeGroupType: url.financeGroupType,
                groupId: url.group_id,
                method: url.method,
                mileage: url.mileage,
                monthlyPay: url.monthlypay,
                payInFull: url.payinfull,
                step: url.step,
                term: url.term
            };

            this.$store.commit('setDeepLinkRequestParams', requestedData);

            EventsBus.$on('CarFinder::UpdateUrl', () => {
                this.updateUrl();
            });

            EventsBus.$on('PartExchangeFilter::PXWithOutPX', () => {
                this.PartExchangeFilter.skipStep();
            });
        },

        beforeCompile() {
            this.$store.commit('setCarFilters', this.carFilters);

            if (this.deepLinkParams.step !== 'carFilter') {
                this.ajaxLoading = true;
            }
        },

        ready() {
            const parser = this.parseURL();
            if (parser.searchObject.skip) {
                jQuery('html, body').animate({
                    scrollTop: jQuery('.category-products').offset().top
                }, 1);
            }

            this.onResize(window.innerWidth, window.innerHeight);
            if (this.$root.isSafari()) { // Resize on safari not working as well as excepted
                let lastWidth = window.innerWidth;
                let lastHeight = window.innerHeight;

                setInterval(() => {
                    const newWidth = window.innerWidth;
                    const newHeight = window.innerHeight;

                    if (newWidth !== lastWidth || lastHeight !== newHeight) {
                        lastWidth = newWidth;
                        lastHeight = newHeight;
                        this.onResize(newWidth, newHeight);
                    }
                }, 50);
            } else {
                let resizeTimerPointer = null;

                jQuery(window).resize(() => {
                    clearTimeout(resizeTimerPointer);

                    resizeTimerPointer = setTimeout(() => {
                        const newWidth = window.innerWidth;
                        const newHeight = window.innerHeight;
                        this.onResize(newWidth, newHeight);
                    }, 50);
                });
            }
            if (!this.FinanceFilter.financeParams.acceptedFinance) {
                this.currentStep = 0;
            } else {
                this.currentStep = this.steps.indexOf(this.currentSavedStep) !== -1
                    ? this.steps.indexOf(this.currentSavedStep)
                    : 0;

                if (this.options.showFinancePopup) {
                    this.financeConfirmationPopup = true;
                }
            }

            if (sessionStorage.getItem('YouBuild::updateFilters')) {
                this.CarFilter.updateFilters();
                sessionStorage.setItem('YouBuild::updateFilters', false);
            }

            if (window.sessionStorage.getItem('CarFinder::resetFilters') === 'true') {
                this.CarFilter.resetFiltersExceptFinance();
                this.CarFilter.updateFilters();
                window.sessionStorage.setItem('CarFinder::resetFilters', false);
            }

            if (window.sessionStorage.getItem('CarFinder::redirectToResults') === 'true') {
                this.currentStep = 1;
                window.sessionStorage.removeItem('CarFinder::redirectToResults');
            }

            // When redirected from my account to view compare list, session storage cleared from compare
            if (window.sessionStorage.getItem('CarFinder::redirectToResultsCompare')) {
                this.currentStep = 1;
                window.sessionStorage.removeItem('CarFinder::redirectToResultsCompare');
            }

            if (window.sessionStorage.getItem('CarFinder::redirectToPdp')) {
                window.sessionStorage.removeItem('CarFinder::redirectToPdp');
            }

            // Reset PX if in-store mode and customer is a guest
            if (this.isInStoreDevice && this.currentStep === 0 && !this.youBuild) {
                this.resetInStoreDevice();
                this.$broadcast('PartExchange::softReset');
            }

            if (this.isInStoreDevice) {
                this.FinanceFilter.filterCollection();
            }

            if (this.currentStep !== 0) {
                this.$broadcast('CarFilter::updateFilters');
            }

            this.changeHeaderNavigationClass(this.currentStep);

            if (this.currentStep === 0) {
                this.softResetFilters();
            }

            this.ajaxLoading = false;
            this.fireEvent(this.currentStep);
        }
    });
</script>
