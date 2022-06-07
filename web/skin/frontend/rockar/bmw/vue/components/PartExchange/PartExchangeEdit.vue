<template>
    <div class="row" id="my-current-car-px">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <div class="part-exchange-regular row">
            <div class="part-exchange-left col-3 col-md-6">
                <div class="px-vrm-block">
                    <div class="px-car-info">
                        <p>
                            {{ 'Ok, so your current car\'s a' | translate }}:<br>
                            <p class="px-car-name">{{ title }}</p>
                        </p>
                    </div>

                    <div class="px-vrm-block-input">
                        <input type="text" v-model="vrm" :disabled="valuationResult">
                    </div>
                </div>

                <div class="px-mileage-block">
                    <p class="px-regular-heading">{{ 'My car\'s mileage is' | translate }}</p>
                    <input type="text" placeholder="0" class="px-input keyboard-numbers" v-model="mileage | numberFormat" :disabled="valuationResult">
                </div>
            </div>

            <div class="button-wrap">
                <button class="button-default"
                        @click="$refs.partExchangeVrm.getCarDetails"
                        v-if="isButtonGoVisible"
                >
                    {{ 'Next' | translate }}
                </button>
                <button class="button-default button cancel"
                        @click="$refs.partExchangeVrm.softReset"
                        v-if="isButtonCancelVisible"
                        :disabled="isButtonCancelDisabled"
                >
                    {{ 'Cancel' | translate }}
                </button>
            </div>

            <div class="part-exchange-middle col-4 col-md-6">
                <div class="px-condition-block">
                    <p class="px-light-text">{{ 'I\'d rate my car as:' | translate }}</p>

                    <app-range-slider
                        v-if="carConditions.length"
                        :use-id="true" :active-on-slide="true"
                        :options="carConditions"
                        :display-steps="true"
                        :active="activeCondition"
                        :is-disabled="valuationResult"
                        custom-event="changeCarCondition"
                        custom-event-change="changeCarCondition"
                        v-ref:condition-slider>
                    </app-range-slider>

                    <div class="px-condition-list" v-for="condition in carConditions" v-if="activeCondition == condition.id">
                        <p class="px-condition-title" >{{ condition.title }}</p>
                        <p class="px-light-text">{{{ condition.body }}}</p>
                    </div>
                </div>
            </div>

            <div class="part-exchange-right col-5 col-md-12">
                <div class="px-additional-info-block">
                    <div class="px-additional-info-checkbox" v-for="info in additionalInfo">
                        <input type="checkbox" :id="'info-checkbox'+info.id" v-model="info.checked" :disabled="valuationResult">
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

                        <p v-if="(info.error && info.error.length > 0) && !info.checked">{{ info.error }}</p>
                    </div>
                </div>

                <div class="px-submit-block">
                    <button :class="[valuationResult ? 'button-disabled' : 'button-default']" :disabled="valuationResult" @click="getValuation()">{{ 'Get Valuation' | translate }}</button>
                </div>
            </div>
        </div>

        <!-- Valuation -->
        <div class="part-exchange-valuation row" v-show="valuationResult">
            <div class="col-3 col-md-4 px-car-worth">
                <p class="px-bold-heading">{{ 'Your car is worth today' | translate }}</p>
                <p class="h1">{{ pxDisplayValue | numberFormat '0,0' true }}</p>
            </div>

            <div class="col-4 px-outstanding-finance">

                <div class="row">
                    <div class="col-12">
                        <p class="px-bold-heading">{{ 'Outstanding finance on the car?' | translate }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <p class="px-how-much">{{ 'Left to pay?' | translate }}</p>
                    </div>

                    <div class="col-6">
                        <input type="text" v-model="outstandingFinance | numberOutstandingFinanceFormat" class="px-input keyboard-numbers">
                    </div>
                </div>

                <div class="row">
                    <p class="px-light-text">
                        {{ 'We value your car based on what you\'ve told us and what it\'s worth today.  This value can change if the details you have told us changes or you choose to buy a car later.' | translate }}
                    </p>

                    <!-- Disabled till future clarifications
                    <p class="px-light-text" v-if="outstandingFinance < 1000">
                        {{ 'We value your car based on what you\'ve told us and what it\'s worth today.  This value can change if the details you have told us changes or you choose to buy a car later.' | translate }}
                    </p>

                    <p class="px-light-text" v-if="outstandingFinance >= 1000">
                        {{ 'Sorry, but Rockar are unable to take your current car. We can still help you buy a new one though.' | translate }}
                    </p>
                    -->

                </div>

            </div>

            <div class="col-5 col-md-4 part-exchange-right">
                <div class="px-submit-block">
                    <button class="button-default" @click="save" :disabled="!valuationResult && isValid">{{ 'Save' | translate }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import NumbericInputMixin from 'core/mixins/NumericInput';
    import appRangeSlider from 'core/components/Elements/RangeSlider';
    import appTooltip from 'core/components/Elements/Tooltip';

    export default Vue.extend({
        mixins: [NumbericInputMixin],

        props: {
            pxId: {
                required: false,
                type: Number,
                default: null
            },
            vrm: {
                required: true,
                type: String
            },
            title: {
                required: true,
                type: String
            },
            mileage: {
                required: true,
                type: Number
            },
            outstandingFinance: {
                required: true,
                type: Number
            },
            activeCondition: {
                required: true,
                type: Number
            },
            pxValue: {
                required: true,
                type: Number
            },
            origAdditionalInfo: {
                required: false
            },
            origCarConditions: {
                required: false
            },
            origSaveValuationUrl: {
                required: false,
                type: String
            },
            origValuationUrl: {
                required: false,
                type: String
            }
        },

        data() {
            return {
                valuationResult: false,
                isValid: true,
                ajaxLoading: false,
                pxError: false,
                pxDisplayValue: this.pxValue - this.outstandingFinance
            }
        },

        computed: {
            additionalInfo() {
                if (this.origAdditionalInfo) {
                    return this.origAdditionalInfo;
                }

                return this.$parent.$parent.additionalInfo;
            },
            carConditions() {
                if (this.origCarConditions) {
                    return this.origCarConditions;
                }

                return this.$parent.$parent.carConditions;
            },
            saveValuationUrl() {
                if (this.origSaveValuationUrl) {
                    return this.origSaveValuationUrl;
                }

                return this.$parent.$parent.saveValuationUrl;
            },
            valuationUrl() {
                if (this.origValuationUrl) {
                    return this.origValuationUrl;
                }

                return this.$parent.$parent.valuationUrl;
            },
            additionalInfoListId() {
                const list = [];

                this.additionalInfo.forEach((ai) => {
                    list.push(ai.id);
                });
                return list;
            }
        },

        methods: {
            getAdditionalInfo() {
                const newInfo = [];

                this.additionalInfo.forEach((carInfo) => {
                    const data = {
                        id: carInfo.id,
                        checked: carInfo.checked
                    };
                    newInfo.push(data);
                });

                return newInfo;
            },

            getValuation() {
                if (!this.valuationResult) {
                    this.ajaxLoading = true;

                    this.$http({
                        url: this.valuationUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data: {
                            mileage: this.mileage,
                            carCondition: this.activeCondition,
                            additionalInfo: this.getAdditionalInfo()
                        }
                    }).then(this.carValuationSuccess, this.carValuationFail);
                }
            },

            carValuationSuccess(resp) {
                this.pxError = false;
                this.pxValue = resp.data.totals.total;
                this.pxDisplayValue = this.pxValue - this.outstandingFinance;

                if (resp.data.totals.total < 1) {
                    this.pxValue = 0;
                    this.pxError = true;
                    this.ajaxLoading = false;
                } else {
                    this.pxError = false;
                    this.ajaxLoading = false;
                    this.valuationResult = true;
                }
            },

            carValuationFail(error) {
                this.ajaxLoading = false;
                this.$store.commit('setNotificationMessage', { message: this.translateString('My Current Cars PX:') + error, type: 'error', timeout: 5000 });
            },

            save() {
                if (this.valuationResult) {
                    this.ajaxLoading = true;

                    this.$http({
                        url: this.saveValuationUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data: {
                            vrm: this.vrm,
                            cap_id: this.capId,
                            car_mileage: this.mileage,
                            car_condition: this.activeCondition,
                            checkboxes: this.additionalInfoListId,
                            outstanding_finance: this.outstandingFinance,
                            part_exchange_value: this.pxValue,
                            browser: window.navigator.userAgent,
                            px_id: this.pxId
                        }
                    }).then(this.saveSuccess, this.saveFail);
                }
            },

            saveSuccess(resp) {
                this.pxError = false;
                this.pxValue = resp.data.total;
                this.pxDisplayValue = this.pxValue - this.outstandingFinance;

                if (resp.data.total < 1) {
                    this.pxValue = 0;
                    this.pxError = true;
                    this.ajaxLoading = false;
                } else {
                    this.ajaxLoading = false;
                }
            },

            saveFail(error) {
                this.ajaxLoading = false;
                this.$store.commit('setNotificationMessage', { message: this.translateString('My Current Cars PX:') + error, type: 'error', timeout: 5000 });
            }
        },

        events: {
            changeCarCondition(id) {
                this.activeCondition = id;
            }
        },

        watch: {
            'valuationResult'(result) {
                this.$refs.conditionSlider.disableSlider(!result);
            },

            'outstandingFinance'(value) {
                if (value >= this.pxValue || value < 0) {
                    this.isValid = false;
                }
            }
        },

        components: {
            appRangeSlider,
            appTooltip
        }
    });
</script>
