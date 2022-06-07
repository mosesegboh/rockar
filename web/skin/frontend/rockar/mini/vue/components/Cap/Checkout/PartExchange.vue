<template>
    <div class="compact-px part-exchange px-popup">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <app-part-exchange-custom-car v-show="openCustom" v-ref:part-exchange-custom-car></app-part-exchange-custom-car>

        <div class="part-exchange-regular row" v-show="!openCustom">
            <div class="part-exchange-left equal-height col-6">
                <app-part-exchange-vrm
                    v-ref:part-exchange-vrm
                    :vehicle-brand-name="vehicleBrandName"
                >
                </app-part-exchange-vrm>

                <div class="px-mileage-block">
                    <p class="px-light-text">{{ 'My car\'s mileage is:' | translate }}</p>
                    <input type="text" placeholder="0" class="px-input keyboard-numbers" v-model="mileage | numberMilesFormat"
                           @focus="selectMileage"
                           @blur="deselectMileage">
                </div>

                <div v-if="PXVrm">
                    <div class="px-car-info" v-if="showCurrentCarBlock">
                        <p>{{ 'Okay so your current car is' | translate }}:</p>
                        <p class="px-car-name">{{ currentCarDetails }}</p>
                    </div>
                </div>

                <div class="button-wrap">
                    <button class="button-default"
                            @click="$refs.partExchangeVrm.getCarDetails"
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
                        <div class="px-additional-info-list">
                            <input type="checkbox" :id="'info-checkbox'+info.id" v-model="info.checked" :disabled="disableSelects">
                            <label :for="'info-checkbox'+info.id" ><span></span></label>

                            <div class="info-label-wrap">
                                <label :for="'info-checkbox'+info.id" class="px-light-text">{{ info.title }}</label>
                                <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                                    <span class="action-badge info" slot="tooltipElement"></span>
                                    <div slot="tooltipContent">
                                        <p>{{ info.hint }}</p>
                                    </div>
                                </app-tooltip>
                            </div>
                        </div>
                        <p :class="{ 'warning' : !info.isRequired }" v-if="info.error.length > 0 && !info.checked">{{ info.error }}</p>
                    </div>
                </div>

                <div class="px-submit-block">
                    <div class="px-expired-msg" v-if="!valuationResult && isExpired">
                        {{ 'The value of your car has changed. Update your valuation' | translate }}
                    </div>

                    <button :class="[checkData() ? 'button-default' : 'button-disabled']" :disabled="!checkData()" @click="getValuation()" v-if="!valuationResult">{{ isExpired ? 'Update Valuation' : 'Get Valuation' | translate }}</button>
                    <button class="button-empty" @click="continueWithout()" v-if="!valuationResult && canEdit">{{ 'Continue Without Trade-In' | translate }}</button>
                </div>

                <app-checkout-part-exchange-valuation
                    v-ref:part-exchange-valuation-result
                    :product-id="productId"
                    :lead-time-part-exchange-value="savedPxLeadTimeValue"
                    :lead-time="leadTime">
                </app-checkout-part-exchange-valuation>
            </div>
        </div>
    </div>
</template>

<script>
    import corePartExchange from 'mini/components/Checkout/PartExchange';
    import appSelect from 'core/components/Elements/Select';
    import PartExchange from 'core/components/Cap/Shared/PartExchange';
    import appTooltip from 'core/components/Elements/Tooltip';
    import appPartExchangeVrm from 'mini/components/Cap/Checkout/PartExchangeVRM';
    import appPartExchangeCustomCar from 'mini/components/PartExchange/PartExchangeCustomCar';
    import appCheckoutPartExchangeValuation from 'mini/components/Checkout/PartExchangeValuation';
    import numeral from 'numeral';

    export default corePartExchange.extend({
        mixins: [PartExchange],

        props: {
            vehicleBrandName: {
                required: false,
                type: String,
                default: 'BMW'
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
                        derivative: ''
                    };
                }
            },
            vehicleTypeUrl: {
                required: false,
                type: String
            },
            yearUrl: {
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

        filters: {
            numberMilesFormat: {
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

            isButtonGoVisible() {
                return this.$refs.partExchangeVrm !== null && this.$refs.partExchangeVrm.notCarFinderStep && !this.$store.state.general.PX.Vrm.carDetails.result;
            },

            isButtonCancelVisible() {
                return this.$refs.partExchangeVrm !== null && this.$refs.partExchangeVrm.notCarFinderStep && this.$store.state.general.PX.Vrm.carDetails.result;
            },

            isButtonCancelDisabled() {
                return this.$refs.partExchangeVrm !== null && (this.$refs.partExchangeVrm.carInfo.vrmInput.length <= 0);
            },

            savedPxLeadTimeValue() {
                return this.saved && !isNaN(this.savedPx.updated_part_exchange_value) ? Math.floor(this.savedPx.updated_part_exchange_value) : 0;
            }
        },

        methods: {
            selectActiveCondition(data) {
                this.$dispatch('PartExchange::changeCondition', data.id);
            },

            continueWithout() {
                this.softReset();
                this.$dispatch('PartExchangeVRM::resetSlider');
                this.closeModal();
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

                if (valuation.cap) {
                    this.PXVrm.carInfo.model = valuation.cap.model;
                    this.PXVrm.carDetails.result = true;
                    this.$store.commit('setPXVrmCarInfoModel', valuation.cap.model);
                    this.$store.commit('setPXVrmCarDetailsResult', true);
                }

                this.PXVrm.carInfo.model = valuation.cap.model;
                this.PXVrm.carInfo.title = valuation.cap_extended.product_name;

                if (valuation.vrm) {
                    this.PXVrm.carInfo.vrm = valuation.vrm;
                    this.PXVrm.carInfo.vrmInput = valuation.vrm;
                    this.PXVrm.carInfo.registrationYear = valuation.plate_year;
                    this.$store.commit('setPXVrmCarInfoVrmInput', valuation.vrm);
                    this.$store.commit('setPXVrmCarInfoVrm', valuation.vrm);
                    this.$store.commit('setPXVrmCarInfoRegistrationYear', valuation.plate_year);
                }

                this.PXVrm.carDetails.vrmDisabled = true;
                this.PXValuation.valuationResult = true;
                this.PXValuation.partExchangeValue = parseFloat(valuation.part_exchange_value);
                this.PXValuation.outstandingFinance = parseFloat(valuation.outstanding_finance);
                this.PXValuation.leadTimePartExchangeValue = parseFloat(valuation.updated_part_exchange_value);
                this.$store.commit('setPXValuationValuationResult', true);
                this.$store.commit('setPXValuationPartExchangeValue', parseFloat(valuation.part_exchange_value));
                this.$store.commit('setPXValuationValuationCompleted', true);
                this.$store.commit('setPXValuationOutstandingFinance', parseFloat(valuation.outstanding_finance));
                this.$store.commit('setPXOutstandingFinanceSettlement', parseInt(valuation.outstanding_finance_settlement));

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
            },

            selectMileage() {
                this.hasMileageFocus = true;
            },

            deselectMileage() {
                this.hasMileageFocus = false;
            },

            /**
             * Method taken from core, Rewritten to satisfy coding standards
             *
             * @param {Object} data
             */
            updatePartExchange(data) {
                if (!this.canEdit) {
                    this.$store.commit('setMySavedCarPxValue', this.PXValuation.partExchangeValue);
                    this.$store.commit('setMySavedCarMileage', this.mileage);
                    this.$store.commit('setMySavedCarOutstandingFinance', this.PXValuation.outstandingFinance);
                    this.$store.commit('setMySavedCarExpireDate', data.data.expire);
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
