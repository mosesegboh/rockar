<template>
    <div>
        <div class="input-label-wrapper col-6 col-md-12" v-show="!carAlternatives.length || carDetails.result">
            <input
                type="text"
                @focus="selectVRM()"
                @blur="deselectVRM()"
                v-model="carInfo.vrmInput | vrmFilter"
                id="td-plate"
                v-el:px-vrm-input
            >
            <label class="input-label" for="td-plate">{{ 'Vehicle registration number*' | translate }}</label>
        </div>

        <div v-show="!carAlternatives.length || carDetails.result" class="px-select-wrapper col-6 col-md-12">
            <app-select
                :init-selected="carInfo.registrationYear"
                :options="availableYears"
                @select="setRegistrationYear"
                title="&nbsp;"
                :label="'Registration year*'"
                :show-items="3"
            ></app-select>
        </div>
    </div>
</template>

<script>
import appSelect from 'dsp2/components/Elements/Select';
import numeral from 'numeral';
import Constants from 'core/components/Shared/Constants';

export default Vue.extend({
    mixins: [Constants],

    data() {
        return {
            carInfo: {
                vrmInput: '',
                vrmInputYear: '',
                vrm: null,
                capId: null,
                title: null,
                model: null,
                registrationYear: ''
            },
            originalPlaceholder: '',
            PX: this.$parent,
            carNotFound: false,
            plateNumberError: false,
            carDetails: {
                result: false,
                vrmDisabled: false
            },
            carAlternatives: [],
            carSelectedAlternative: null,
            mileageError: false,
            step: -1  // Set invalid Step
        };
    },

    props: {
        registrationYear: {
            required: false,
            type: String,
            default: ''
        },

        carAlternativeDetailsUrl: {
            required: false,
            type: String,
            default: ''
        }
    },

    methods: {
        carDetailsFail(error) {
            console.error('Trade-In VRM:', error);
            EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
            this.carNotFound = true;
            this.plateNumberError = false;
            this.carInfo.vrm = this.carInfo.vrmInput;
            this.$dispatch('PartExchange::showError');
        },

        carDetailsSuccess(details) {
            details = details.data;

            if (details.alternatives.length !== undefined && details.alternatives.length) {
                this.carAlternatives = this.arrangeAlternatives(details.alternatives);
                this.carNotFound = false;
                this.plateNumberError = false;
                this.mileageError = false;

                this.$store.commit('setPXVrmCarAlternatives', this.arrangeAlternatives(details.alternatives));
                this.$store.commit('setPXVrmCarNotFound', false);
                this.$store.commit('setPXVrmCarPlateNumberError', false);
                this.$store.commit('setPXVrmCarDetailsResult', false);
            } else {
                this.carInfo.vrm = details.vrm;
                this.carInfo.vrmInput = details.vrm;
                this.carInfo.capId = details.cap.capid;
                this.carInfo.title = details.cap_extended.product_name;
                this.carInfo.model = details.cap.model;
                this.carInfo.derivative = details.cap.derivative;
                this.carInfo.registrationYear = details.plate_year;
                this.PX.mileage = details.mileage > 0
                    ? details.mileage
                    : parseInt(details.derivative.expected_mileage);
                this.step = details.step;

                this.updatePxVuexData(details);

                this.$store.commit('setPXVrmCarDetailsResult', true);
            }

            EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
        },

        detailsChanged() {
            if (jQuery('.page-loader:visible,.general-preloader:visible').length) {
                return;
            }

            this.carDetails.result = false;
            this.carAlternatives = [];
            this.plateNumberError = false;
            this.mileageError = false;
            this.carNotFound = false;
            this.carDetails.vrmDisabled = false;
        },

        getCarDetails() {
            if (this.$parent.mileage > 0) {
                this.mileageError = false;

                if (this.testCarPlateNumber()) {
                    EventsBus.$emit('PartExchangeFilter::ajaxLoading', true);
                    const promise = this.$http({
                        url: this.PX.carDetailsUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data: {
                            vrm: this.carInfo.vrmInput,
                            mileage: this.$parent.mileage,
                            registrationYear: this.carInfo.registrationYear
                        }
                    });
                    promise.then(this.carDetailsSuccess, this.carDetailsFail);

                    return promise;
                } else {
                    this.plateNumberError = true;
                    this.carNotFound = false;
                }
            } else {
                this.mileageError = true;
            }

            return false;
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
            this.$store.commit('setPXVrmCarInfoRegistrationYear', details.plate_year);
            this.$store.commit('setPXMileage', details.mileage > 0
                ? details.mileage
                : parseInt(details.derivative.expected_mileage));
        },

        setRegistrationYear(option) {
            this.carInfo.registrationYear = option.value;
        },

        resetCarData() {
            this.carInfo = {
                vrmInput: '',
                vrmInputYear: '',
                vrm: null,
                capId: null,
                title: null,
                model: null,
                registrationYear: ''
            };
            this.PX.mileage = 0;
        },

        setSavedPxData() {
            this.carInfo = {
                vrmInput: this.PX.savedPx.vrm,
                vrmInputYear: this.PX.savedPx.plate_year,
                vrm: this.PX.savedPx.vrm,
                capId: this.PX.savedPx.cap.capid,
                title: this.PX.savedPx.cap_extended.product_name,
                model: this.PX.savedPx.cap.model,
                registrationYear: this.PX.savedPx.plate_year
            };
        },

        testCarPlateNumber() {
            const rule = /^[a-zA-Z0-9 ]+$/;
            return rule.test(this.carInfo.vrmInput);
        },

        openCustom() {
            this.PX.openCustom = true;
            this.PX.$refs.partExchangeValuationResult.valuationResult = false;
        },

        setAlternativeCar(derivative) {
            EventsBus.$emit('PartExchangeFilter::ajaxLoading', true);

            this.$http({
                url: this.carAlternativeDetailsUrl,
                method: 'GET',
                data: {
                    capid: derivative.id,
                    vrm: this.carInfo.vrmInput
                }
            }).then(this.alternativeSuccess, this.alternativeFail);
        },

        alternativeSuccess(details) {
            if (typeof details.data !== 'undefined') {
                details = details.data;

                this.carInfo.vrm = details.vrm;
                this.carInfo.vrmInput = details.vrm;
                this.carInfo.capId = details.cap.capid;
                this.carInfo.title = details.cap_extended.product_name;
                this.carInfo.model = details.cap.model;

                if (this.PX.mileage === 0) {
                    this.PX.mileage = parseInt(details.derivative.expected_mileage);
                }

                this.updatePxVuexData(details);

                this.carNotFound = false;
                this.plateNumberError = false;
                this.carDetails.result = true;
                this.carDetails.vrmDisabled = true;
                this.$parent.disableSelects = false;
                this.carAlternatives = [];
            }

            EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
        },

        alternativeFail(details) {
            console.error(details);
        },

        arrangeAlternatives(alternatives) {
            const list = [];
            alternatives.forEach((obj) => {
                const data = {
                    'id': obj.capid,
                    'title': obj.derivative
                };

                list.push(data);
            });

            return list;
        },

        selectVRM() {
            // Hack to fix Edge issue #8229660
            setTimeout(() => {
                this.$els.pxVrmInput.placeholder = '';
                this.$els.pxVrmInput.select();
            }, 10);
        },

        deselectVRM() {
            this.$els.pxVrmInput.placeholder = this.originalPlaceholder;
        },

        softReset() {
            this.$dispatch('PartExchange::softReset', true);
            this.$dispatch('CarFinder::saveValuationSuccess');
            this.$dispatch('PartExchange::resetSlider');
        }
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
        },

        vrmFilter: {
            read(input) {
                return input ? input.replace(/[^A-Z0-9]/gi, '').toUpperCase() : '';
            },

            write(input) {
                return input.replace(/[^A-Z0-9]/gi, '').toUpperCase();
            }
        }
    },

    computed: {
        availableYears() {
            const currentYear = new Date().getFullYear();
            const minYear = currentYear - 20;
            const years = [];

            for (let i = currentYear; i >= minYear; i--) {
                years.push({
                    title: i,
                    value: i.toString()
                });
            }

            return years;
        },

        FinanceFilter() {
            return this.$root.$refs.financeFilter;
        }
    },

    events: {
        'PartExchangeVrm::updatedVrm'(data) {
            if (typeof data.vrm === 'undefined') {
                Object.assign(this.carInfo, this.$store.state.general.PX.Vrm.carInfo);
                Object.assign(this.carDetails, this.$store.state.general.PX.Vrm.carDetails);
            } else {
                this.mileage = data.vrm;
            }
        }
    },

    watch: {
        'carInfo.vrmInput': {
            handler(newVal, oldVal) {
                if (oldVal) {
                    this.detailsChanged();
                }
            }
        }
    },

    created() {
        window.EventsBus.$on('PartExchange::resetPxData', this.resetCarData);
        window.EventsBus.$on('FinanceFilter::setSavedPxData', this.setSavedPxData);
    },

    ready() {
        this.carInfo.registrationYear = this.registrationYear;
    },

    components: {
        appSelect
    }
});
</script>
