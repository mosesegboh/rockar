<template>
    <div class="px-popup-content">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <h4 :class="checkout ? 'step-header' : 'h4 px-title'">{{ pxMainTitle }}</h4>

        <template v-if="checkout">
            <div class="px-skip">
                <p>
                    {{ introText }}
                </p>
                <div v-if="!pxAddedFromDropdownAccessPoints" class="row">
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
                    <div class="px-popup-info-block mobile">
                        <h3 class="h3 px-step-title">
                            {{ 'Vehicle Details' | translate }}
                        </h3>
                        <p> {{ 'Please fill in your trade-in vehicle details' | translate }}</p>
                        <p> {{ `Receive an instant trade-in estimate on your current vehicle.
                        You can confirm your trade-in value with an onsite vehicle inspection
                        at your nearest MINI Retailer.` | translate }}</p>
                    </div>
                    <div class="px-popup-info-block desktop">
                        <h3 class="h3 px-step-title">
                            {{ 'Vehicle Details' | translate }}
                        </h3>
                        <p> {{ `Please provide the details of your trade-in vehicle to get an instant valuation.
                        Once received, this amount can be confirmed at an on-site vehicle inspection centre or your
                        nearest MINI retailer.` | translate }}</p>
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
                    <button class="button dsp2-outline" @click="openNotMyCar">{{ 'No. This is not my vehicle.' | translate }}</button>
                </div>
                <div class="col-6 col-md-12">
                    <button class="button dsp2-money" @click="acceptVRM()">{{ 'Yes. Please continue.' | translate }}</button>
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
import appPartExchangeVrm from 'dsp2/components/PartExchange/PartExchangeComponents/PartExchangeVRM';
import appPartExchangeValuation from 'mini2/components/PartExchange/PartExchangeComponents/PartExchangeValuation';
import appMoreInfo from 'dsp2/components/Elements/MoreInfo';
import appSelect from 'dsp2/components/Elements/Select';
import appPartExchangeCustomCar from 'dsp2/components/PartExchange/PartExchangeComponents/PartExchangeCustomCar';
import appModal from 'core/components/Elements/Modal';
import appNavigationSteps from 'dsp2/components/PartExchange/PartExchangeNavigation/NavigationSteps';
import appPartExchangeCondition from 'dsp2/components/PartExchange/PartExchangeComponents/PartExchangeCondition';
import appAccordion from 'dsp2/components/Elements/Accordion';
import appAccordionGroup from 'dsp2/components/AccordionGroup';
import appPartExchangeCore from 'dsp2/components/PartExchange/PartExchangeCore/PartExchangeCore';

export default appPartExchangeCore.extend({
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
