<template>
    <div class="compact-px part-exchange px-popup">
        <div class="general-preloader" v-show="ajaxLoading">
            <div class="show-loading"></div>
        </div>

        <app-part-exchange-custom-car v-show="openCustom" v-ref:part-exchange-custom-car></app-part-exchange-custom-car>

        <div class="part-exchange-regular row" v-show="!openCustom">
            <div class="part-exchange-left equal-height col-6">

                <app-part-exchange-vrm v-ref:part-exchange-vrm></app-part-exchange-vrm>

                <div class="px-mileage-block">
                    <p class="px-light-text">{{ 'What\'s your vehicle\'s current mileage?' | translate }}</p>
                    <input type="text"
                           placeholder="0"
                           @focus="selectMileage"
                           @blur="deselectMileage"
                           :class="[checkData() ? 'active' : '', 'active', 'px-input', '  keyboard-numbers']"
                           v-model="mileage | numberKMFormat">
                </div>

                <div v-if="PXVrm">
                    <div class="px-car-info" v-if="showCurrentCarBlock">
                        <p>{{ 'My current vehicle is:' | translate }}:</p>
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
                    <p class="px-light-text">{{ 'I\'d rate the condition of my vehicle as:' | translate }}</p>
                    <app-range-slider v-if="activeSliderCondition" :use-id="true" :active-on-slide="true"
                                      :options="carConditions" :display-steps="true" :active="activeSliderCondition"
                                      :is-disabled="disableSelects" custom-event="PartExchange::changeCondition"
                                      custom-event-change="PartExchange::changeCondition"
                                      v-ref:condition-slider></app-range-slider>

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
                        <input type="checkbox" :id="'popup-info-checkbox'+info.id" v-model="info.checked"
                               :disabled="disableSelects">
                        <label :for="'popup-info-checkbox'+info.id"><span></span></label>

                        <div class="info-label-wrap">
                            <label :for="'popup-info-checkbox'+info.id" class="px-light-text">{{ info.title }}</label>
                            <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                                <span class="action-badge info-small" slot="tooltipElement"></span>

                                <div slot="tooltipContent">
                                    <p>{{ info.hint }}</p>
                                </div>

                            </app-tooltip>
                        </div>

                        <p :class="{ 'warning' : !info.isRequired }" v-if="info.error.length > 0 && !info.checked">{{
                            info.error }}</p>
                    </div>

                </div>

                <div class="px-submit-block">
                    <div class="px-expired-msg" v-if="!valuationResult && isExpired">
                        {{ 'The value of your vehicle has changed. Update your valuation' | translate }}
                    </div>

                    <button :class="[checkData() ? 'button-default' : 'button-disabled']" :disabled="!checkData()"
                            @click="getValuation()" v-if="!valuationResult">{{ isExpired ? 'Update Valuation' : 'Get estimate' | translate }}
                    </button>
                    <button class="button-empty" @click="continueWithout()" v-if="!valuationResult && canEdit">{{
                        'Continue without Trade in' | translate }}
                    </button>
                </div>

                <app-configurator-part-exchange-valuation
                        v-ref:part-exchange-valuation-result
                        :product-id="productId"
                        :lead-time="leadTime">
                </app-configurator-part-exchange-valuation>
            </div>
        </div>
    </div>
</template>

<script>
    import corePartExchange from 'core/components/Configurator/PartExchange';
    import PartExchange from 'mini/components/Shared/PartExchange';
    import appPartExchangeVrm from 'mini/components/Configurator/PartExchangeVRM';
    import appConfiguratorPartExchangeValuation from 'mini/components/Configurator/PartExchangeValuation';
    import numeral from 'numeral';

    export default corePartExchange.extend({
        mixins: [PartExchange],

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
                this.PXVrm.carInfo.registrationYear = valuation.plate_year;
                this.PXVrm.carInfo.vrmInput = valuation.vrm;
                this.PXVrm.carDetails.result = true;
                this.PXVrm.carDetails.vrmDisabled = true;

                this.$store.commit('setPXVrmCarInfoCapId', parseInt(valuation.px_id));
                this.$store.commit('setPXVrmCarInfoModel', valuation.cap.model);
                this.$store.commit('setPXVrmCarInfoTitle', valuation.cap_extended.product_name);
                this.$store.commit('setPXVrmCarInfoVrm', valuation.vrm);
                this.$store.commit('setPXVrmCarInfoVrmInput', valuation.vrm);
                this.$store.commit('setPXVrmCarDetailsResult', true);
                this.$store.commit('setPXVrmCarDetailsVrmDisabled', true);

                this.PXValuation.valuationResult = true;
                this.PXValuation.partExchangeValue = parseFloat(valuation.part_exchange_value);
                this.PXValuation.leadTimePartExchangeValue = parseFloat(valuation.updated_part_exchange_value);
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
            }
        },

        ready() {
            const pxVrmInput = this.deepLinkParams.pxVrmInput;
            const pxYear = parseInt(this.deepLinkParams.pxRegistrationYear);
            const pxMmCode = this.deepLinkParams.pxMmCode || '';
            const pxMileage = parseInt(this.deepLinkParams.pxMileage);
            const pxConditions = parseInt(this.deepLinkParams.pxCondition);
            const pxSettlement = parseFloat(this.deepLinkParams.pxSettlement);
            let pxCheckedInputs = this.deepLinkParams.pxAdditionalInfo || '';

            if (pxVrmInput || pxYear || pxMmCode || pxMileage) {
                this.PXVrm.softReset();
                this.disableSelects = false;
                this.valuationResult = false;
            }

            this.mileage = pxMileage || this.mileage;
            this.activeCondition = pxConditions || this.activeCondition;
            this.PXValuation.outstandingFinance = pxSettlement || this.PXValuation.outstandingFinance;

            if (pxCheckedInputs) {
                pxCheckedInputs = pxCheckedInputs.split(',');
                this.additionalInfo.forEach((item) => {
                    item.checked = pxCheckedInputs.indexOf(item.id.toString()) >= 0;
                });
            }

            this.$dispatch('PartExchangeVRM::resetSlider');

            this.PXVrm.carInfo.vrmInput = pxVrmInput || this.PXVrm.carInfo.vrmInput;
            this.PXVrm.carInfo.registrationYear = pxYear || this.PXVrm.carInfo.registrationYear;
            this.PXVrm.carInfo.capId = pxMmCode.replace(/\D/g, '') || this.PXVrm.carInfo.capId;

            if (this.PXVrm.carInfo.capId) {
                this.softResetSuccessQueue.push(() => this.PXVrm.getCarDetails());
            }
        },

        components: {
            corePartExchange,
            appPartExchangeVrm,
            appConfiguratorPartExchangeValuation
        }
    });
</script>

