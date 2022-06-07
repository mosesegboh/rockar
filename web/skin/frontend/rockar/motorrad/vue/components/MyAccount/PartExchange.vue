<template>
    <div class="compact-px part-exchange px-popup">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <app-part-exchange-custom-car v-show="openCustom" v-ref:part-exchange-custom-car></app-part-exchange-custom-car>

        <div class="part-exchange-regular row" v-show="!openCustom">
            <div class="part-exchange-left equal-height col-6">

                <app-part-exchange-vrm v-ref:part-exchange-vrm></app-part-exchange-vrm>

                <div class="px-mileage-block">
                    <p class="px-light-text">{{ 'Then your car\'s mileage is:' | translate }}</p>
                    <input type="text"
                        placeholder="0"
                        @focus="selectMileage"
                        @blur="deselectMileage"
                        @keyup.enter="PXVrm.goNextStep()"
                        :class="[checkData() ? 'active' : '', 'px-input', 'keyboard-numbers']"
                        v-model="mileage | numberMilesFormat">
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
                    <button class="button-empty" @click="continueWithout()" v-if="!valuationResult && canEdit">{{ 'Continue without Trade-In' | translate }}</button>
                </div>

                <app-part-exchange-valuation
                        v-ref:part-exchange-valuation-result
                        :product-id="productId"
                        :lead-time="leadTime">
                </app-part-exchange-valuation>
            </div>
        </div>
    </div>
</template>

<script>
    import appSelect from 'core/components/Elements/Select';
    import PartExchange from 'motorrad/components/Shared/PartExchange';
    import appTooltip from 'core/components/Elements/Tooltip';
    import appPartExchangeVrm from 'motorrad/components/PartExchange/PartExchangeVRM';
    import appPartExchangeCustomCar from 'motorrad/components/PartExchange/PartExchangeCustomCar';
    import appPartExchangeValuation from 'motorrad/components/MyAccount/PartExchangeValuation';

    export default Vue.extend({
        mixins: [PartExchange],

        computed: {
            activeConditionSelectIndex() {
                let result = 0;
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

            closePartExchange() {
                this.closeModal();
            },

            updateCarFinderDetails() {
                const PX = this.$root.$refs.carFinder.PartExchange;
                const mileage = this.mileage;

                this.$root.$refs.carFilter.$refs.partExchangeFilterMenu.PartExchange.mileage = mileage;
                PX.mileage = mileage;
                Object.assign(PX.PXVrm.carInfo, this.PXVrm.carInfo);
                this.$store.state.general.PX.Valuation.valuationResult = !this.$store.state.general.PX.Valuation.valuationResult
            },

            continueWithout() {
                if (this.$root.$refs.carFinder) {
                    this.updateCarFinderDetails()
                } else {
                    this.softReset();
                }
                this.closePartExchange();
            },

            updatedFilters() {
                EventsBus.$emit('CarFinder::applyFilters', null);
            },

            softResetFiltersSuccess() {
                this.ajaxLoading = false;
                this.$dispatch('Main::forceUpdateFinanceQuote');
            },

            softResetFiltersFail() {
                this.ajaxLoading = false;
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

                this.$store.commit('setPXMileage', valuation.mileage);

                this.PXVrm.carInfo.capId = parseInt(valuation.px_id);
                this.PXVrm.carInfo.model = valuation.cap.model;
                this.PXVrm.carInfo.title = valuation.cap_extended.product_name;
                this.PXVrm.carInfo.vrm = valuation.vrm;
                this.PXVrm.carInfo.vrmInput = valuation.vrm;
                this.PXVrm.carInfo.registrationYear = valuation.plate_year;
                this.PXVrm.carDetails.result = true;
                this.PXVrm.carDetails.vrmDisabled = true;

                this.$store.commit('setPXVrmCarInfoCapId', parseInt(valuation.px_id));
                this.$store.commit('setPXVrmCarInfoModel', valuation.cap.model);
                this.$store.commit('setPXVrmCarInfoTitle', valuation.cap_extended.product_name);
                this.$store.commit('setPXVrmCarInfoVrm', valuation.vrm);
                this.$store.commit('setPXVrmCarInfoVrmInput', valuation.vrm);
                this.$store.commit('setPXVrmCarInfoRegistrationYear', valuation.plate_year);
                this.$store.commit('setPXVrmCarDetailsResult', true);
                this.$store.commit('setPXVrmCarDetailsVrmDisabled', true);

                this.PXValuation.valuationResult = true;
                this.PXValuation.partExchangeValue = parseFloat(valuation.part_exchange_value);
                this.PXValuation.outstandingFinance = parseFloat(valuation.outstanding_finance);

                this.$store.commit('setPXValuationValuationResult', true);
                this.$store.commit('setPXValuationPartExchangeValue', parseFloat(valuation.part_exchange_value));
                this.$store.commit('setPXValuationOutstandingFinance', parseFloat(valuation.outstanding_finance));
                this.$store.commit('setPXOutstandingFinanceSettlement', parseInt(valuation.outstanding_finance_settlement));

                if (this.isExpired) {
                    this.valuationResult = false;
                    this.PXValuation.valuationCompleted = false;
                    this.PXValuation.valuationResult = false;
                    this.$store.commit('setPXValuationValuationCompleted', false);
                    this.$store.commit('setPXValuationValuationResult', false);
                } else {
                    this.valuationResult = true;
                    this.PXValuation.valuationCompleted = true;
                    this.PXValuation.valuationResult = true;
                    this.$store.commit('setPXValuationValuationCompleted', true);
                    this.$store.commit('setPXValuationValuationResult', true);
                }
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

                this.valuationResult = true;
                this.disableSelects = false;
                this.isExpired = valuation.is_expired;

                this.PXVrm.carInfo.capId = parseInt(valuation.px_id);
                this.PXVrm.carInfo.model = valuation.cap.model;
                this.PXVrm.carInfo.title = valuation.cap_extended.product_name;
                this.PXVrm.carInfo.vrm = valuation.vrm;
                this.PXVrm.carInfo.vrmInput = valuation.vrm;
                this.PXVrm.carInfo.registrationYear = valuation.plate_year;
                this.PXVrm.carDetails.result = true;
                this.PXVrm.carDetails.vrmDisabled = true;

                this.$store.commit('setPXVrmCarInfoCapId', parseInt(valuation.px_id));
                this.$store.commit('setPXVrmCarInfoModel', valuation.cap.model);
                this.$store.commit('setPXVrmCarInfoTitle', valuation.cap_extended.product_name);
                this.$store.commit('setPXVrmCarInfoVrm', valuation.vrm);
                this.$store.commit('setPXVrmCarInfoVrmInput', valuation.vrm);
                this.$store.commit('setPXVrmCarInfoRegistrationYear', valuation.plate_year);
                this.$store.commit('setPXVrmCarDetailsResult', true);
                this.$store.commit('setPXVrmCarDetailsVrmDisabled', true);

                this.PXValuation.partExchangeValue = parseFloat(valuation.part_exchange_value);
                this.PXValuation.outstandingFinance = parseFloat(valuation.outstanding_finance);

                this.$store.commit('setPXValuationPartExchangeValue', parseFloat(valuation.part_exchange_value));
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

            htmlEntityDecode(encodedHtml) {
                return jQuery('<textarea />').html(encodedHtml).text();
            },

            selectMileage() {
                setTimeout(() => {
                    if (this.$els.pxMilageInput && this.$els.pxMilageInput.value) {
                        const len = this.$els.pxMilageInput.value.length;
                        this.$els.pxMilageInput.setSelectionRange(len, len);
                    }
                }, 10);
                this.hasMileageFocus = true;
            },

            deselectMileage() {
                this.hasMileageFocus = false;
            },

            getValuation() {
                this.$dispatch('CarFinder::changeCarCondition', this.activeCondition);
                return this.PXValuation.getValuation();
            },
        },

        components: {
            appSelect,
            appTooltip,
            appPartExchangeVrm,
            appPartExchangeCustomCar,
            appPartExchangeValuation
        }
    });
</script>
