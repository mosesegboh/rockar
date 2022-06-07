<template>
    <div class="part-exchange-custom">

        <div class="row">

            <div class="col-6 px-selects">
                <p class="px-regular-heading">{{ 'Your vehicle details:' | translate }}</p>

                <app-select :title="'Vehicle Type' | translate" :options="customCarData.vehicleType" custom-event="PartExchangeCustomCar::customVehicleTypeSelected"></app-select>

                <app-select :title="'Select Registration Year' | translate" :options="customCarData.registrationYear" :disabled="!customCar.vehicleType" custom-event="PartExchangeCustomCar::customRegistrationYearSelected"></app-select>

                <app-select :title="'Make' | translate" :options="customCarData.make" :disabled="!customCar.registrationYear" custom-event="PartExchangeCustomCar::customMakeSelected"></app-select>

                <app-select :title="'Model' | translate" :options="customCarData.model" :disabled="!customCar.make" custom-event="PartExchangeCustomCar::customModelSelected"></app-select>

                <app-select :title="'Fuel Type' | translate" :options="customCarData.fuelType" :disabled="!customCar.model" custom-event="PartExchangeCustomCar::customFuelTypeSelected"></app-select>

                <app-select :title="'Transmission' | translate" :options="customCarData.transmission" :disabled="!customCar.fuelType" custom-event="PartExchangeCustomCar::customTransmissionSelected"></app-select>

                <app-select :title="'Variant' | translate" :options="customCarData.variant" :disabled="!customCar.transmission" custom-event="PartExchangeCustomCar::customVariantSelected"></app-select>

            </div>
            <div class="col-6">
                <div class="px-regular-heading text-left">{{ 'No vehicle to trade in?' | translate }}</div>
                <button class="button skip-button" @click="skipStep">{{ 'Skip this step' | translate }}</button>
            </div>
        </div>

        <div class="row px-custom-actions">
            <div class="two-in-row row-elements">
                <div class="row-element">
                    <button class="button-empty full-width keyboard-skip" @click="closeCustom">{{ 'Back' | translate }}</button>
                </div>

                <div class="row-element">
                    <button :class="[customCarValid ? 'button-default' : 'button-disabled', 'full-width']" @click="customCarSelect()" :disabled="!customCarValid">{{ 'Continue' | translate }}</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import moment from 'moment';
    import appSelect from 'core/components/Elements/Select';

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

        computed: {
            customCarValid() {
                var isValid = false;

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

            PX() {
                return this.$parent;
            }
        },

        methods: {
            handleInit(url, data = null, successCallback, failCallback = false) {
                this.PX.ajaxLoading = true;

                failCallback = failCallback || this.failCallback;

                this.$http({
                    url,
                    data
                }).then(successCallback, failCallback);
            },

            failCallback(error) {
                error = error.data;
                this.PX.ajaxLoading = false;
            },

            vehicleTypeInitSuccess(response) {
                this.customCarData.vehicleType = this.formatArray(response.data, 1, 'id', 'desc');
                this.PX.ajaxLoading = false;
            },

            registrationYearInitSuccess(response) {
                this.customCarData.registrationYear = this.formatArray(response.data, 1, 'id', 'desc');
                this.PX.ajaxLoading = false;
            },

            makeInitSuccess(response) {
                this.customCarData.make = this.formatArray(response.data, 1, 'id', 'desc');
                this.PX.ajaxLoading = false;
            },

            modelInitSuccess(response) {
                this.customCarData.model = this.formatArray(response.data, 1, 'id', 'desc');
                this.PX.ajaxLoading = false;
            },

            fuelTypeInitSuccess(response) {
                this.customCarData.fuelType = this.formatArray(response.data, 1, 'id', 'desc');
                this.PX.ajaxLoading = false;
            },

            transmissionInitSuccess(response) {
                this.customCarData.transmission = this.formatArray(response.data, 1, 'id', 'desc');
                this.PX.ajaxLoading = false;
            },

            variantInitSuccess(response) {
                this.customCarData.variant = this.formatArray(response.data, 1, 'id', 'desc');
                this.PX.ajaxLoading = false;
            },

            customCarSelect() {
                this.PX.ajaxLoading = true;
                this.$http({
                    url: this.PX.customCarUrl,
                    data: {
                        vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm,
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
                this.PX.ajaxLoading = false;
                var PXVRM = this.PX.$refs.partExchangeVrm;

                PXVRM.capId = customCar.cap.capid;
                PXVRM.carInfo.title = customCar.cap_extended.product_name;
                PXVRM.carInfo.model = customCar.cap.model;
                PXVRM.carInfo.registrationYear = customCar.cap.plate_year;
                PXVRM.carNotFound = false;
                PXVRM.plateNumberError = false;
                PXVRM.carDetails.result = true;
                PXVRM.carDetails.vrmDisabled = true;
                PXVRM.$parent.disableSelects = false;
                PXVRM.carAlternatives = [];

                const expectedMileage = parseInt(customCar.derivative.expected_mileage);

                if (expectedMileage) {
                    this.PX.mileage = expectedMileage;
                }
                this.PX.openCustom = false;
                this.PX.valuationResult = false;
                this.$root.$refs.carFinder.customCar = true;
                this.updatePxVuexData(customCar);
                // If manual car entry was opened from valuation step, then need to re-evaluate
                if (this.PX.stepTwoSubStep === 2) {
                    this.PX.PXValuation.getValuation();
                }
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
                this.PX.ajaxLoading = false;
            },

            closeCustom() {
                this.PX.openCustom = false;
                if (this.PX.valuationResult) {
                    this.PX.$refs.partExchangeValuationResult.valuationResult = true;
                }
            },

            closePartExchange() {
                this.PX.closePartExchange();
            },

            skipStep() {
                this.mileage = 0;

                EventsBus.$emit('CarFinder::nextStep', false, true, false); // force update step, but don't go next
                this.$parent.updateCurrentStep().then(() => { // this updates step data and after clear PX
                    this.PXValuation.continueWithout();
                });
            },

            formatArray(array, type, keyOne, keyTwo) {
                var newArray = [];

                switch (type) {
                    case 1:
                        array.map((obj) => {
                            var newObj = { id: obj[keyOne], title: obj[keyTwo] };
                            newArray.push(newObj);
                        });
                        break;

                    case 2:
                        array.map((val) => {
                            var newObj = { id: val, title: val };
                            newArray.push(newObj);
                        });
                        break;

                    // no default
                }

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
                    vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm || null
                };

                this.handleInit(this.PX.vehicleTypeUrl, data, this.vehicleTypeInitSuccess);
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

                var data = {
                    vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType
                };

                this.handleInit(this.PX.yearUrl, data, this.registrationYearInitSuccess);
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

                var data = {
                    vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType,
                    registrationYear: this.customCar.registrationYear
                };

                this.handleInit(this.PX.makeUrl, data, this.makeInitSuccess);
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

                var data = {
                    vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType,
                    registrationYear: this.customCar.registrationYear,
                    make: this.customCar.make
                };

                this.handleInit(this.PX.modelUrl, data, this.modelInitSuccess);
            },

            'PartExchangeCustomCar::customModelSelected'(modelObj) {
                this.customCar.model = modelObj.id;

                this.customCar.fuelType = null;
                this.customCarData.fuelType = [];

                this.customCar.transmission = null;
                this.customCarData.transmission = [];

                this.customCar.variant = null;
                this.customCarData.variant = [];

                var data = {
                    vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType,
                    registrationYear: this.customCar.registrationYear,
                    make: this.customCar.make,
                    model: this.customCar.model
                };

                this.handleInit(this.PX.fuelTypeUrl, data, this.fuelTypeInitSuccess);
            },

            'PartExchangeCustomCar::customFuelTypeSelected'(fuelTypeObj) {
                this.customCar.fuelType = fuelTypeObj.id;

                this.customCar.transmission = null;
                this.customCarData.transmission = [];

                this.customCar.variant = null;
                this.customCarData.variant = [];

                var data = {
                    vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType,
                    registrationYear: this.customCar.registrationYear,
                    make: this.customCar.make,
                    model: this.customCar.model,
                    fuelType: this.customCar.fuelType
                };

                this.handleInit(this.PX.transmissionUrl, data, this.transmissionInitSuccess);
            },

            'PartExchangeCustomCar::customTransmissionSelected'(transmissionObj) {
                this.customCar.transmission = transmissionObj.id;

                this.customCar.variant = null;
                this.customCarData.variant = [];

                var data = {
                    vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm,
                    vehicleType: this.customCar.vehicleType,
                    registrationYear: this.customCar.registrationYear,
                    make: this.customCar.make,
                    model: this.customCar.model,
                    fuelType: this.customCar.fuelType,
                    transmission: this.customCar.transmission
                };

                this.handleInit(this.PX.variantUrl, data, this.variantInitSuccess);
            },

            'PartExchangeCustomCar::customVariantSelected'(variantObj) {
                this.customCar.variant = variantObj.id;
            },

            'PartExchangeCustomCar::closeCustom'() {
                this.closeCustom();
            }
        },

        components: {
            appSelect
        }
    });
</script>
