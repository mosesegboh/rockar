<template>
    <div class="part-exchange">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <app-part-exchange-custom-car v-show="openCustom" v-ref:part-exchange-custom-car></app-part-exchange-custom-car>

        <div class="part-exchange-regular row" v-show="!openCustom">
            <div class="part-exchange-left col-3 col-md-6">

                <app-part-exchange-vrm
                        car-alternative-details="url"
                        v-ref:part-exchange-vrm>
                </app-part-exchange-vrm>

                <div class="px-mileage-block">
                    <p class="px-regular-heading">{{ 'My car\'s mileage is' | translate }}</p>
                    <input type="text" placeholder="0" class="px-input keyboard-numbers" v-model="mileage | numberFormat" :disabled="disableSelects">
                </div>

            </div>

            <div class="part-exchange-middle col-4 col-md-6">
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

            <div class="part-exchange-right col-5 col-md-12">
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
                    <button :class="[checkData() ? 'button-default' : 'button-disabled']" :disabled="!checkData()" @click="getValuation()" v-if="!valuationResult">{{ 'Get Valuation' | translate }}</button>
                    <button class="button button-empty" @click="resetFilters()" v-if="!valuationResult">{{ '...Or start again' | translate }}</button>
                </div>
            </div>
        </div>

        <app-part-exchange-valuation v-ref:part-exchange-valuation-result></app-part-exchange-valuation>
    </div>
</template>

<script>
    import PartExchange from 'bmw/components/Shared/PartExchange';
    import appPartExchangeVrm from 'bmw/components/PartExchange/PartExchangeVRM';
    import appPartExchangeCustomCar from 'bmw/components/PartExchange/PartExchangeCustomCar';
    import appPartExchangeValuation from 'bmw/components/CarFinder/PartExchangeValuation';
    import appTooltip from 'core/components/Elements/Tooltip';
    import appSelect from 'core/components/Elements/Select';
    import numeral from 'numeral';
    import translateString from 'core/filters/Translate';

    export default Vue.extend({
        mixins: [PartExchange],

        computed: {
            additionalInfoErrors() {
                const result = [];
                this.additionalInfo.forEach((info) => {
                    if (info.error.length > 0 && !info.checked) {
                        result.push(info.error);
                    }
                });
                return result;
            },

            activeConditionSelectIndex() {
                let result = 0;
                this.carConditions.forEach((condition, index) => {
                    if (condition.id === this.activeCondition) {
                        result = index;
                    }
                });
                return result;
            }
        },

        methods: {
            translateString,

            selectActiveCondition(data) {
                this.$dispatch('PartExchange::changeCondition', data.id);
            },

            closePartExchange() {
                this.$parent.show = false;
                this.$root.$refs.carFilter.$parent.show = true;
            },

            softResetFiltersSuccess() {
                EventsBus.$emit('FinanceFilter::applyFilter');
                this.ajaxLoading = false;
            },

            loadActiveValuation(valuation) {
                this.carConditions.forEach((condition) => {
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
                this.registrationYear = valuation.plate_year;

                this.valuationResult = true;
                this.disableSelects = false;
                this.isExpired = valuation.is_expired;

                this.PXVrm.carInfo.capId = parseInt(valuation.px_id);

                if (valuation.cap) {
                    this.PXVrm.carInfo.model = valuation.cap.model;
                    this.PXVrm.carDetails.result = true;
                    this.$store.commit('setPXVrmCarInfoModel', valuation.cap.model);
                    this.$store.commit('setPXVrmCarDetailsResult', true);
                }

                if (valuation.cap_extended) {
                    this.PXVrm.carInfo.title = valuation.cap_extended.product_name;
                }

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
                this.PXValuation.valuationCompleted = true;
                this.PXValuation.outstandingFinance = parseFloat(valuation.outstanding_finance);
                this.$store.commit('setPXValuationValuationResult', true);
                this.$store.commit('setPXValuationPartExchangeValue', parseFloat(valuation.part_exchange_value));
                this.$store.commit('setPXValuationValuationCompleted', true);
                this.$store.commit('setPXValuationOutstandingFinance', parseFloat(valuation.outstanding_finance));

                if (this.isUsedInFinance) {
                    const titleValue = currencySymbol + numeral(valuation.part_exchange_value - valuation.outstanding_finance).format('0,0.00');
                    this.$parent.title = `${this.translateString('Vehicle for Trade in:')}<span class="description"><span>${this.PXVrm.carInfo.title}: <b>${titleValue}</b></span></span>`;
                } else {
                    this.$parent.title = this.translateString('I\'m not swapping a vehicle');
                }
            },

            hardResetPartExchange(callback) {
                this.valuationResult = false;
                this.mileage = 0;
                this.registrationYear = null;

                this.PXVrm.carInfo.capId = null;
                this.PXVrm.carInfo.model = null;
                this.PXVrm.carInfo.title = null;
                this.PXVrm.carInfo.vrm = null;
                this.PXVrm.carInfo.vrmInput = '';
                this.PXVrm.carDetails.result = false;
                this.PXVrm.carDetails.vrmDisabled = false;
                this.PXVrm.carNotFound = false;
                this.PXVrm.carAlternatives = [];
                this.PXVrm.registrationYear = null;

                this.PXValuation.valuationResult = false;
                this.PXValuation.partExchangeValue = 0;
                this.PXValuation.valuationCompleted = false;
                this.PXValuation.outstandingFinance = 0;

                this.additionalInfo.forEach((checkbox) => {
                    checkbox.checked = checkbox.default;
                });

                this.carConditions.forEach((condition, index) => {
                    if (condition.is_default) {
                        this.activeCondition = condition.id;
                    }
                });

                if (this.resetUrl) {
                    this.$http({
                        url: this.resetUrl,
                        data: {
                            px_id: this.peId
                        }
                    }).then(callback);
                }

                this.$parent.title = this.translateString('Do you have an existing car to trade-in?');
                this.disableSelects = true;
            },

            changeButtonTitle() {
                if (this.PXVrm.carDetails.result === false && this.$parent.show === false) {
                    this.$parent.type = 'car-not-applied';
                } else {
                    this.$parent.type = 'edit-close';
                }
            }
        },

        created() {
            EventsBus.$on('AccordionGroup::toggleAccordion', () => {
                if (!this.$parent.disabled) {
                    this.changeButtonTitle();
                }
            });

            EventsBus.$on('CarFinder::saveValuationSuccess', () => {
                this.changeButtonTitle();
            });

            this.$store.commit('setPxFutureValueBlock', this.pxFutureValueBlock);
            this.$store.commit('setPxFutureValueEnabled', this.pxFutureValueEnabled);
        },

        ready() {
            this.changeButtonTitle();
        },

        components: {
            appPartExchangeVrm,
            appPartExchangeCustomCar,
            appPartExchangeValuation,
            appTooltip,
            appSelect
        }
    });
</script>
