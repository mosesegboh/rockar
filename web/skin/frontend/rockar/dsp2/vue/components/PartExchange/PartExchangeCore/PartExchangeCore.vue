<template>
    <div class="px-popup-content">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <h4 :class="checkout ? 'step-header' : 'h4 px-title'">{{ pxMainTitle }}</h4>

        <template v-if="checkout && !pxAddedFromDropdownAccessPoints">
            <div class="px-skip">
                <p>
                    {{ introText }}
                </p>
                <div class="row">
                    <div class="col-6">
                        <p>{{ 'No vehicle to trade in?' | translate }}</p>
                    </div>
                    <div class="col-6">
                        <button class="button dsp2-action" @click="checkoutContinueWithoutTradeIn">{{ 'Skip this step' | translate }}</button>
                    </div>
                </div>
            </div>
        </template>

        <app-navigation-steps
            :requested-step="steps[currentStepPx]"
            :navigation-substep="subStep"
            :current-step="currentStepPx"
            :hide-previous="pxAddedFromDropdownAccessPoints && (pdp || myAccount)"
            v-if="(currentStep !== 2 && checkout) || !checkout">
        </app-navigation-steps>

        <app-part-exchange-custom-car
            v-show="openCustom"
            :show-error="showError"
            v-ref:part-exchange-custom-car
            :variant-url="variantUrl"
            :part-exchange-vrm="partExchangeVrm"
            :custom-car-url="customCarUrl"
            :vehicle-type-url="vehicleTypeUrl"
            :year-url="yearUrl"
            :make-url="makeUrl"
            :model-url="modelUrl"
            :fuel-type-url="fuelTypeUrl"
            :transmission-url="transmissionUrl"
            :accessed-from="accessedFrom"
            :car-finder="carFinder"
            :car-finder-component="carFinderComponent"
        >
        </app-part-exchange-custom-car>

        <template v-if="pxAddedFromDropdownAccessPoints">
            <div>
                <div class="px-quote-text">
                    <p>{{ introText }}</p>
                    <p>{{ 'Your trade-in vehicle:' | translate }}</p>
                </div>
                <div>
                    <app-accordion-group>
                        <app-accordion
                            :title="productTitle"
                            :scroll-on-show="false"
                            :open="showAccordion"
                            class-name="accordion-light"
                            type="right-down"
                            id="product_info"
                            v-ref:px-accordion
                        >
                            <div class="currentstep-one">
                                <app-part-exchange-condition
                                    @save="saveValuation"
                                    @next="nextStep(val)"
                                    @select="selectMileage"
                                    @deselect="deselectMileage"
                                    @remove="skipPXWithOutPX"
                                    :car-conditions="carConditions"
                                    :additional-info="additionalInfo"
                                    :active-condition="activeCondition"
                                    :valuation-result="valuationResult"
                                    :is-expired="isExpired"
                                    :part-exchange-is-valid="partExchangeIsValid"
                                    :part-exchange-second-step="partExchangeSecondStep"
                                    :px-accordion="true"
                                    :saved-mileage="mileage"
                                    :has-negative-equity="hasNegativeEquity"
                                    :is-pay-in-full="isPayInFull"
                                    v-ref:px-condition
                                >
                                </app-part-exchange-condition>
                            </div>
                        </app-accordion>
                    </app-accordion-group>
                </div>
            </div>
        </template>

        <div v-show="subStep === 0 && !openCustom && currentStep === 0" class="substep-0">
            <div class="row">
                <div class="col-12">
                    <div class="px-popup-info-block">
                        <h3 class="h3 px-step-title">
                            {{ 'Vehicle Details' | translate }}
                        </h3>
                        <p> {{ 'Please fill in your trade-in vehicle details' | translate }}</p>
                        <p> {{ `Receive an instant trade-in estimate on your current vehicle.
                        You can confirm your trade-in value with an onsite vehicle inspection
                        at your nearest BMW Retailer.` | translate }}</p>
                    </div>

                    <div class="px-vrm">
                        <app-part-exchange-vrm
                            car-alternative-details="url"
                            :registration-year="tempPxRegistrationYear"
                            :car-alternative-details-url="carAlternativeDetailsUrl"
                            v-ref:part-exchange-vrm
                        >
                        </app-part-exchange-vrm>
                    </div>

                    <div class="col-6 col-md-12 px-mileage">
                        <div class="input-label-wrapper">
                            <input type="text"
                               @focus="selectMileage"
                               @blur="deselectMileage"
                               @keyup.enter="nextStep(false)"
                               id="td-mileage"
                               v-model="mileage | numberKilometerFormatPx"
                            >
                            <label class="input-label" for="td-mileage">{{ 'Current Mileage (Km)*' | translate }}</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr>
                    </div>

                    <div class="px-bottom col-6 col-md-12">
                        <button class="button dsp2-money"
                            :class="{ disabled: disabledNext }"
                            @click="nextStep(false)"
                        >
                            {{ 'Search my current vehicle' | translate }}
                        </button>
                        <p class="px-required">{{ '* Indicates a required field.' | translate }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="subStep === 1 && !openCustom" class="substep-1">
            <div class="row">
                <div class="col-12">
                    <div class="px-popup-info-block">
                        <h3 class="h3 px-step-title"> {{ 'Vehicle Details' | translate }} </h3>
                        <p>{{ 'Is this your vehicle?' | translate }}</p>
                    </div>
                </div>
            </div>
            <div class="px-car-info">
                <p>{{ PXVrm.carInfo.title }}</p>
            </div>
            <hr>
            <div class="px-bottom">
                <div class="col-6 col-md-12">
                    <button class="button dsp2-outline" @click="openNotMyCar">{{ 'No, not my vehicle' | translate }}</button>
                </div>
                <div class="col-6 col-md-12">
                    <button class="button dsp2-money" @click="acceptVRM()">{{ 'Yes, continue' | translate }}</button>
                </div>
            </div>
        </div>

        <div v-show="currentStep === 1 && !openCustom" class="currentstep-one">
            <div class="row">
                <div class="col-12">
                    <div class="px-popup-info-block">
                        <h3 class="h3 px-step-title"> {{ 'Vehicle Condition' | translate }} </h3>
                        <p>{{ 'Please rate the condition of your' | translate }} {{ PXVrm.carInfo.title }}</p>
                    </div>
                </div>
            </div>

            <app-part-exchange-condition
                @save="saveValuation"
                @next="nextStep(val)"
                :car-conditions="carConditions"
                :additional-info="additionalInfo"
                :active-condition="activeCondition || 0"
                :valuation-result="valuationResult"
                :is-expired="isExpired"
                :part-exchange-is-valid="partExchangeIsValid"
                :part-exchange-second-step="partExchangeSecondStep"
                v-ref:px-condition
            ></app-part-exchange-condition>
        </div>

        <app-navigation-steps
            :requested-step="steps[currentStepPx]"
            :navigation-substep="subStep"
            :current-step="currentStepPx"
            :hide-previous="pxAddedFromDropdownAccessPoints && (pdp || myAccount)"
            v-if="currentStep === 2 && checkout && !pxAddedFromDropdownAccessPoints">
        </app-navigation-steps>

        <div
            v-show="(currentStep === 2 && !openCustom) || pxAddedFromDropdownAccessPoints"
            class="currentstep-two"
            :class="{ disabled: !valuationResultConditions }"
        >
            <div class="row">
                <div class="col-12">
                    <div class="px-popup-info-block">
                        <h3 class="h3 px-step-title valuation"> {{ 'Valuation' | translate }} </h3>
                    </div>
                </div>
            </div>

            <app-part-exchange-valuation
                v-ref:part-exchange-valuation-result
                :product-id="productId"
                :est-value-disclaimer="estValueDisclaimer"
                :accessed-from="accessedFrom"
                :finance-quote="financeQuote"
                :settlement-quotes-url="settlementQuotesUrl"
                :valuation-url="valuationUrl"
                :save-valuation-url="saveValuationUrl"
                :customer-account-url="customerAccountUrl"
                :customer-is-logged-in="customerIsLoggedIn"
                :checkout-accordion="checkoutAccordion"
                :step-code="stepCode"
                :finance-filter="financeFilter"
                :checkout="checkout"
                :my-account="myAccount"
                :pdp="pdp"
                :car-finder="carFinder"
                :part-exchange-vrm="partExchangeVrm"
                :px-condition="PartExchangeConditions"
                :reset-url="resetUrl"
                :part-exchange-valuation-result="PartExchangeValuationResult"
                :update-step-url="updateStepUrl"
            ></app-part-exchange-valuation>

            <div class="col-12 px-bottom-separator">
                <hr>
            </div>

            <template v-if="!hasNegativeEquity || (hasNegativeEquity && isPayInFull)">
                <div class="px-bottom">
                    <div class="col-6 col-md-12">
                        <button class="button dsp2-outline" @click="skipPXWithOutPX">
                            {{ 'Remove trade-in' | translate }}
                        </button>
                    </div>

                    <div class="col-6 col-md-12">
                        <button class="button dsp2-money" @click="updateOutstandingFinance() + nextStep(false)">
                            {{ 'Continue with trade-in' | translate }}
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    import PartExchange from 'dsp2/components/Shared/PartExchange';
    import appPartExchangeVrm from 'dsp2/components/PartExchange/PartExchangeComponents/PartExchangeVRM';
    import appPartExchangeValuation from 'dsp2/components/PartExchange/PartExchangeComponents/PartExchangeValuation';
    import appMoreInfo from 'dsp2/components/Elements/MoreInfo';
    import appSelect from 'dsp2/components/Elements/Select';
    import appPartExchangeCustomCar from 'dsp2/components/PartExchange/PartExchangeComponents/PartExchangeCustomCar';
    import appModal from 'core/components/Elements/Modal';
    import translateString from 'core/filters/Translate';
    import appNavigationSteps from 'dsp2/components/PartExchange/PartExchangeNavigation/NavigationSteps';
    import Constants from 'dsp2/components/Shared/Constants';
    import appPartExchangeCondition from 'dsp2/components/PartExchange/PartExchangeComponents/PartExchangeCondition';
    import appAccordion from 'dsp2/components/Elements/Accordion';
    import appAccordionGroup from 'dsp2/components/AccordionGroup';
    import EventTracker from 'dsp2/mixins/EventTracker';

    export default Vue.extend({
        mixins: [PartExchange, Constants, EventTracker],

        data() {
            return {
                partExchange: false,
                subStep: 0,
                outstandingFinance: 0,
                hasMileageFocus: false,
                currentStep: 0,
                steps: ['vehicleDetails', 'vehicleCondition', 'valuation'],
                showError: false,
                ajaxLoading: false,
                hasNegativeEquity: false,
                isPxDeleteAction: false,
                triggerEventWatcher: true
            };
        },

        props: {
            estValueDisclaimer: {
                required: true,
                type: String
            },

            show: {
                required: false,
                type: Boolean,
                default: false
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

            vehicleTypeUrl: {
                required: false,
                type: String,
                default: ''
            },

            yearUrl: {
                required: false,
                type: String,
                default: ''
            },

            fuelTypeUrl: {
                required: false,
                type: String,
                default: ''
            },

            transmissionUrl: {
                required: false,
                type: String,
                default: ''
            },

            variantUrl: {
                required: false,
                type: String,
                default: ''
            },

            productId: {
                required: false,
                type: [Number, Boolean],
                default: false
            },

            updateStepUrl: {
                required: false,
                type: String,
                default: ''
            },

            isPxRemoved: {
                required: false,
                type: Boolean,
                default: false
            },

            accessedFrom: {
                required: true,
                type: String
            },

            partExchangeFilter: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            financeFilter: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            financeQuote: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            partExchangeInfoBlock: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            carFinderComponent: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            stepCode: {
                required: false,
                type: String,
                default: ''
            },

            checkoutAccordion: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            dropdownLayout: {
                required: true,
                type: Boolean
            }
        },

        computed: {
            activeConditionTitle() {
                let result = 0;
                this.carConditions.forEach((condition) => {
                    if (condition.id === this.activeCondition) {
                        result = condition.title;
                    }
                });
                return result;
            },

            conditionSlider() {
                return this.PartExchangeConditions.$refs.conditionSlider;
            },

            PxVuex() {
                return this.$store.state.general.PX;
            },

            partExchangeCustomCar() {
                return this.$refs.partExchangeCustomCar;
            },

            disabledNext() {
                switch (this.currentStep) {
                    case 0:
                        return (
                            this.carNotFound
                            || this.plateError
                            || !this.hasMileage
                            || !this.hasVrmInput
                            || !this.hasRegistrationYear
                            || (this.subStep === 1 && !this.partExchangeIsValid));
                    case 1:
                        return false;
                    default:
                        return false;
                }
            },

            plateError() {
                return this.PXVrm.plateNumberError;
            },

            carNotFound() {
                return this.PXVrm.carNotFound;
            },

            hasMileage() {
                return this.$store.state.general.PX.mileage > 0 || this.mileage > 0;
            },

            hasRegistrationYear() {
                return this.PXVrm.carInfo.registrationYear !== '';
            },

            tempPxRegistrationYear() {
                return this.tempPx ? this.tempPx.registrationYear : '';
            },

            hasVrmInput() {
                return this.PXVrm.carInfo.vrmInput !== '';
            },

            partExchangeSecondStep() {
                return this.currentStep === 1;
            },

            partExchangeIsValid() {
                return this.checkData();
            },

            customCar() {
                return this.PXVrm.carInfo.vrmInput === this.PXVrm.carInfo.vrm;
            },

            PartExchange() {
                return this.PXVrm.PX;
            },

            isPayInFull() {
                return this.PartExchange.PXValuation.isPayInFullGroup;
            },

            hasSavedPx() {
                return Object.keys(this.financeFilter).length !== 0
                    ? this.financeFilter.showPxInfoBlock && this.hasVrm
                    : this.hasVrm;
            },

            hasVrm() {
                if (this.tempPx) {
                    return !!this.tempPx.vrm && !!this.savedPx.vrm;
                } else {
                    return false
                }
            },

            pxAddedFromDropdownAccessPoints() {
                return this.dropdownLayout
                    && this.savedPx.vrm
                    && this.currentStep === 2;
            },

            productTitle() {
                return `<div class="accordion-px-title">
                        <span>${this.PXVrm.carInfo.title}</span>
                        <div class="px-edit"><span class="icon-edit"></span>
                        <span class="icon-edit-text">${this.translateString('Edit')}</span></div></div>`;
            },

            introText() {
                return `${this.translateString(`You can review the details of your trade-in or add a vehicle
                        to trade in. The estimated trade-in value of your vehicle is subject to an onsite inspection at
                        your preferred BMW Retailer.`)}`;
            },

            PartExchangeConditions() {
                return this.$refs.pxCondition;
            },

            getQuoteConditions() {
                return this.financeQuote && this.PartExchangeConditions;
            },

            valuationResultConditions() {
                if (this.PartExchangeConditions) {
                    return this.PartExchangeConditions.pxAccordion ? this.PartExchangeConditions.valuationResult : true;
                }
            },

            showAccordion() {
                if (this.getQuoteConditions) {
                    return this.PartExchangeConditions.isExpired && this.PartExchangeConditions.partExchangeIsValid;
                } else {
                    return false;
                }
            },

            currentStepPx() {
                if (this.getQuoteConditions) {
                    if (this.PartExchangeConditions.pxAccordion && !this.PartExchangeConditions.valuationResult) {
                        return 1;
                    } else {
                        return this.currentStep;
                    }
                } else {
                    return this.currentStep;
                }
            },

            checkout() {
                return this.accessedFrom === this.PXACCESSPOINTS.CHECKOUT;
            },

            myAccount() {
                return this.accessedFrom === this.PXACCESSPOINTS.MYACCOUNT;
            },

            carFinder() {
                return this.accessedFrom === this.PXACCESSPOINTS.CARFINDER;
            },

            pdp() {
                return this.accessedFrom === this.PXACCESSPOINTS.PDP;
            },

            pxMainTitle() {
                return `${this.translateString('Trade-in (Optional)')}`;
            },

            NegativeEquity() {
                return this.PartExchangeValuationResult.$refs.negativeEquity;
            },

            PartExchangeValuationResult() {
                return this.$refs.partExchangeValuationResult;
            },

            partExchangeVrm() {
                return this.$refs.partExchangeVrm;
            },

            stepsChanged() {
                const { currentStep, subStep } = this;
                return { currentStep, subStep };
            }
        },

        events: {
            'PartExchange::triggerEventTrackerCheck'() {
                this.fireEvent();
                this.isPxDeleteAction = false;
            },

            'PartExchange::changeCarCondition'(newValue) {
                this.activeCondition = newValue;
            },

            'PartExchange::showError'() {
                this.openNotMyCar();
                this.notCorrectDetails();
                this.subStep = 3;
            },

            'PartExchange::moveToValuation'() {
                this.currentStep = 2;
            },

            'GlobalWarning::close'() {
                this.showError = false;
            },

            'success'() {
                this.closePartExchange();
                window.location.reload();
            }
        },

        watch: {
            show: {
                deep: true,
                handler(newVal) {
                    if (newVal) {
                        this.fireEventCheckout();
                    }
                }
            },

            stepsChanged: {
                // eslint-disable-next-line
                handler: function(val) {
                    if (!this.isPxDeleteAction && this.triggerEventWatcher) {
                        this.fireEvent();
                    }

                    this.triggerEventWatcher = true;
                    this.isPxDeleteAction = false;
                },

                deep: true
            }
        },

        methods: {
            translateString,

            closePartExchange() {
                if (this.pdp || this.checkout) {
                    this.financeQuote.editPartExchange = false;
                } else if (this.carFinder) {
                    this.$dispatch('FinanceFilter::closeTradeInModal');
                }
            },

            acceptVRM() {
                this.currentStep = 1;
                this.subStep = 0;
                this.PXVrm.carDetails.result = true;
                this.updateCurrentStep();
            },

            openNotMyCar() {
                this.PXVrm.openCustom();
                this.showError = false;
                this.fireEvent();
            },

            selectMileage() {
                this.hasMileageFocus = true;
            },

            deselectMileage() {
                this.hasMileageFocus = false;
            },

            notCorrectDetails() {
                this.showError = true;
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
                        case 0: // Vehicle Details
                            if (!this.disabledNext) {
                                if (!this.customCar) {
                                    if (this.hasVrm) {
                                        this.ajaxLoading = true;
                                        this.removePX();
                                    } else {
                                        this.PXVrm.getCarDetails().then(() => {
                                            if (!this.carNotFound && !this.plateError) {
                                                this.subStep = 1;
                                                this.updateCurrentStep();

                                                let component;

                                                switch (this.accessedFrom) {
                                                    case this.PXACCESSPOINTS.CARFINDER:
                                                        component = this.financeFilter;
                                                        break;
                                                    case this.PXACCESSPOINTS.PDP:
                                                        component = this.financeQuote;
                                                        break;
                                                    case this.PXACCESSPOINTS.CHECKOUT:
                                                        component = this.financeQuote;
                                                        break;
                                                    case this.PXACCESSPOINTS.MYACCOUNT:
                                                        component = this.partExchangeFilter;
                                                        break;
                                                    default:
                                                        break;
                                                }

                                                if (component.isPxRemoved) {
                                                    component.isPxRemoved = false;
                                                    this.NegativeEquity.updatePxStateInSession(false)
                                                }
                                            } else {
                                                // Custom Vehicle
                                                this.updateCurrentStep();
                                            }
                                        });
                                    }
                                } else {
                                    this.subStep = 1;
                                    this.updateCurrentStep();
                                }
                            }
                            break;
                        case 1: // Vehicle Condition
                            if (this.partExchangeIsValid) {
                                this.getValuation().then(() => {
                                    this.updateCurrentStep();
                                });
                            }
                            break;
                        case 2: // Save Valuation
                            this.ajaxLoading = true;
                            // If valuation details updated from accordion, gets valuation
                            if (this.valuationResultConditions) {
                                this.PXValuation.saveValuation();
                            } else {
                                this.mileage = this.PartExchangeConditions.mileage;
                                this.getValuation();
                            }
                            break;
                        default:
                            this.currentStep++;
                            this.updateCurrentStep();
                            break;
                    }
                }
            },

            removePX() {
                this.ajaxLoading = true;

                this.$http({
                    url: this.resetUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        px_id: this.peId
                    }
                }).then(this.removePXSuccess, this.removePXFail);
            },

            removePXSuccess() {
                this.isPxDeleteAction = true;

                if (this.hasVrm) {
                    this.PXVrm.getCarDetails().then(() => {
                        if (!this.carNotFound && !this.plateError) {
                            /**
                             * Trade-in removed on Vehicle details step if new registration number added
                             * */
                            switch (this.accessedFrom) {
                                case this.PXACCESSPOINTS.CARFINDER:
                                    this.financeFilter.hidePxInfoBlock();
                                    window.EventsBus.$emit('PartExchange::removedPx');
                                    break;
                                case this.PXACCESSPOINTS.MYACCOUNT:
                                    this.$dispatch('Main::resetPX', false);
                                    break;
                                case this.PXACCESSPOINTS.PDP:
                                case this.PXACCESSPOINTS.CHECKOUT:
                                    window.EventsBus.$emit('PartExchange::removedPx');
                                    break;
                                default:
                                    break;
                            }

                            this.isExpired = false;
                            this.peId = '';
                            this.subStep = 1;
                            this.clearCheckboxes();
                            this.PXValuation.clearOutstandingFinanceData();
                        }

                        this.updateCurrentStep();
                        this.ajaxLoading = false;
                    });
                } else {
                    /**
                     * Trade-in removed from Valuation step
                     */
                    switch (this.accessedFrom) {
                        case this.PXACCESSPOINTS.CARFINDER:
                            this.$dispatch('Main::resetPX', false);
                            this.financeFilter.removePXSuccess();
                            break;
                        case this.PXACCESSPOINTS.PDP:
                            this.$dispatch('Quote::ResetPDPTradeIn');
                            // Disables layout, so it doesn't show if user proceeds to add new trade-in without reload
                            this.dropdownLayout = false;
                            break;
                        case this.PXACCESSPOINTS.MYACCOUNT:
                            this.$root.$refs.tradeInMyAccountModal.show = false;
                            this.clearCheckboxes();
                            this.$dispatch('Main::resetPX', false);
                            // Disables layout, so it doesn't show if user proceeds to add new trade-in without reload
                            this.dropdownLayout = false;
                            this.isExpired = false;
                            this.peId = '';
                            break;
                        default:
                            break;
                    }

                    this.PXVrm.resetCarData();
                    this.updateCurrentStep();
                    this.ajaxLoading = false;
                }
            },

            removePXFail(error) {
                this.ajaxLoading = false;
                console.error('My Current Cars:', error);
            },

            updateCurrentStep(newCurrentStep = false) {
                let promise = {};
                let currentStep = newCurrentStep || this.currentStep;
                currentStep = currentStep > 10 && parseInt(currentStep / 10) || currentStep;

                switch (currentStep) {
                    case 0:
                        promise = this.updateCurrentStepInSession(this.PXSTEPS.VALUE_CURRENT_CAR);
                        break;
                    case 1:
                        promise = this.updateCurrentStepInSession(this.PXSTEPS.CONDITION_INFO);
                        break;
                }

                return promise;
            },

            updateCurrentStepInSession(step, url = false) {
                if (!url) {
                    url = this.updateStepUrl;
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

            goToStep(current, sub) {
                if (!sub && current) {
                    this.currentStep = current - 1;
                } else {
                    if (this.PartExchange.openCustom) {
                        this.PartExchange.openCustom = false;
                        this.PXVrm.carInfo.vrm = 0;
                        this.currentStep = 0;
                        this.subStep = 0;
                    } else {
                        this.currentStep = current;
                        this.subStep = sub - 1;
                    }
                }

                this.updateCurrentStep();
            },

            resetTempPx() {
                this.tempPx = {
                    vrm: '',
                    mileage: 0,
                    capId: 0,
                    model: '',
                    title: '',
                    derivative: '',
                    registrationYear: ''
                };
            },

            resetPxData() {
                this.resetSteps();
                this.clearCheckboxes();
                this.resetTempPx();

                if (!this.myAccount) {
                    this.softReset();
                }

                if (this.carFinder) {
                    this.peId = '';
                    this.isExpired = false;
                }
            },

            clearCheckboxes() {
                this.additionalInfo.forEach((checkbox) => {
                    checkbox.checked = checkbox.default;
                });
            },

            saveValuation() {
                this.ajaxLoading = true;
                this.PXValuation.saveValuation();
            },

            setSavedMileage() {
                this.mileage = this.tempPx.mileage;
            },

            resetMileage(isExpired = false) {
                if (isExpired) {
                    this.subStep = 0;
                    this.currentStep = 1;
                    this.valuationResult = false;
                }

                this.setSavedMileage();
            },

            /**
             *  Resets steps.
             *  In case the accordion layout is present (My Account) and vehicle is not removed,
             *  sets step for trade-in navigation bar.
             */
            resetSteps() {
                this.currentStep = this.myAccount && this.savedPx.peId !== '' ? 2 : 0;

                this.subStep = 0;
                this.openCustom = false;
            },

            updateOutstandingFinance() {
                this.$store.commit('setPXValuationOutstandingFinance', this.PartExchange.PXValuation.outstandingFinance);
            },

            skipPXWithOutPX() {
                this.isPxDeleteAction = true;

                if (this.carFinder) {
                    this.PartExchangeValuationResult.skipPXWithOutPX();
                } else {
                    this.skipStep();
                }
            },

            resetSavedPx() {
                if (!Array.isArray(this.savedPx) && typeof this.savedPx === 'object') {
                    for (const prop of Object.getOwnPropertyNames(this.savedPx)) {
                        delete this.savedPx[prop];
                    }
                }
            },

            skipStep() {
                this.updateCurrentStep();

                switch (this.accessedFrom) {
                    case this.PXACCESSPOINTS.CARFINDER:
                        EventsBus.$emit('CarFinder::nextStep', false, true, false); // force update step, but don't go next
                        this.carFinderComponent.updateCurrentStep().then(() => { // this updates step data and after clear PX
                            this.removePX();
                            this.resetPxData();
                            this.closePartExchange();

                            if (!this.financeFilter.openFromTradeInBlock && !this.financeQuote) {
                                this.$dispatch('FinanceFilterOverlay::openMoreFiltersOverlay');
                            }
                        });
                        break;
                    case this.PXACCESSPOINTS.PDP:
                        this.removePX();
                        this.resetPxData();
                        this.financeQuote.editPartExchange = false;
                        this.financeQuote.removeOverlayParamFromURL(this.OVERLAYSEARCHPARAMS.TRADEIN);
                        break;
                    case this.PXACCESSPOINTS.CHECKOUT:
                        this.removePX();
                        this.resetPxData();
                        this.financeQuote.editPartExchange = false;
                        this.resetSavedPx();
                        this.NegativeEquity.resetPxData();
                        // Disables layout, so it doesn't show if user proceeds to add new trade-in without reload
                        this.dropdownLayout = false;
                        break;
                    case this.PXACCESSPOINTS.MYACCOUNT:
                        this.ajaxLoading = true;
                        this.removePX();
                        this.clearCheckboxes();
                        this.resetTempPx();
                        this.resetSavedPx();
                        this.currentStep = 0;
                        break;
                    default:
                        break;
                }
            },

            checkoutContinueWithoutTradeIn() {
                this.softReset();
                this.$dispatch('CheckoutAccordionGroup::nextStep', this.stepCode);
                this.currentStep = 0;
                this.subStep = 0;
            },

            resetOutstandingFinance() {
                this.PartExchangeValuationResult.manualOutstandingFinance = 0;
                this.$store.commit('setPXValuationOutstandingFinance', 0);
            },

            /**
             * Fire event for tracking purposes on initial load of trade-in (Checkout)
             */
            fireEventCheckout() {
                this.fireEventForTracking(
                    this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                    this.getEventConstants().EVENTRACKERVALUES.PXCHECKOUT
                );
            },

            /**
             * Fire event for tracking purposes on initial load of trade-in steps
             */
            fireEvent() {
                switch (this.accessedFrom) {
                    case this.PXACCESSPOINTS.PDP:
                    case this.PXACCESSPOINTS.CARFINDER:
                    case this.PXACCESSPOINTS.MYACCOUNT:
                        if (this.subStep === 0 && !this.openCustom && this.currentStep === 0) {
                            this.fireEventForTracking(
                                this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                                this.getEventConstants().EVENTRACKERVALUES.PXDETAILS
                            );
                        } else if (this.subStep === 1 && !this.openCustom) {
                            this.fireEventForTracking(
                                this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                                this.getEventConstants().EVENTRACKERVALUES.PXCONFIRM
                            );
                        } else if (this.openCustom) {
                            this.fireEventForTracking(
                                this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                                this.getEventConstants().EVENTRACKERVALUES.PXCUSTOM
                            );
                        } else if (this.partExchangeSecondStep) {
                            this.fireEventForTracking(
                                this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                                this.getEventConstants().EVENTRACKERVALUES.PXCONDITION
                            );
                        } else if ((this.currentStep === 2 && !this.openCustom) || this.pxAddedFromDropdownAccessPoints) {
                            this.fireEventForTracking(
                                this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                                this.getEventConstants().EVENTRACKERVALUES.PXVALUATION
                            );
                        } else {
                            return false;
                        }

                        break;
                    default:
                        break;
                }
            }
        },

        ready() {
            if (!this.savedPx.vrm && this.tempPx
                && this.tempPx.mileage && this.tempPx.vrm) {
                this.PXVrm.carInfo.vrmInput = this.tempPx.vrm;
                this.PXVrm.carInfo.vrm = this.tempPx.vrm;
                this.PXVrm.carInfo.title = this.tempPx.title;
                this.PXVrm.carInfo.model = this.tempPx.model;
                this.PXVrm.carInfo.capId = this.tempPx.capId;
                this.PXVrm.carInfo.derivative = this.tempPx.derivative;
                this.mileage = this.tempPx.mileage;
                this.PXVrm.carDetails.result = true;
            }

            if (!this.savedPx.car_condition && this.tempPx && this.tempPx.car_condition) {
                const data = {
                    id: parseInt(this.tempPx.car_condition)
                };
                this.activeCondition = data.id;
                this.selectActiveCondition(data);
            }

            // Change steps for trade-in navigation bar, in case the accordion layout is present (PDP, Checkout)
            if (this.peId && this.pdp || this.peId && this.checkout) {
                this.subStep = 0;
                this.currentStep = 2;
                this.triggerEventWatcher = false;
            }
        },

        created() {
            /**
             * Trade-in removed from Valuation
             */
            window.EventsBus.$on('PartExchange::resetPxData', () => {
                this.isPxDeleteAction = true;

                if (this.accessedFrom === this.PXACCESSPOINTS.CHECKOUT) {
                    this.removePX();
                    this.clearCheckboxes();
                    this.currentStep = 0;
                    this.subStep = 0;
                    this.resetOutstandingFinance();
                } else if (this.accessedFrom === this.PXACCESSPOINTS.MYACCOUNT) {
                    // Checks if there is already saved trade-in
                    if (this.peId) {
                        this.resetTempPx();
                        this.removePX();
                        this.resetOutstandingFinance();
                    } else {
                        this.clearCheckboxes();
                        this.resetTempPx();
                        this.$dispatch('Main::resetPX', false);
                        // Disables layout, so it doesn't show if user proceeds to add new trade-in without reload
                        this.dropdownLayout = false;
                        this.resetOutstandingFinance();
                        this.ajaxLoading = false;
                    }

                    this.currentStep = 0;
                    this.subStep = 0;
                } else {
                    this.resetPxData();

                    if (this.accessedFrom === this.PXACCESSPOINTS.PDP) {
                        // Disables layout, so it doesn't show if user proceeds to add new trade-in without reload
                        this.dropdownLayout = false;
                    }
                }
            });

            window.EventsBus.$on('FinanceFilter::setSavedPxData', (isExpired) => {
                this.resetMileage(isExpired);
            });

            window.EventsBus.$on('PartExchangeFilter::ajaxLoading', (value) => {
                this.ajaxLoading = value;
            });

            window.EventsBus.$on('PartExchangeFilter::negativeEquity', (value) => {
                this.hasNegativeEquity = value;
            });

            window.EventsBus.$on('PartExchangeFilter::disableSelects', () => {
                this.disableSelects = false;
            });

            window.EventsBus.$on('PartExchangeFilter::hasSavedPx', () => {
                this.subStep = 0;
                this.currentStep = this.hasSavedPx ? 2 : 0;
            });

            window.EventsBus.$on('PartExchange::triggerEventTrackerCheck', () => {
                this.fireEvent();
                this.isPxDeleteAction = false;
            });
        },

        components: {
            appPartExchangeVrm,
            appPartExchangeValuation,
            appPartExchangeCustomCar,
            appSelect,
            appMoreInfo,
            appModal,
            appNavigationSteps,
            appPartExchangeCondition,
            appAccordion,
            appAccordionGroup
        }
    });
</script>
