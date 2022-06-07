<template>
    <div :class="['px-vrm-block', isPopup ? 'inside-popup' : '', isAccordion ? 'inside-accordion' : '']">

        <p class="px-regular-heading">
            {{ 'Considering trading in your vehicle for a ' + vehicleBrandName + '?' | translate }}
        </p>

        <p class="px-regular-heading">
            {{ 'Let\'s get started.' | translate }}
        </p>

        <p class="px-light-text">
            {{ 'Please provide us with your vehicle registration number:' | translate }}
        </p>

        <div class="px-vrm-block-input row" v-show="!carAlternatives.length || carDetails.result">
            <input type="text"
                   placeholder="{{ originalPlaceholder }}"
                   @focus="isExplorer ? '' : selectVRM()"
                   @blur="deselectVRM()"
                   :class="['px-input', PX.isButtonCancelVisible ? 'col-10' : 'col-12' ,'keyboard-plate', PX.canEdit ? '' : 'full-width']"
                   v-model="carInfo.vrmInput | vrmFilter"
                   v-el:px-vrm-input
                   :disabled="(notCarFinderStep && carDetails.vrmDisabled) || (PX.isButtonCancelVisible == '' ? false : PX.isButtonCancelVisible)"
            >
            <div :class="['col-2','cancel-current-car']"
                 @click="softReset"
                 v-if="PX.isButtonCancelVisible"
                 :disabled="PX.isButtonCancelDisabled">
                <span></span>
            </div>
        </div>
        <div class="px-vrm-block-input" v-show="carAlternatives.length">
            <app-select title="-- Select your car --" :item-height="79" :options="carAlternatives" custom-event="PartExchangeVRM::setAlternativeCar"></app-select>
        </div>

        <div class="px-car-info" v-if="PX.canOpenCustom && carNotFound">
            <p class="px-error">
                {{ 'We are unable to find your vehicle, please ' | translate }}
                <a class="px-link" @click.prevent="openCustom">{{ 'click here' | translate }}</a>
                {{ ' to complete a manual trade-in.' | translate }}
            </p>
        </div>

        <div class="px-car-info" v-if="!PX.canOpenCustom && carNotFound">
            <p class="px-error">
                {{ 'We are unable to find your vehicle' | translate }}
            </p>
        </div>

        <div class="px-car-info" v-if="plateNumberError">
            <p class="px-error">
                {{ 'Looks like you\'ve entered an incorrect number' | translate }}
            </p>
        </div>

        <div class="px-mileage-block">
            <p class="px-light-text">{{ 'What year did you first register it in?' | translate }}</p>
                <input
                    type="number"
                    class="px-input keyboard-numbers"
                    placeholder="Year"
                    max="4"
                    v-model="carInfo.registrationYear | regYearFormat"
                    :disabled="(notCarFinderStep && carDetails.vrmDisabled) || (PX.isButtonCancelVisible == '' ? false : PX.isButtonCancelVisible)"
                >
        </div>
    </div>
</template>

<script>
    import appPartExchangeVrm from 'bmw/components/PartExchange/PartExchangeVRM';
    import Constants from 'core/components/Shared/Constants';

    export default appPartExchangeVrm.extend({
        props: {
            vehicleBrandName: {
                type: String,
                required: false,
                default: 'BMW'
            }
        },
        methods: {
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
                                capId: this.carInfo.capId,
                                vrm: this.carInfo.vrmInput,
                                mileage: this.$parent.mileage,
                                registrationYear: this.carInfo.registrationYear
                            }
                        });
                        promise.then(this.carDetailsSuccess, this.carDetailsFail);
                        this.carDetails.result = true;
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

            softReset() {
                this.$dispatch('PartExchange::softReset', true);
                this.$dispatch('CarFinder::saveValuationSuccess');
                this.$dispatch('PartExchange::resetSlider');
                this.$dispatch('PartExchangeVRM::resetSlider');
                this.$parent.disableMoreInfo = true;
                this.alternativeCarSelected = false;
                this.carInfo.registrationYear = null;
            },
        },

        computed: {
            isExplorer() {
                return document.documentMode || /Edge/.test(navigator.userAgent);
            },

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
            }
        },

        data() {
            return {
                alternativeCarSelected: false
            };
        },

        events: {
            'PartExchangeVRM::setAlternativeCar'(capId) {
                if (capId.id) {
                    this.setAlternativeCar(capId);
                    this.$store.state.general.PX.Vrm.carDetails.result = true;
                    this.alternativeCarSelected = true;
                }
            }
        }
    });
</script>
