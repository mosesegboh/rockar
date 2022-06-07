<template>
    <div class="compact-px part-exchange px-popup">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <app-part-exchange-custom-car v-show="openCustom" v-ref:part-exchange-custom-car></app-part-exchange-custom-car>

        <div class="part-exchange-regular row" v-show="!openCustom">
            <div class="part-exchange-left equal-height col-6">

                <app-part-exchange-vrm v-ref:part-exchange-vrm></app-part-exchange-vrm>

                <div class="px-mileage-block">
                    <p class="px-light-text">{{ 'Your vehicle\'s mileage is:' | translate }}</p>
                    <input type="text"
                        placeholder="0"
                        @focus="selectMileage"
                        @blur="deselectMileage"
                        :class="[checkData() ? 'active' : '', 'active', 'px-input', 'keyboard-numbers']"
                        v-model="mileage | numberKMFormat">
                </div>

                <div v-if="PXVrm">
                    <div class="px-car-info" v-if="showCurrentCarBlock">
                        <p>{{ 'Okay so your current vehicle is' | translate }}:</p>
                        <p class="px-car-name">{{ currentCarDetails }}</p>
                    </div>
                </div>

                <div class="button-wrap">
                    <button class="button-default px-go"
                            @click="$refs.partExchangeVrm.getCarDetails"
                            v-if="isButtonGoVisible"
                    >
                        {{ 'Go' | translate }}
                    </button>
                </div>

                <div class="px-condition-block">
                    <p class="px-light-text">{{ 'I\'d rate the condition of my vehicle as:' | translate }}</p>

                    <app-range-slider v-if="activeSliderCondition" :use-id="true"
                                      :active-on-slide="true" :options="carConditions" :display-steps="true"
                                      :active="activeSliderCondition" :is-disabled="disableSelects"
                                      custom-event="PartExchange::changeCondition" custom-event-change="PartExchange::changeCondition"
                                      v-ref:condition-slider>
                    </app-range-slider>

                    <template v-for="condition in carConditions">
                        <div class="px-condition-list" v-if="activeCondition == condition.id">
                            <p class="px-condition-title">{{ condition.title }}</p>
                            <p class="px-light-text">{{{ condition.body }}}</p>
                        </div>
                    </template>
                </div>
            </div>

            <div class="part-exchange-right equal-height col-6">
                <div class="px-additional-info-block">

                    <div class="px-additional-info-checkbox" v-for="info in additionalInfo">
                        <input type="checkbox" :id="'info-checkbox'+info.id" v-model="info.checked" :disabled="disableSelects">
                        <label :for="'info-checkbox'+info.id" ><span></span></label>

                        <div class="info-label-wrap">
                            <label :for="'info-checkbox'+info.id" class="px-light-text">{{ info.title }}</label>
                                <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                                    <span class="action-badge info-small" slot="tooltipElement"></span>

                                    <div slot="tooltipContent">
                                        <p>{{ info.hint }}</p>
                                    </div>
                                </app-tooltip>
                        </div>

                        <p :class="{ 'warning' : !info.isRequired }" v-if="info.error.length > 0 && !info.checked">{{ info.error }}</p>
                    </div>
                </div>

                <div class="px-submit-block">
                    <div class="px-expired-msg" v-if="!valuationResult && isExpired">
                        {{ 'The value of your vehicle has changed. Update your valuation' | translate }}
                    </div>

                    <p class="px-light-text">{{ '*The "Trade-In Value" of your vehicle is an estimate provided and many factors that cannot be assessed without a physical inspection of the vehicle may affect actual value. The final or actual "Trade-In Value" for your vehicle will be confirmed upon physical inspection of your vehicle having been undertaken by an authorised and approved BMW Motorrad retailer.' | translate }}</p>

                    <button :class="[checkData() ? 'button-default' : 'button-disabled', 'get-val']" :disabled="!checkData()" @click="getValuation()" v-if="!valuationResult">{{ isExpired ? 'Update Your Valuation' : 'Get Valuation' | translate }}</button>
                    <button class="button-empty no-val" @click="continueWithout()" v-if="!valuationResult && canEdit">{{ 'Continue without Trade-in' | translate }}</button>
                </div>

                <app-account-part-exchange-valuation
                        v-ref:part-exchange-valuation-result
                        :product-id="productId"
                        :lead-time="leadTime">
                </app-account-part-exchange-valuation>
            </div>
        </div>
    </div>
</template>

<script>
    import corePartExchange from 'motorrad/components/MyAccount/PartExchange';
    import appSelect from 'core/components/Elements/Select';
    import PartExchange from 'core/components/Cap/Shared/PartExchange';
    import appTooltip from 'core/components/Elements/Tooltip';
    import appPartExchangeVrm from 'motorrad/components/Configurator/PartExchangeVRM';
    import appPartExchangeCustomCar from 'motorrad/components/PartExchange/PartExchangeCustomCar';
    import appAccountPartExchangeValuation from 'motorrad/components/MyAccount/PartExchangeValuation';
    import numeral from 'numeral';

    export default corePartExchange.extend({
        mixins: [PartExchange],

        props: {
            vehicleTypeUrl: {
                required: false,
                type: String
            },
            fuelTypeUrl: {
                required: false,
                type: String
            },

            transmissionUrl: {
                required: false,
                type: String
            },
            variantUrl: {
                required: false,
                type: String
            }
        },

        computed: {
            activeConditionSelectIndex() {
                var result = 0;
                this.carConditions.forEach((condition, index) => {
                    if (condition.id === this.activeCondition) {
                        result = index;
                    }
                });
                return result;
            }
        },

        data() {
            return {
                hasMileageFocus: false,
                currentYear: new Date().getFullYear(),
                minYear: 1885,
                registrationYear: null
            };
        },

        filters: {
            numberKMFormat: {
                read(number) {
                    number = Math.floor(number);
                    if (isNaN(number)) {
                        number = 0;
                    }
                    if (this.hasMileageFocus) {
                        return number !== 0 ? numeral(number).format('0,0') : '';
                    } else {
                        return numeral(number).format('0,0') + this.translateString(' KM');
                    }
                },
                write(number) {
                    return Math.floor(numeral(number).value());
                }
            }
        },

        methods: {

            selectMileage() {
                this.hasMileageFocus = true;
            },

            deselectMileage() {
                this.hasMileageFocus = false;
            },

            selectActiveCondition(data) {
                this.$dispatch('PartExchange::changeCondition', data.id);
            },

            continueWithout() {
                this.softReset();
                this.$dispatch('PartExchangeVRM::resetSlider');
                this.closePartExchange();
            },

            softResetFiltersSuccess() {
                this.$dispatch('Main::forceUpdateFinanceQuote');
                this.$dispatch('CheckoutAccordionGroup::nextStep', this.$parent.stepCode);
                this.ajaxLoading = false;
            },

            loadValuationSuccess(valuation, noAjax = false) {
                if (!noAjax) {
                    valuation = valuation.data;
                }

                this.ajaxLoading = false;

                this.activeCondition = parseInt(valuation.car_condition);
                this.mileage = valuation.mileage;

                if (valuation.additional_info) {
                    valuation.additional_info.forEach((checkbox, index) => {
                        this.additionalInfo[index].checked = checkbox.checked === 'true';
                    });
                }

                this.disableSelects = false;
                this.isExpired = valuation.is_expired;

                this.PXVrm.carInfo.capId = parseInt(valuation.px_id);
                this.PXVrm.carInfo.model = valuation.cap.model;
                this.PXVrm.carInfo.title = valuation.cap_extended.product_name;
                this.PXVrm.carInfo.vrm = valuation.vrm;
                this.PXVrm.carInfo.vrmInput = valuation.vrm;
                this.PXVrm.carDetails.result = true;
                this.PXVrm.carDetails.vrmDisabled = true;

                this.PXValuation.partExchangeValue = parseFloat(valuation.part_exchange_value);
                this.PXValuation.outstandingFinance = parseFloat(valuation.outstanding_finance);

                if (this.isExpired) {
                    this.valuationResult = false;
                    this.PXValuation.valuationCompleted = false;
                    this.PXValuation.valuationResult = false;
                } else {
                    this.valuationResult = true;
                    this.PXValuation.valuationCompleted = true;
                    this.PXValuation.valuationResult = true;
                }
            },

            checkInputs() {
                if (this.PXVrm) {
                    const carInfo = this.PXVrm.carInfo;

                    if (carInfo.registrationYear && carInfo.vrmInput && this.mileage > 0) {
                        return true;
                    }
                }

                return false;
            }
        },

        components: {
            appSelect,
            appTooltip,
            appPartExchangeVrm,
            appPartExchangeCustomCar,
            appAccountPartExchangeValuation
        }
    });
</script>
