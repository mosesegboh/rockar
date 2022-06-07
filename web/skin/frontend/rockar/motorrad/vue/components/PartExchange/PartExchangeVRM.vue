<template>
    <div :class="['px-vrm-block', isPopup ? 'inside-popup' : '', isAccordion ? 'inside-accordion' : '']">
        <p class="px-regular-heading">
            {{ 'Please provide us with your vehicle registration number:' | translate }}
        </p>

        <div class="px-vrm-block-input" v-show="!carAlternatives.length || carDetails.result">
            <input type="text"
                   placeholder="{{ originalPlaceholder }}"
                   @focus="selectVRM()"
                   @blur="deselectVRM()"
                   :class="['px-input', 'keyboard-plate', PX.canEdit ? '' : 'full-width']"
                   v-model="carInfo.vrmInput | vrmFilter"
                   v-el:px-vrm-input
                   :disabled="notCarFinderStep && carDetails.vrmDisabled"
            >

        </div>
        <div class="px-vrm-block-input" v-else>
            <app-select title="-- Select your car --" :item-height="79" :options="carAlternatives" custom-event="PartExchangeVRM::setAlternativeCar"></app-select>
        </div>

        <div class="px-car-info" v-if="carDetails.result && !carNotFound">
            <p>{{ 'Ok, so your current vehicle\'s a' | translate }}:</p>
            <p class="px-car-name">{{ carInfo.title }}</p>
            <a v-if="PX.canOpenCustom" href class="px-link" @click.prevent="openCustom">{{ 'Not your vehicle?' | translate }}</a>
        </div>

        <div class="px-car-info" v-if="PX.canOpenCustom && carNotFound">
            <p class="px-error">
                {{ 'We are unable to find your vehicle, ' | translate }}
                <a href class="px-link" @click.prevent="openCustom">{{ 'please click here to complete a manual trade-in.' | translate }}</a>
            </p>
        </div>

        <div class="px-car-info" v-if="!PX.canOpenCustom && carNotFound">
            <p class="px-error">
                {{ 'We are unable to find your vehicle' | translate }}
        </div>

        <div class="px-car-info" v-if="plateNumberError">
            <p class="px-error">
                {{ 'You might have entered an incorrect number' | translate }}
            </p>
        </div>
        <div class="px-mileage-block">
            <div class="px-mileage-block-wrapper">
                <p class="px-regular-heading">
                    {{ 'What year did you first register it in?' | translate }}
                </p>

                <div class="px-vrm-block-input" v-show="!carAlternatives.length || carDetails.result">
                    <input
                        type="number"
                        placeholder="Year"
                        min="minYear"
                        max="currentYear"
                        maxlength="4"
                        :class="['px-input', 'keyboard-plate', PX.canEdit ? '' : 'full-width']"
                        v-model="carInfo.registrationYear | regYearFormat"
                        :disabled="valuationResult"
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import corePartExchangeVRM from 'core/components/PartExchange/PartExchangeVRM';
import numeral from 'numeral';

export default corePartExchangeVRM.extend({
    data() {
        return {
            currentYear: new Date().getFullYear(),
            minYear: 1885,
            carInfo: {
                vrmInput: '',
                vrmInputYear: '',
                vrm: null,
                capId: null,
                title: null,
                model: null,
                registrationYear: null
            },
            originalPlaceholder: 'Registration Number'
        };
    },
    methods: {
        carDetailsFail(error) {
            console.error('Trade-In VRM:', error);
            this.PX.ajaxLoading = false;
            this.carNotFound = true;
            this.plateNumberError = false;
            this.carInfo.vrm = this.carInfo.vrmInput;
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

            this.PX.ajaxLoading = false;
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
                    this.PX.ajaxLoading = true;
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
    }
});
</script>
