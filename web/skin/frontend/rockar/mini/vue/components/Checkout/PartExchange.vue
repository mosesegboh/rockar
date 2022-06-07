<template>
    <div class="compact-px part-exchange px-popup">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <app-part-exchange-custom-car v-show="openCustom" v-ref:part-exchange-custom-car></app-part-exchange-custom-car>

        <div class="part-exchange-regular row" v-show="!openCustom">
            <div class="part-exchange-left equal-height col-6">

                <app-part-exchange-vrm v-ref:part-exchange-vrm></app-part-exchange-vrm>

                <div class="px-mileage-block">
                    <p class="px-light-text">{{ 'What year did you first register it in?' | translate }}</p>
                    <input
                        type="number"
                        placeholder="Year"
                        min="minYear"
                        max="currentYear"
                        maxlength="4"
                        class="text-center"
                        v-model="registrationYear | regYearFormat"
                        :disabled="valuationResult"
                    >
                </div>

                <div class="px-mileage-block">
                    <p class="px-light-text">{{ 'Then your car\'s mileage is:' | translate }}</p>
                    <input type="text" placeholder="0" class="px-input keyboard-numbers" v-model="mileage | numberFormat">
                </div>

                <div v-if="PXVrm">
                    <div class="px-car-info" v-if="showCurrentCarBlock">
                        <p>{{ 'Okay so your current car is' | translate }}:</p>
                        <p class="px-car-name">{{ currentCarDetails }}</p>
                    </div>
                </div>

                <div class="button-wrap">
                    <button class="button-default"
                            @mousedown="$refs.partExchangeVrm.getCarDetails"
                            v-if="isButtonGoVisible"
                    >
                        {{ 'Go' | translate }}
                    </button>
                </div>

                <div class="px-condition-block">
                    <p class="px-light-text">{{ 'I\'d rate my car as:' | translate }}</p>
                    <app-range-slider v-if="activeSliderCondition" :use-id="true" :active-on-slide="true" :options="carConditions" :display-steps="true" :active="activeSliderCondition" :is-disabled="disableSelects" custom-event="PartExchange::changeCondition" custom-event-change="PartExchange::changeCondition" v-ref:condition-slider></app-range-slider>

                    <template v-for="condition in carConditions">
                        <div class="px-condition-list" v-if="activeCondition == condition.id">
                            <p class="px-condition-title" >{{ condition.title }}</p>
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
                        {{ 'The value of your car has changed. Update your valuation' | translate }}
                    </div>

                    <button :class="[checkData() ? 'button-default' : 'button-disabled']" :disabled="!checkData()" @click="getValuation()" v-if="!valuationResult">{{ isExpired ? 'Update Valuation' : 'Get Valuation' | translate }}</button>
                    <button class="button-empty" @click="continueWithout()" v-if="!valuationResult && canEdit">{{ 'Continue without trade-in' | translate }}</button>
                </div>

                <app-checkout-part-exchange-valuation
                        v-ref:part-exchange-valuation-result
                        :product-id="productId"
                        :lead-time="leadTime">
                </app-checkout-part-exchange-valuation>
            </div>
        </div>
    </div>
</template>

<script>
    import appSelect from 'core/components/Elements/Select';
    import PartExchange from 'mini/components/Shared/PartExchange';
    import appTooltip from 'core/components/Elements/Tooltip';
    import appPartExchangeVrm from 'mini/components/PartExchange/PartExchangeVRM';
    import appPartExchangeCustomCar from 'mini/components/PartExchange/PartExchangeCustomCar';
    import appCheckoutPartExchangeValuation from 'mini/components/Checkout/PartExchangeValuation';
    import numeral from 'numeral';


    export default Vue.extend({
        mixins: [PartExchange],

        props: {
            productId: {
                required: false,
                type: Number
            },
        },

        data() {
            return {
                currentYear: new Date().getFullYear(),
                minYear: 1885,
                registrationYear: null
            };
        },

        filters: {
            regYearFormat: {
                read(number) {
                    return numeral(number).value();
                },

                write(number) {
                    number = numeral(number.toString().replace(/\D/g, '')).value();

                    if (number < this.minYear) {
                        number = this.minYear;
                    } else if (number > this.currentYear) {
                        number = this.currentYear;
                    }

                    return number;
                }
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
            },

            showCurrentCarBlock() {
                return this.PXVrm.carDetails.result && !this.PXVrm.carNotFound;
            },

            currentCarDetails() {
                return this.PXVrm.carInfo.title;
            },
        },

        methods: {
            selectActiveCondition(data) {
                this.$dispatch('PartExchange::changeCondition', data.id);
            },

            updatePartExchange(data) {
                if (!this.canEdit) {
                    var mySavedCar = this.$parent.$parent;

                    mySavedCar.pxValue = this.PXValuation.partExchangeValue;
                    mySavedCar.mileage = this.mileage;
                    mySavedCar.outstandingFinance = this.PXValuation.outstandingFinance;
                    mySavedCar.expireDate = data.data.expire; // WIP
                }
            },

            closePartExchange() {
                if (this.PXVrm.carInfo.title && this.PXVrm.carInfo.vrm) {
                    this.$dispatch('CheckoutAccordionGroup::nextStep', this.$parent.stepCode);
                }

                this.closeModal();
            },

            continueWithout() {
                this.softReset();
                this.closePartExchange();
            },

            softResetFiltersSuccess() {
                this.$dispatch('CheckoutAccordionGroup::nextStep', this.$parent.stepCode);
                this.ajaxLoading = false;
            },

            getStatusBar() {
                let statusBar = this.translateString('Add');
                let statusAction = 'add';

                if (this.PXVrm.carInfo.title && this.PXVrm.carInfo.vrm) {
                    const titleValue = this.PXValuation.leadTimePartExchangeValue;
                    statusBar = `${this.translateString('PLATE NUMBER:')} ${this.PXVrm.carInfo.vrm} - ${currencySymbol}${numeral(titleValue).format('0,0.00')}`;
                    statusAction = 'edit';
                }

                return { message: statusBar, action: statusAction };
            },

            loadActiveValuation(valuation) {
                this.carConditions.forEach((condition, index) => {
                    if (condition.id === parseInt(valuation.car_condition)) {
                        this.activeCondition = condition.id;
                    }
                });

                if (valuation.additional_info) {
                    valuation.additional_info.forEach((checkbox, index) => {
                        this.additionalInfo[index].checked = checkbox.checked === 'true';
                    });
                }

                this.mileage = valuation.mileage;
                this.disableSelects = false;
                this.isExpired = valuation.is_expired;

                this.PXVrm.carInfo.capId = parseInt(valuation.px_id);
                this.PXVrm.carInfo.model = valuation.cap.model;
                this.PXVrm.carInfo.title = valuation.cap_extended.product_name;
                this.PXVrm.carInfo.vrm = valuation.vrm;
                this.PXVrm.carInfo.vrmInput = valuation.vrm;
                this.PXVrm.carDetails.result = true;
                this.PXVrm.carDetails.vrmDisabled = true;

                this.PXValuation.valuationResult = true;
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

            softResetFiltersFail(error) {
                this.ajaxLoading = false;
                if (error.data.redirect && error.status === 401) {
                    this.$root.loggedOutPopup(error.data.redirect);
                    return;
                } else {
                    if (error.statusText) {
                        alert(error.statusText)
                    }
                    return;
                }
            }
        },

        components: {
            appSelect,
            appTooltip,
            appPartExchangeVrm,
            appPartExchangeCustomCar,
            appCheckoutPartExchangeValuation
        }
    });
</script>
