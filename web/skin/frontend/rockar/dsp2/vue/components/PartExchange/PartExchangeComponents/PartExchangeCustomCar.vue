<template>
    <div class="substep-2">
        <div class="row">
            <div class="col-12">
                <div class="px-popup-info-block">
                    <h3 class="h3 px-step-title">{{ 'Vehicle Details' | translate }}</h3>

                    <app-global-warning
                        type="error"
                        message="We are unable to find your vehicle. Please complete a manual trade-in"
                        :show="showError"
                    >
                    </app-global-warning>

                    <p class="px-step-message" v-if="!showError">{{ 'Please fill in your trade-in vehicle details' | translate }}</p>
                </div>

                <div class="col-6">
                    <app-select
                        :options="customCarData.vehicleType"
                        title="&nbsp;"
                        :label="'Vehicle Type*'"
                        custom-event="PartExchangeCustomCar::customVehicleTypeSelected"
                        :disabled="isSetVehicleOptions"
                    ></app-select>

                    <app-select
                        :options="registrationYearList"
                        title="&nbsp;"
                        :label="'Registration Year*'"
                        custom-event="PartExchangeCustomCar::customRegistrationYearSelected"
                        :disabled="!customCar.vehicleType"
                    ></app-select>

                    <app-select
                        :options="customCarData.make"
                        title="&nbsp;"
                        :label="'Make*'"
                        custom-event="PartExchangeCustomCar::customMakeSelected"
                        :disabled="!customCar.registrationYear"
                    ></app-select>

                    <app-select
                        :options="customCarData.model"
                        title="&nbsp;"
                        :label="'Model*'"
                        custom-event="PartExchangeCustomCar::customModelSelected"
                        :disabled="!customCar.make"
                    ></app-select>
                </div>

                <div class="col-6 px-custom-col-two">
                    <app-select
                        :options="customCarData.fuelType"
                        title="&nbsp;"
                        :label="'Fuel Type*'"
                        custom-event="PartExchangeCustomCar::customFuelTypeSelected"
                        :disabled="!customCar.model"
                    ></app-select>

                    <app-select
                        :options="customCarData.transmission"
                        title="&nbsp;"
                        :label="'Transmission*'"
                        custom-event="PartExchangeCustomCar::customTransmissionSelected"
                        :disabled="!customCar.fuelType"
                    ></app-select>

                    <app-select
                        :options="customCarData.variant"
                        title="&nbsp;"
                        :label="'Variant*'"
                        custom-event="PartExchangeCustomCar::customVariantSelected"
                        :disabled="!customCar.transmission"
                    ></app-select>
                </div>

            </div>
            <div class="col-12">
                <hr>
            </div>
            <div class="px-bottom col-6 col-md-12">
                <button class="button dsp2-money"
                    @click="customCarSelect()"
                    :class="{ disabled: !customCarValid }"
                    :disabled="!customCarValid"
                >
                    {{ 'Continue' | translate }}
                </button>
                <p class="px-required">{{ '* Indicates a required field.' | translate }}</p>
            </div>
        </div>
    </div>
</template>

<script>
    import appSelect from 'dsp2/components/Elements/Select';
    import appGlobalWarning from 'dsp2/components/Elements/GlobalWarning'

    export default Vue.extend({
        data() {
            return {
                customCar: {
                    vehicleType: null,
                    registrationYear: null,
                    make: null,
                    makeTitle: null,
                    model: null,
                    fuelType: null,
                    transmission: null,
                    variant: null
                },

                customCarData: {
                    vehicleType: [],
                    registrationYear: [],
                    make: [],
                    model: [],
                    fuelType: [],
                    transmission: [],
                    variant: []
                }
            }
        },

        props: {
            showError: {
                required: true,
                type: Boolean
            },

            variantUrl: {
                required: false,
                type: String,
                default: ''
            },

            partExchangeVrm: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            customCarUrl: {
                required: false,
                type: String,
                default: ''
            },

            vehicleTypeUrl: {
                required: false,
                type: String
            },

            yearUrl: {
                required: false,
                type: String,
                default: ''
            },

            makeUrl: {
                required: false,
                type: String,
                default: ''
            },

            modelUrl: {
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

            accessedFrom: {
                required: true,
                type: String
            },

            carFinder: {
                required: false,
                type: Boolean,
                default: false
            },

            carFinderComponent: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            }
        },

        computed: {
            customCarValid() {
                let isValid = false;

                if (
                    this.customCar.vehicleType
                    && this.customCar.registrationYear
                    && this.customCar.make
                    && this.customCar.model
                    && this.customCar.fuelType
                    && this.customCar.transmission
                    && this.customCar.variant
                ) {
                    isValid = true;
                }

                return isValid;
            },

            isSetVehicleOptions() {
                return this.customCarData.vehicleType.length === 0;
            },

            PX() {
                return this.$parent;
            },

            registrationYearList() {
                const currentYear = new Date().getFullYear();
                const minYear = currentYear - 20;

                return this.customCarData.registrationYear.filter(yearOption => yearOption.id >= minYear);
            }
        },

        methods: {
            handleInit(url, data = null, successCallback, failCallback = false) {
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', true);

                failCallback = failCallback || this.failCallback;

                this.$http({
                    url,
                    data
                }).then(successCallback, failCallback);
            },

            failCallback(error) {
                error = error.data;
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
            },

            vehicleTypeInitSuccess(response) {
                this.customCarData.vehicleType = this.formatArray(response.data, 'id', 'desc');
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
            },

            registrationYearInitSuccess(response) {
                this.customCarData.registrationYear = this.formatArray(response.data, 'id', 'desc');
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
            },

            makeInitSuccess(response) {
                this.customCarData.make = this.formatArray(response.data, 'id', 'desc');
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
            },

            modelInitSuccess(response) {
                this.customCarData.model = this.formatArray(response.data, 'id', 'desc');
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
            },

            fuelTypeInitSuccess(response) {
                this.customCarData.fuelType = this.formatArray(response.data, 'id', 'desc');
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
            },

            transmissionInitSuccess(response) {
                this.customCarData.transmission = this.formatArray(response.data, 'id', 'desc');
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
            },

            variantInitSuccess(response) {
                this.customCarData.variant = this.formatArray(response.data, 'id', 'desc');
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
            },

            customCarSelect() {
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', true);
                this.$http({
                    url: this.customCarUrl,
                    data: {
                        vrm: this.partExchangeVrm.carInfo.vrm,
                        vehicleType: this.customCar.vehicleType,
                        registrationYear: this.customCar.registrationYear,
                        make: this.customCar.make,
                        model: this.customCar.model,
                        fuelType: this.customCar.fuelType,
                        transmission: this.customCar.transmission,
                        variant: this.customCar.variant
                    }
                }).then(this.customCarSelectSuccess, this.customCarSelectFail);
            },

            customCarSelectSuccess(customCar) {
                customCar = customCar.data;
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
                const PXVRM = this.partExchangeVrm;

                PXVRM.capId = customCar.cap.capid;
                PXVRM.carInfo.title = customCar.cap_extended.product_name;
                PXVRM.carInfo.model = customCar.cap.model;
                PXVRM.carInfo.registrationYear = customCar.cap.plate_year;
                PXVRM.carNotFound = false;
                PXVRM.plateNumberError = false;
                PXVRM.carDetails.result = true;
                PXVRM.carDetails.vrmDisabled = true;
                PXVRM.carAlternatives = [];
                this.$dispatch('PartExchangeFilter::disableSelects');

                const expectedMileage = parseInt(customCar.derivative.expected_mileage);

                if (expectedMileage) {
                    this.PX.mileage = expectedMileage;
                }
                this.PX.openCustom = false;
                this.PX.valuationResult = false;

                if (this.carFinder) {
                    if (this.$root.$refs.carFinder) {
                        this.carFinderComponent.customCar = true;
                    }
                }

                this.updatePxVuexData(customCar);
                this.PX.currentStep = 1;
                this.PX.subStep = 0;
            },

            /**
             * Set PX data into vuex storage
             *
             * @param details
             */
            updatePxVuexData(details) {
                this.$store.commit('setPXVrmCarInfoVrm', details.vrm);
                this.$store.commit('setPXVrmCarInfoVrmInput', details.vrm);
                this.$store.commit('setPXVrmCarInfoCapId', details.cap.capid);
                this.$store.commit('setPXVrmCarInfoTitle', details.cap_extended.product_name);
                this.$store.commit('setPXVrmCarInfoModel', details.cap.model);
                this.$store.commit('setPXVrmCarInfoDerivative', details.cap.derivative);
                this.$store.commit('setPXVrmCarInfoRegistrationYear', details.cap.plate_year);
                this.$store.commit('setPXMileage', details.mileage > 0
                    ? details.mileage
                    : parseInt(details.derivative.expected_mileage));
            },

            customCarSelectFail(error) {
                error = error.data;
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
            },

            formatArray(array, keyOne, keyTwo) {
                const newArray = [];

                array.map((obj) => {
                    const newObj = { id: obj[keyOne], title: obj[keyTwo] };
                    newArray.push(newObj);
                });

                return newArray;
            }
        },

        events: {
            'PartExchangeCustomCar::customCarInit'() {
                this.customCar.vehicleType = null;
                this.customCarData.vehicleType = [];

                this.customCar.registrationYear = null;
                this.customCarData.registrationYear = [];

                this.customCar.make = null;
                this.customCar.makeTitle = null;
                this.customCarData.make = [];

                this.customCar.model = null;
                this.customCarData.model = [];

                this.customCar.fuelType = null;
                this.customCarData.fuelType = [];

                this.customCar.transmission = null;
                this.customCarData.transmission = [];

                this.customCar.variant = null;
                this.customCarData.variant = [];

                const data = {
                    vrm: this.partExchangeVrm.carInfo.vrm || null
                };

                this.handleInit(this.vehicleTypeUrl, data, this.vehicleTypeInitSuccess);
            },

            'PartExchangeCustomCar::customVehicleTypeSelected'(vehicleTypeObj) {
                this.customCar.vehicleType = vehicleTypeObj.id;

                this.customCar.registrationYear = null;
                this.customCarData.registrationYear = [];

                this.customCar.make = null;
                this.customCarData.make = [];

                this.customCar.model = null;
                this.customCarData.model = [];

                this.customCar.fuelType = null;
                this.customCarData.fuelType = [];

                this.customCar.transmission = null;
                this.customCarData.transmission = [];

                this.customCar.variant = null;
                this.customCarData.variant = [];

                const data = {
                    vrm: this.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType
                };

                this.handleInit(this.yearUrl, data, this.registrationYearInitSuccess);
            },

            'PartExchangeCustomCar::customRegistrationYearSelected'(registrationYearObj) {
                this.customCar.registrationYear = registrationYearObj.id;

                this.customCar.make = null;
                this.customCarData.make = [];

                this.customCar.model = null;
                this.customCarData.model = [];

                this.customCar.fuelType = null;
                this.customCarData.fuelType = [];

                this.customCar.transmission = null;
                this.customCarData.transmission = [];

                this.customCar.variant = null;
                this.customCarData.variant = [];

                const data = {
                    vrm: this.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType,
                    registrationYear: this.customCar.registrationYear
                };

                this.handleInit(this.makeUrl, data, this.makeInitSuccess);
            },

            'PartExchangeCustomCar::customMakeSelected'(makeObj) {
                this.customCar.make = makeObj.id;

                this.customCar.model = null;
                this.customCarData.model = [];

                this.customCar.fuelType = null;
                this.customCarData.fuelType = [];

                this.customCar.transmission = null;
                this.customCarData.transmission = [];

                this.customCar.variant = null;
                this.customCarData.variant = [];

                const data = {
                    vrm: this.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType,
                    registrationYear: this.customCar.registrationYear,
                    make: this.customCar.make
                };

                this.handleInit(this.modelUrl, data, this.modelInitSuccess);
            },

            'PartExchangeCustomCar::customModelSelected'(modelObj) {
                this.customCar.model = modelObj.id;

                this.customCar.fuelType = null;
                this.customCarData.fuelType = [];

                this.customCar.transmission = null;
                this.customCarData.transmission = [];

                this.customCar.variant = null;
                this.customCarData.variant = [];

                const data = {
                    vrm: this.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType,
                    registrationYear: this.customCar.registrationYear,
                    make: this.customCar.make,
                    model: this.customCar.model
                };

                this.handleInit(this.fuelTypeUrl, data, this.fuelTypeInitSuccess);
            },

            'PartExchangeCustomCar::customFuelTypeSelected'(fuelTypeObj) {
                this.customCar.fuelType = fuelTypeObj.id;

                this.customCar.transmission = null;
                this.customCarData.transmission = [];

                this.customCar.variant = null;
                this.customCarData.variant = [];

                const data = {
                    vrm: this.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType,
                    registrationYear: this.customCar.registrationYear,
                    make: this.customCar.make,
                    model: this.customCar.model,
                    fuelType: this.customCar.fuelType
                };

                this.handleInit(this.transmissionUrl, data, this.transmissionInitSuccess);
            },

            'PartExchangeCustomCar::customTransmissionSelected'(transmissionObj) {
                this.customCar.transmission = transmissionObj.id;

                this.customCar.variant = null;
                this.customCarData.variant = [];

                const data = {
                    vrm: this.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType,
                    registrationYear: this.customCar.registrationYear,
                    make: this.customCar.make,
                    model: this.customCar.model,
                    fuelType: this.customCar.fuelType,
                    transmission: this.customCar.transmission
                };

                this.handleInit(this.variantUrl, data, this.variantInitSuccess);
            },

            'PartExchangeCustomCar::customVariantSelected'(variantObj) {
                this.customCar.variant = variantObj.id;
            }
        },

        components: {
            appSelect,
            appGlobalWarning
        }
    });
</script>
