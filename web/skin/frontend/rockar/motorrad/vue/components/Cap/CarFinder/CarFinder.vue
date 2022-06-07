<template>
    <div id="car-finder">
        <div class="general-preloader" v-show="ajaxLoading || CarFilter.ajaxLoading"><div class="show-loading"></div></div>
        <app-navigation-steps
            :requested-step="steps[currentStep]"
            :navigation-content-url="options.navigationContentUrl"
            :navigation-substep="PartExchangeFilter.stepTwoSubStep"
        >
            <div slot="navigation_learn_more_popup_content" class="vue-slot">
                <slot name="navigation_learn_more_popup_content"></slot>
            </div>
        </app-navigation-steps>
        <slot name="finance_filter_slider"></slot>
        <slot></slot>
        <div class="navigation-buttons-wrapper col-12"
             v-show="!this.PartExchange.openCustom"
             :class="{'extra-small-window': extraSmallWindow, 'footer-margin': currentStep === 3 && this.CarFilter.productCount === 1,'px-second-step': partExchangeSecondStep}"
             v-el:navigation-buttons-wrapper>
            <div class="navigation-outer-container">
                <button class="button button-narrow step-back desktop-tablet-only"
                    v-show="currentStep !== 0 && currentStep < 3"
                    @click="prevStep"
                >
                    {{ 'Step back' | translate}}
                </button>

                <button class="button button-narrow next-step desktop-tablet-only"
                    :class="{disabled: disabledNext}"
                    v-show="!partExchangeLastStep && !partExchangeSecondStep && currentStep !== steps.length - 1 && currentStep !== 2"
                    @click="nextStep(false)"
                >
                    {{ 'Next step' | translate }}
                </button>

                <!-- Model filter button -->
                <button class="button button-narrow next-step continue desktop-tablet-only"
                        :class="{disabled: disabledNext}"
                        :disabled="disabledNext"
                        v-show="currentStep === 2"
                        @click="nextStep(false)"
                >
                    {{ 'Next step' | translate }}
                </button>

                <!-- Mobile navigation global button -->
                <button class="button button-narrow next-step mobile-only"
                        :class="{'with-back': currentStep === 1 || currentStep === 2, 'full-width': currentStep === 0, 'disabled': disabledNext}"
                        :disabled="disabledNext"
                        v-show="!partExchangeSecondStep"
                        @click="nextStep(false)"
                >
                    {{ 'Next step' | translate }}
                </button>

                <!-- Trade in start -->
                <button class="button button-narrow get-valuation"
                    :class="{ disabled: !partExchangeIsValid }"
                    v-show="partExchangeSecondStep"
                    @click="nextStep(false)"
                >
                    {{ 'Get Valuation' | translate }}
                </button>

                <button class="button button-wide button-narrow next-step continue-px with-px"
                        :class="[partExchangeValuationIsValid ? 'button-default' : 'button-disabled']"
                        :disabled="!partExchangeValuationIsValid"
                        @click="updateOutstandingFinance() + nextStep(false)"
                        v-if="partExchangeLastStep"
                >
                    {{ 'Continue with this offer' | translate }}
                </button>
                <button class="button button-narrow button-gray-light-2 button-wide next-step continue-px without-px text-center desktop-tablet-only"
                        @click="PartExchangeFilter.skipStep(true)"
                        v-if="partExchangeLastStep"
                >
                    {{ 'Continue without Trade in' | translate }}
                </button>

                <button class="button button-narrow button-gray-light-2 continue-px without-px skip-px"
                    v-show="partExchangeSecondStep"
                    @click="PartExchangeFilter.skipStep()"
                >
                    {{ 'Skip Trade in' | translate }}
                </button>

                <!-- Trade in end -->
                <button class="button button-narrow step-back mobile-only"
                    v-show="currentStep === 1 && (PartExchangeFilter.stepTwoSubStep <= 2) || currentStep === 2 || currentStep === 3"
                    @click="prevStep"
                >
                    <span></span>
                </button>
            </div>
        </div>

        <app-modal :show.sync="editPartExchange" v-show="editPartExchange" class="carfinder-px-modal" >
            <div slot="content" class="car-finder-px-modal">
                <app-configurator-part-exchange
                        v-ref:configurator-part-exchange
                        :pe-id="PartExchange.pxId"
                        :valuation-url="PartExchange.valuationUrl"
                        :car-details-url="partExchangeAdditional.carDetailsUrl"
                        :reset-url="partExchangeAdditional.resetUrl"
                        :additional-info='additionalInfo'
                        :explanatory-text="partExchangeAdditional.explanatoryText"
                        :active-condition="PartExchange.activeCondition"
                        :car-conditions="carConditions"
                        :car-alternative-details-url="partExchangeAdditional.carAlternativeDetailsUrl"
                        :save-valuation-url="PartExchange.saveValuationUrl"
                        :save-to-session-url="partExchangeAdditional.saveToSessionUrl"
                        :saved='partExchangeSaved'
                        :saved-px-list="partExchangeAdditional.savedPxList"
                        :vehicle-type-url="partExchangeAdditional.vehicleTypeUrl"
                        :year-url="partExchangeAdditional.yearUrl"
                        :make-url="partExchangeAdditional.makeUrl"
                        :model-url="partExchangeAdditional.modelUrl"
                        :fuel-type-url="partExchangeAdditional.fuelTypeUrl"
                        :transmission-url="partExchangeAdditional.transmissionUrl"
                        :variant-url="partExchangeAdditional.variantUrl"
                        :custom-car-url="partExchangeAdditional.customCarUrl"
                        :active-px-url="partExchangeAdditional.activePxUrl"
                        :saved-px="savedPx"
                        :can-open-custom="false"
                ></app-configurator-part-exchange>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import appConfiguratorPartExchange from 'motorrad/components/Configurator/PartExchange';
    import appNavigationSteps from 'motorrad/components/CarFinder/NavigationSteps';
    import appModal from 'core/components/Elements/Modal';
    import Constants from 'core/components/Shared/Constants';
    import UrlParser from 'motorrad/mixins/UrlParser';
    import translateString from 'core/filters/Translate';
    import stickybits from 'stickybits';

    export default Vue.extend({
        mixins: [Constants, UrlParser],

        components: {
            appConfiguratorPartExchange,
            appNavigationSteps,
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
                default: false
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
            }
        },

        data() {
            return {
                steps: ['finance', 'partExchange', 'modelFilter', 'carFilter'],
                currentStep: 0,
                levitateNavigation: false,
                editPartExchange: false,
                ajaxLoading: false,
                financeConfirmationPopup: false,
                extraSmallWindow: false
            }
        },

        watch: {
            editPartExchange(val) {
                if (!val) { // On modal close
                    if (this.ConfiguratorPartExchange.checkData() && this.ConfiguratorPartExchange.PXValuation.valuationCompleted) {
                        this.PartExchange.PXVrm.carInfo.capId = this.ConfiguratorPartExchange.PXVrm.carInfo.capId;
                        this.PartExchange.PXVrm.carInfo.model = this.ConfiguratorPartExchange.PXVrm.carInfo.model;
                        this.PartExchange.PXVrm.carInfo.title = this.ConfiguratorPartExchange.PXVrm.carInfo.title;
                        this.PartExchange.PXVrm.carInfo.vrm = this.ConfiguratorPartExchange.PXVrm.carInfo.vrm;
                        this.PartExchange.PXVrm.carInfo.vrmInput = this.ConfiguratorPartExchange.PXVrm.carInfo.vrmInput;

                        this.PartExchange.PXVrm.carDetails.result = this.ConfiguratorPartExchange.PXVrm.carDetails.result;
                        this.PartExchange.PXVrm.carDetails.vrmDisabled = this.ConfiguratorPartExchange.PXVrm.carDetails.vrmDisabled;
                        this.PartExchange.PXVrm.carNotFounded = this.ConfiguratorPartExchange.PXVrm.carNotFound;
                        this.PartExchange.PXVrm.carAlternatives = this.ConfiguratorPartExchange.PXVrm.carAlternatives;
                        this.PartExchange.PXVrm.step = this.STEPS.OUTSTANDING_FINANCE_ON_CAR; // Set as passed 3 step

                        this.PartExchange.PXValuation.valuationResult = this.ConfiguratorPartExchange.PXValuation.valuationResult;
                        this.PartExchange.PXValuation.partExchangeValue = this.ConfiguratorPartExchange.PXValuation.partExchangeValue;
                        this.PartExchange.PXValuation.valuationCompleted = this.ConfiguratorPartExchange.PXValuation.valuationCompleted;
                        this.PartExchange.PXValuation.outstandingFinance = this.ConfiguratorPartExchange.PXValuation.outstandingFinance;

                        this.PartExchange.additionalInfo = this.ConfiguratorPartExchange.additionalInfo;
                        this.PartExchange.carConditions = this.ConfiguratorPartExchange.carConditions;
                        this.PartExchange.mileage = this.ConfiguratorPartExchange.mileage;
                        this.PartExchange.valuationResult = this.ConfiguratorPartExchange.valuationResult;
                        this.PartExchange.activeCondition = this.ConfiguratorPartExchange.activeCondition;
                        this.PartExchange.step = this.STEPS.OUTSTANDING_FINANCE_ON_CAR; // Set as passed 3 step
                        this.$nextTick(() => {
                            this.FinanceFilterMenu.FinanceFilter.filterCollection();
                        });
                    }
                } else { // On modal open
                    this.$nextTick(() => {
                        this.ConfiguratorPartExchange.valuationResult = false;
                        this.ConfiguratorPartExchange.mileage = 0;

                        this.ConfiguratorPartExchange.PXVrm.carInfo.capId = null;
                        this.ConfiguratorPartExchange.PXVrm.carInfo.model = null;
                        this.ConfiguratorPartExchange.PXVrm.carInfo.title = null;
                        this.ConfiguratorPartExchange.PXVrm.carInfo.vrm = null;
                        this.ConfiguratorPartExchange.PXVrm.carInfo.vrmInput = '';
                        this.ConfiguratorPartExchange.PXVrm.carDetails.result = false;
                        this.ConfiguratorPartExchange.PXVrm.carDetails.vrmDisabled = false;
                        this.ConfiguratorPartExchange.PXVrm.carNotFound = false;
                        this.ConfiguratorPartExchange.PXVrm.carAlternatives = [];

                        this.ConfiguratorPartExchange.PXValuation.valuationResult = false;
                        this.ConfiguratorPartExchange.PXValuation.partExchangeValue = 0;
                        this.ConfiguratorPartExchange.PXValuation.valuationCompleted = false;
                        this.ConfiguratorPartExchange.PXValuation.outstandingFinance = 0;

                        this.ConfiguratorPartExchange.additionalInfo.forEach((checkbox) => {
                            checkbox.checked = checkbox.default;
                        });

                        this.ConfiguratorPartExchange.carConditions.forEach((condition, index) => {
                            if (condition.is_default) {
                                this.ConfiguratorPartExchange.activeCondition = condition.id;
                            }
                        });

                        // Pass VRM if filled before
                        if (this.PartExchange.PXVrm.carInfo.vrmInput || this.PartExchange.PXVrm.carInfo.vrmInput.length) {
                            this.ConfiguratorPartExchange.PXVrm.carInfo.vrm = this.PartExchange.PXVrm.carInfo.vrm;
                            this.ConfiguratorPartExchange.PXVrm.carInfo.vrmInput = this.PartExchange.PXVrm.carInfo.vrmInput;
                        }

                        if (this.PartExchange.mileage) {
                            this.ConfiguratorPartExchange.mileage = this.PartExchange.mileage;
                            this.ConfiguratorPartExchange.PXVrm.getCarDetails(this.STEPS.CONDITION_INFO);
                        }

                        if (this.PartExchange.activeCondition) {
                            this.ConfiguratorPartExchange.activeCondition = this.PartExchange.activeCondition;
                            this.ConfiguratorPartExchange.activeConditionSelectIndex = this.PartExchange.activeConditionSelectIndex;
                            if (typeof this.ConfiguratorPartExchange.$refs.conditionSlider !== 'undefined') {
                                this.ConfiguratorPartExchange.$refs.conditionSlider.changeActive(this.PartExchange.activeCondition);
                            }
                        }

                        if (this.PartExchange.PXValuation.outstandingFinance) {
                            this.ConfiguratorPartExchange.PXValuation.outstandingFinance = this.PartExchange.PXValuation.outstandingFinance;
                        }

                        this.ConfiguratorPartExchange.additionalInfo = this.PartExchange.additionalInfo;
                    });
                }
            },

            carFilters: {
                handler(newVal) {
                    this.$store.commit('setCarFilters', newVal);
                },

                deep: true
            },

            currentStep(newVal, oldVal) {
                if (newVal === this.STEPS.CHOOSE_CAR_MODEL_PAGE && oldVal !== this.STEPS.CHOOSE_YOUR_CAR_PAGE) {
                    this.CarFilter.updateFilters();
                }
            },
        },

        computed: {
            PartExchangeFilter() {
                return this.$root.$refs.partExchangeFilter;
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

            plateError() {
                return this.PartExchangeFilter.PXVrm.plateNumberError;
            },

            carNotFound() {
                return this.PartExchangeFilter.PXVrm.carNotFound;
            },

            partExchangeIsValid() {
                return this.PartExchangeFilter.checkData();
            },

            partExchangeValuationIsValid() {
                return this.PartExchangeFilter.PXValuation.isValuationValid;
            },

            partExchangeLastStep() {
                return this.currentStep === 1 && (this.PartExchangeFilter.stepTwoSubStep === 2);
            },

            partExchangeSecondStep() {
                return this.currentStep === 1 && (this.PartExchangeFilter.stepTwoSubStep === 1);
            },

            PartExchange() {
                return this.PartExchangeFilter.PXVrm.PX;
            },

            FinanceFilterMenu() {
                return this.CarFilter.financeFilterMenu;
            },

            ConfiguratorPartExchange() {
                return this.$refs.configuratorPartExchange;
            },

            hasAlternatives() {
                return this.PartExchangeFilter.PXVrm.carAlternatives.length;
            },

            hasPXResult() {
                return this.PartExchangeFilter.PXVrm.carDetails.result;
            },

            hasMileage() {
                return this.$store.state.general.PX.mileage > 0 || this.PartExchangeFilter.mileage > 0;
            },

            hasVrmInput() {
                return this.PartExchangeFilter.PXVrm.carInfo.vrmInput !== '';
            },

            disabledNext() {
                switch (this.currentStep) {
                    case 1:
                        return ((this.carNotFound || this.plateError)
                            || !this.hasMileage
                            || !this.hasVrmInput
                            || (this.PartExchangeFilter.stepTwoSubStep === 1 && !this.partExchangeIsValid));
                    case 2:
                        return !this.modelFilterValid;
                    default:
                        return false;
                }
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

            customCar() {
                return this.PartExchangeFilter.PXVrm.carInfo.vrmInput === this.PartExchangeFilter.PXVrm.carInfo.vrm;
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
                    this.$broadcast('PartExchangeCustomCar::closeCustom');
                    this.PartExchangeFilter.hardResetPartExchange(() => {
                        this.FinanceFilter.ajaxLoading = false;
                        this.$store.commit('setNotificationMessage', { message: this.translateString('All filters are reset.'), type: 'success', timeout: 5000 });
                    });
                });
                this.CarFilter.resetFilters();
                this.CarFilter.resetUrl();

                this.FinanceFilter.$parent.show = true;
                this.PartExchangeFilter.$parent.show = false;
                this.CarFilter.$parent.show = false;
                this.FinanceFilter.manualFilterDisable = true;
            },

            'CarFinder::updateFilters'() {
                this.$broadcast('CarFilter::updateFilters');
            },

            'CarFinder::changeCarCondition'(newValue) {
                this.$broadcast('PartExchange::changeCarCondition', newValue);
            },

            'CarFinder::updateCurrentStepInSession'(step, url = false) {
                this.updateCurrentStepInSession(step, url);
            }
        },

        methods: {
            productsUpdated(data) {
                this.productsList.products = data.products;
            },

            translateString,

            resetUrl() {
                history.replaceState({}, '', window.location.href.split('?')[0]);
            },

            resetInStoreDevice() {
                if (this.isInStoreDevice) {
                    this.resetUrl();
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
                            this.FinanceFilter.handleFinanceConfirmationPopup();
                            this.pauseVideos();
                            break;

                        case 1: // Trade in step
                            switch (this.PartExchangeFilter.stepTwoSubStep) {
                                case 0: // Step 2.1
                                    if (!this.disabledNext) {
                                        if (!this.customCar) {
                                            this.PartExchangeFilter.PXVrm.getCarDetails().then(() => {
                                                if (!this.carNotFound && !this.plateError) {
                                                    if (this.hasAlternatives && !this.hasPXResult) {
                                                        this.updateCurrentStep();
                                                    } else if (this.hasAlternatives && this.hasPXResult) {
                                                        this.PartExchangeFilter.stepTwoSubStep = 1;
                                                        this.PartExchange.enableMoreInfo();
                                                        this.updateCurrentStep();
                                                    } else {
                                                        if (this.PartExchangeFilter.PXVrm.carDetails.result) {
                                                            this.vrmConfirmationPopup = false;
                                                            this.PartExchangeFilter.stepTwoSubStep = 1;
                                                            this.$broadcast('MoreInfo::enable');
                                                        } else {
                                                            this.PartExchange.vrmConfirmationPopup = true;
                                                        }
                                                        this.updateCurrentStep();
                                                    }
                                                }
                                            });
                                        } else {
                                            this.PartExchangeFilter.stepTwoSubStep = 1;
                                            this.updateCurrentStep();
                                        }
                                    }

                                    break;

                                case 1: // Step 2.2
                                    if (this.partExchangeIsValid) {
                                        this.PartExchangeFilter.getValuation().then(() => {
                                            this.updateCurrentStep();
                                        });
                                    }
                                    break;

                                case 2: // step 2.3
                                    this.ajaxLoading = true;
                                    this.updateCurrentStep(this.STEPS.CHOOSE_YOUR_CAR).then(() => {
                                        this.ajaxLoading = true;
                                        this.PartExchangeFilter.PXValuation.saveValuation().then(() => {
                                            this.ajaxLoading = false;
                                        });
                                    });
                                    break;

                                default:
                                    this.currentStep++;
                                    this.updateCurrentStep();
                                    break;
                            }
                            break;

                        case 2: // Model filter
                            this.FinanceFilter.applyFilter();
                            this.currentStep++;
                            this.updateCurrentStep();
                            this.CarFilter.updateFilters();
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
                switch (this.currentStep) {
                    case 1:
                        if (this.PartExchangeFilter.stepTwoSubStep > 0) {
                            this.PartExchangeFilter.stepTwoSubStep--;
                        } else {
                            this.FinanceFilter.resetAcceptedFinance();
                            this.currentStep--;
                        }

                        break;
                    case 2:
                        if (this.PartExchangeFilter.stepTwoSubStep > 1) {
                            /**
                             * PX 3rd step, check if still have valid valuation
                             */
                            if (!this.partExchangeValuationIsValid) {
                                this.PartExchangeFilter.stepTwoSubStep = 0;
                            }
                        } else if (this.PartExchangeFilter.stepTwoSubStep > 0) {
                            /**
                             * PX 2nd step, check if still have PX result
                             */
                            if (!this.hasPXResult) {
                                this.PartExchangeFilter.stepTwoSubStep = 0;
                            }
                        }
                        this.currentStep = 1;
                        break;
                    case 3:
                        this.CarFilter.resetFiltersExceptFinance();
                        this.CarFilter.updateFilters();
                        this.currentStep--;
                        break;
                    default:
                        this.currentStep--;
                        break;
                }
                this.updateCurrentStep();
                window.scrollTo(0, 0);
            },

            goToStep(id) {
                if (id <= this.steps.length && id >= 0 && this.currentStep > id) {
                    this.currentStep = id;
                    this.PartExchangeFilter.stepTwoSubStep = 0; // explicitly going to any step must reset PX steps
                    this.PartExchange.openCustom = false;

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
                let currentStep = newCurrentStep || this.currentStep;
                const substep = newCurrentStep > 10 && newCurrentStep % 10 || this.PartExchange.stepTwoSubStep;
                currentStep = currentStep > 10 && parseInt(currentStep / 10) || currentStep;

                switch (currentStep) {
                    case 0:
                        // Finance Selection
                        promise = this.updateCurrentStepInSession(this.STEPS.SET_YOUR_BUDGET);
                        break;
                    case 1:
                        // Trade in
                        switch (substep) {
                            case 0:
                                promise = this.updateCurrentStepInSession(this.STEPS.VALUE_CURRENT_CAR);
                                break;
                            case 1:
                                promise = this.updateCurrentStepInSession(this.STEPS.CONDITION_INFO);
                                break;
                            case 2:
                                promise = this.updateCurrentStepInSession(this.STEPS.OUTSTANDING_FINANCE_ON_CAR);
                                break;
                        }
                        jQuery(window).trigger('scroll'); // on some steps, after changing the content, some UI elements won't rearrange themselves - they need to nudged with this event
                        break;
                    case 2:
                        // Model filter
                        promise = this.updateCurrentStepInSession(this.STEPS.CHOOSE_YOUR_CAR);
                        break;
                    case 3:
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
                            currentStep: step,
                            modelFilter: this.modelFilter
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
                this.editPartExchange = true;
            },

            softResetFilters() {
                this.CarFilter.resetFilters();
                this.CarFilter.resetUrl();
                this.FinanceFilter.applyFilter();
            },

            acceptFilter() {
                this.financeConfirmationPopup = false;
            },

            onResize(width, height) {
                if (width < this.$root.RESPONSIVE_BREAKPOINTS.TABLET && height < this.$root.RESPONSIVE_BREAKPOINTS.MIN_MOBILE_HEIGHT) {
                    this.extraSmallWindow = true;
                } else {
                    this.extraSmallWindow = false;
                }
            },

            hideOverlappedLoaders() {
                jQuery('.general-preloader:visible, .preloader:visible').removeClass('hide-loader');
                const $list = jQuery('.general-preloader:visible, .preloader:visible');
                $list.addClass('hide-loader');
                $list.first().removeClass('hide-loader');
            },

            updateOutstandingFinance() {
                this.$store.commit('setPXValuationOutstandingFinance', this.PartExchange.PXValuation.outstandingFinance)
            },

            /**
             * Get product results step index
             *
             * @returns {number}
             */
            getResultsStep() {
                return this.STEPS.CHOOSE_YOUR_CAR_PAGE;
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
        },

        created() {
            EventsBus.$on('PartExchangeFilter::PXWithOutPX', () => {
                this.PartExchangeFilter.skipStep(true);
            });

            EventsBus.$on('CarFinder::stepBack', () => {
                this.prevStep();
            });

            EventsBus.$on('CarFinder::nextStep', (passed, forceNext = false, forceUpdate = true) => {
                this.nextStep(passed, forceNext, forceUpdate);
            });

            EventsBus.$on('CarFinder::UpdateUrl', () => {
                this.updateUrl();
            });
        },

        beforeCompile() {
            this.$store.commit('setCarFilters', this.carFilters);
        },

        ready() {
            const parser = this.parseURL();
            if (parser.searchObject.skip) {
                jQuery('html, body').animate({
                    scrollTop: jQuery('.category-products').offset().top
                }, 1);
            }

            setInterval(this.hideOverlappedLoaders.bind(this), 50);
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
            setInterval(this.hideOverlappedLoaders.bind(this), 200);
            if (!this.FinanceFilter.financeParams.acceptedFinance) {
                this.currentStep = 0;
                this.PartExchange.stepTwoSubStep = 0;
            } else {
                switch (parseInt(this.options.currentStep)) {
                    case this.STEPS.SET_YOUR_BUDGET:
                        this.currentStep = 0;
                        break;
                    case this.STEPS.VALUE_CURRENT_CAR:
                        this.currentStep = 1;
                        this.PartExchange.stepTwoSubStep = 0;
                        break;
                    case this.STEPS.CONDITION_INFO:
                        this.currentStep = 1;
                        if (this.PartExchange.savedPx.vrm || this.PartExchange.tempPx) {
                            this.PartExchange.stepTwoSubStep = 1;
                        } else {
                            this.PartExchange.stepTwoSubStep = 0;
                        }
                        break;
                    case this.STEPS.OUTSTANDING_FINANCE_ON_CAR:
                        this.currentStep = 1;
                        if (this.PartExchange.savedPx.vrm || this.PartExchange.tempPx) {
                            this.PartExchange.stepTwoSubStep = 1;
                            this.PartExchange.getValuation();
                        } else {
                            this.PartExchange.stepTwoSubStep = 0;
                        }
                        break;
                    case this.STEPS.CHOOSE_YOUR_CAR:
                        this.currentStep = 2;
                        break;
                    case this.STEPS.RESULTS_PAGE:
                        this.currentStep = 3;
                        break;
                    default:
                        this.currentStep = 0;
                        break;
                }

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
                this.currentStep = 3;
                window.sessionStorage.removeItem('CarFinder::redirectToResults');
            }

            // Reset PX if in-store mode and customer is a guest
            if (this.isInStoreDevice && this.currentStep === 0 && !this.youBuild) {
                this.resetInStoreDevice();
                this.$broadcast('PartExchange::softReset');
            }

            if (this.isInStoreDevice) {
                this.FinanceFilter.filterCollection();
            }

            stickybits('.navigation-buttons-wrapper', { verticalPosition: 'bottom' });
        }
    });
</script>
