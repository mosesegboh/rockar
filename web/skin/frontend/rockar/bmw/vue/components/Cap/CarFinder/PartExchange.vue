<template>
    <div v-show="$parent.steps[$parent.currentStep] === 'partExchange'" class="part-exchange">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <app-part-exchange-custom-car v-show="openCustom" v-ref:part-exchange-custom-car></app-part-exchange-custom-car>

        <div class="substep-1" v-show="stepTwoSubStep === 0 && !openCustom">
            <div class="row">
                <div class="part-exchange-wrapper col-12">
                    <div class="part-exchange-regular part-exchange-left" v-show="!openCustom">
                        <div class="part-exchange-vrm-wrapper">
                            <app-part-exchange-vrm
                                    car-alternative-details="url"
                                    v-ref:part-exchange-vrm>
                            </app-part-exchange-vrm>
                        </div>
                        <div class="px-mileage-block">
                            <div class="px-mileage-block-wrapper">
                                <p class="px-regular-heading">{{ 'What\'s your vehicle\'s current mileage?' | translate }}</p>
                                <div class="px-input-wrapper">
                                    <input type="text"
                                           placeholder="0"
                                           @focus="selectMileage"
                                           @blur="deselectMileage"
                                           @keyup.enter="PXVrm.goNextStep()"
                                           :class="[checkData() ? 'active' : '', 'active', 'px-input', 'keyboard-numbers']"
                                           v-model="mileage | numberKMFormat">
                                </div>
                            </div>
                        </div>
                        <button class="nuc-hidden-button" @click="PXVrm.goNextStep()"></button>
                    </div>
                    <div class="part-exchange-or">
                        <div class="hr"></div>
                        <span class="or">{{ 'OR' | translate }}</span>
                    </div>
                    <div class="part-exchange-right">
                        <div class="part-exchange-skip-wrapper">
                            <div class="px-regular-heading text-left">{{ 'No vehicle to trade in?' | translate }}</div>
                            <button class="button skip-button" @click="skipStep">{{ 'Skip this step' | translate }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <app-modal :show.sync="vrmConfirmationPopup" class-name="vrm-confirmation-popup">
                <div slot="content">
                    <p class="h-common">{{ 'Is this your vehicle?' | translate }}</p>
                    <p class="values">
                        <span class="main">{{ PXVrm.carInfo.vrm }}, {{ PXVrm.PX.mileage | numberFormat }} {{ 'KM' | translate}}</span><br>
                        {{ PXVrm.carInfo.title }}
                    </p>
                    <div class="row align-right">
                        <button class="button button-narrow button-grey popup-no" @click="openNotMyCar">{{ 'No' | translate }}</button>
                        <button class="button button-narrow popup-yes" @click="acceptVRM()">{{ 'Yes' | translate }}</button>
                    </div>
                </div>
            </app-modal>
        </div>

        <div v-show="stepTwoSubStep === 1 && !openCustom" class="substep-2-main">
            <div class="row current-car">
                <div class="col-12">
                    <span class="values">
                        <span class="main">{{ PXVrm.carInfo.vrm }}, {{ PXVrm.PX.mileage | numberFormat }} {{ 'KM' | translate}}</span><br>
                        <span class="main-description">{{ PXVrm.carInfo.title }}</span>
                    </span>
                </div>
            </div>

            <div class="mobile-divider">
                <hr/>
            </div>

            <div class="part-exchange-regular substep-2" v-show="!openCustom">

                <div class="row substep-2-header">
                    <div class="col-12">
                        <p class="px-small-heading">{{ 'I\'d rate the condition of my vehicle as:' | translate }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-7 select-condition">

                        <div class="part-exchange-conditions">
                            <app-range-slider
                                    v-if="activeSliderCondition"
                                    :use-id="true"
                                    :active-on-slide="true"
                                    :options="carConditions"
                                    :display-steps="true"
                                    :active="activeSliderCondition"
                                    :is-disabled="disableSelects"
                                    custom-event="PartExchange::changeCondition"
                                    custom-event-change="PartExchange::changeCondition"
                                    v-ref:condition-slider
                            ></app-range-slider>

                            <ul class="conditions-labels" :class="['conditions-' + this.carConditions.length]">
                                <li v-for="condition in carConditions">
                                    <span>{{ condition.title | capitalize }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="part-exchange-conditions-mobile">
                            <app-select
                                    @select="dropdownSelectActiveCondition"
                                    :options="pxConditionsMobile"
                                    :init-selected="activeConditionSelectIndex"
                                    class="grey-dropdown"
                            ></app-select>
                        </div>

                        <div class="condition-info mobile-sm-hide">
                            <template v-for="condition in carConditions">
                                <div class="item" v-show="activeCondition == condition.id">
                                    <p class="title" slot="tooltipElement">{{ condition.title | capitalize }}</p>
                                    <p class="description" v-html="condition.body"></p>
                                    <app-more-info :disable="false" class="condition-more-info">
                                        <p slot="content">{{{ htmlEntityDecode(condition.hint) }}}</p>
                                    </app-more-info>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="col-1">
                        <div class="divider"></div>
                    </div>

                    <div class="col-4">
                        <div class="px-additional-info-block">
                            <div class="px-additional-info-checkbox" v-for="info in additionalInfo">
                                <div class="px-additional-info-list">
                                    <input type="checkbox" :id="'info-checkbox'+info.id" v-model="info.checked" @change="$emit('PartExchange::additionalInfoChange')">
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
                    </div>
                </div>
            </div>
        </div>

        <div v-show="stepTwoSubStep === 2 && !openCustom" class="substep-3">
            <div class="row current-car">
                <div class="col-12 text-center">
                    <span class="values">
                        <span class="main">{{ activeConditionTitle }}, {{ PXVrm.PX.mileage | numberFormat }} {{ 'KM' | translate}}</span><br>
                        {{ PXVrm.carInfo.title }}
                    </span>
                </div>
            </div>

            <div class="mobile-divider">
                <hr/>
            </div>

            <app-part-exchange-valuation v-ref:part-exchange-valuation-result></app-part-exchange-valuation>
        </div>
    </div>
</template>

<script>
    import PartExchange from 'bmw/components/CarFinder/PartExchange';
    import appPartExchangeVrm from 'bmw/components/PartExchange/PartExchangeVRM';
    import appPartExchangeValuation from 'bmw/components/CarFinder/PartExchangeValuation';
    import appMoreInfo from 'core/components/Elements/MoreInfo';
    import appSelect from 'bmw/components/Elements/Select';
    import appPartExchangeCustomCar from 'bmw/components/PartExchange/PartExchangeCustomCar';
    import appTooltip from 'core/components/Elements/Tooltip';
    import appModal from 'core/components/Elements/Modal';
    import translateString from 'core/filters/Translate';
    import numeral from 'numeral';

    export default Vue.extend({
        mixins: [PartExchange],

        data() {
            return {
                partExchange: false,
                stepTwoSubStep: 0,
                outstandingFinance: 0,
                vrmConfirmationPopup: false,
                hasMileageFocus: false
            };
        },

        props: {
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
            numberKMFormat: {
                read(number) {
                    number = Math.abs(Math.floor(number));
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
                    return Math.abs(Math.floor(numeral(number).value()));
                }
            }
        },

        computed: {
            activeConditionTitle() {
                var result = 0;
                this.carConditions.forEach((condition) => {
                    if (condition.id === this.activeCondition) {
                        result = condition.title;
                    }
                });
                return result;
            },

            /**
             * PX conditions formatted for mobile select
             *
             * @returns {Array}
             */
            pxConditionsMobile() {
                const conditionsSelect = [];
                this.carConditions.forEach(condition => {
                    conditionsSelect.push({
                        title: condition.title,
                        value: condition.id
                    });
                });

                return conditionsSelect;
            },

            conditionSlider() {
                return this.$refs.conditionSlider;
            },

            PxVuex() {
                return this.$store.state.general.PX;
            },

            partExchangeCustomCar() {
                return this.$refs.partExchangeCustomCar;
            }
        },

        events: {
            'PartExchange::changeCarCondition'(newValue) {
                this.activeCondition = newValue;
            }
        },

        methods: {
            translateString,

            skipStep() {
                if (this.mileage === 0 || !this.PXVrm.carInfo.vrmInput) {
                    this.softReset();
                }

                if (!this.PXVrm.carNotFound) {
                    this.PxVuex.mileage = this.mileage;
                    this.PxVuex.Vrm.carInfo.vrmInput = this.PXVrm.carInfo.vrmInput;

                    if (this.PXValuation.valuationCompleted
                        || (this.stepTwoSubStep === 2 && this.PxVuex.Vrm.carDetails.result)
                        || this.partExchangeCustomCar.customCarValid
                    ) {
                        if (this.stepTwoSubStep === 0) {
                            this.PxVuex.Vrm.carDetails.result = false;
                        }

                        this.PxVuex.Valuation.partExchangeValue = 0;
                        this.softReset();
                        this.resetPartExchange();
                    }
                } else {
                    this.mileage = 0;
                    this.PXVrm.carInfo.vrmInput = '';
                }

                EventsBus.$emit('CarFinder::nextStep', false, true, false); // force update step, but don't go next
                this.$parent.updateCurrentStep().then(() => { // this updates step data and after clear PX
                    this.PXValuation.continueWithout();
                });
            },

            closePartExchange() {
                this.$parent.show = false;
                this.$root.$refs.carFilter.$parent.show = true;
            },

            hardResetPartExchange(callback) {
                this.valuationResult = false;
                this.mileage = 0;
                this.step = 0;

                this.PXVrm.carInfo.capId = null;
                this.PXVrm.carInfo.model = null;
                this.PXVrm.carInfo.title = null;
                this.PXVrm.carInfo.vrm = null;
                this.PXVrm.carInfo.vrmInput = '';
                this.PXVrm.carDetails.result = false;
                this.PXVrm.carDetails.vrmDisabled = false;
                this.PXVrm.carNotFound = false;
                this.PXVrm.carAlternatives = [];
                this.PXVrm.step = 0;

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

                this.$parent.title = this.translateString('Swap Your Car For A New Car?');
                this.disableSelects = true;
            },

            htmlEntityDecode(encodedHtml) {
                return jQuery('<textarea />').html(encodedHtml).text();
            },

            enableMoreInfo() {
                this.$broadcast('MoreInfo::enable');
            },

            acceptVRM() {
                this.vrmConfirmationPopup = false;
                this.stepTwoSubStep = 1;
                this.PXVrm.carDetails.result = true;
                this.enableMoreInfo();
                this.$parent.updateCurrentStep();
            },

            openManualCarEntry() {
                this.openCustom = true;
                this.$refs.partExchangeValuationResult.valuationResult = false;
            },

            openNotMyCar() {
                this.vrmConfirmationPopup = false;
                this.PXVrm.openCustom();
            },

            selectMileage() {
                this.hasMileageFocus = true;
            },

            deselectMileage() {
                this.hasMileageFocus = false;
            },

            /**
             * Proxy method to select active condition and update slider accordingly
             *
             * @param data
             */
            dropdownSelectActiveCondition(data) {
                this.selectActiveCondition({
                    id: data.value
                });
                this.conditionSlider.changeActive(data.value);
            }
        },

        ready() {
            this.changeButtonTitle();
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
        },

        components: {
            appPartExchangeVrm,
            appPartExchangeValuation,
            appPartExchangeCustomCar,
            appSelect,
            appTooltip,
            appMoreInfo,
            appModal
        }
    });
</script>
