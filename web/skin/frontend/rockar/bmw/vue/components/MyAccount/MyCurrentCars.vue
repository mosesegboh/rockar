<template>
    <div class="my-current-cars">
        <div class="preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <app-modal class-name="px-acc" v-ref:my-current-car-px-modal>
            <div slot="content">
                <app-account-part-exchange
                        :pe-id="pxId"
                        :valuation-url="valuationUrl"
                        :car-details-url="carDetailsUrl"
                        :reset-url="resetUrl"
                        :additional-info='additionalInfo'
                        :explanatory-text="explanatoryText"
                        :active-condition="activeCondition"
                        :car-conditions='carConditions'
                        :car-alternative-details-url="carAlternativeDetailsUrl"
                        :save-valuation-url="saveValuationUrl"
                        :save-to-session-url="saveToSessionUrl"
                        :saved="saved"
                        :saved-px-list="savedPxList"
                        :make-url="makeUrl"
                        :range-url="rangeUrl"
                        :model-url="modelUrl"
                        :year-url="yearUrl"
                        :colour-url="colourUrl"
                        :derivative-url="derivativeUrl"
                        :custom-car-url="customCarUrl"
                        :active-px-url="activePxUrl"
                        :saved-px="savedPx"
                        :can-open-custom="false"
                        :can-edit="false"
                        v-ref:part-exchange-filter
                ></app-account-part-exchange>
            </div>
        </app-modal>

        <div class="my-current-cars-wrap">
            <div class="my-current-cars-left">
                <div class="row">
                    <div class="col-4 my-px-vrm">
                        <div class="car-plate">{{ vrm }}</div>
                    </div>
                    <div class="col-8 my-px-carinfo">
                        <p>{{ title }}</p>
                    </div>
                </div>
            </div>

            <div class="my-current-cars-right my-px-carvalue">
                <div class="part-exchange-summary">
                    <table class="table table-borderless no-margin">
                        <tr>
                            <td class="my-px-value-label">{{ 'Trade-In Value' | translate }}</td>
                            <td class="my-px-value-amount">{{ pxValueCalculated | numberFormat '0,0.00' true }}</td>
                        </tr>
                        <tr>
                            <td>{{ 'Total Mileage' | translate }}</td>
                            <td>{{ mileage | numberFormat '0,0' false }}</td>
                        </tr>
                        <tr>
                            <td>{{ 'Outstanding Finance' | translate }}</td>
                            <td>{{ outstandingFinance | numberFormat '0,0.00' true }}</td>
                        </tr>
                        <tr>
                            <td>{{ 'Trade-In Valuation Expiration Date' | translate }}</td>
                            <td v-if="expireDate">{{ expireDate }}</td>
                            <td v-else class="my-px-date-expired">{{ 'Expired' | translate }}</td>
                        </tr>
                        <tr>
                            <td><button class="button-narrow" @click="openPxModal()">{{ 'Edit Trade-In' | translate }}</button></td>
                            <td>
                                <app-confirmation-modal :confirmation-question="'Do you really want to Remove?' | translate">
                                    <button class="button-empty-light button-narrow" @click="removePX()">{{ 'Remove' | translate }}</button>
                                </app-confirmation-modal>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="my-current-cars-mobile">
            <div class="row mobile-actions">
                <div class="col-12">
                    <div class="my-account-header">
                        <p class="my-account-heading h-common">
                            {{ 'Your Car' | translate }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 vrm-wrapper">
                    <div class="car-plate">{{ vrm }}</div>
                </div>
            </div>

            <div class="row mobile-title">
                <div class="col-12">
                    {{ title }}
                </div>
            </div>

            <div class="row mobile-table">
                <div class="col-12">
                    <table class="table table-borderless">
                        <tr>
                            <td class="my-px-value-label">{{ 'Trade-In Value' | translate }}</td>
                            <td class="my-px-value-amount">{{ pxValueCalculated | numberFormat '0,0.00' true }}</td>
                        </tr>
                        <tr>
                            <td>{{ 'Total Mileage' | translate }}</td>
                            <td>{{ mileage | numberFormat '0,0' false }}</td>
                        </tr>
                        <tr>
                            <td>{{ 'Outstanding Finance' | translate }}</td>
                            <td>{{ outstandingFinance | numberFormat '0,0.00' true }}</td>
                        </tr>
                        <tr>
                            <td>{{ 'Trade-In Valuation Expiration Date' | translate }}</td>
                            <td v-if="expireDate">{{ expireDate }}</td>
                            <td v-else class="my-px-date-expired">{{ 'Expired' | translate }}</td>
                        </tr>
                    </table>

                    <button class="button" @click="openPxModal()">{{ 'Edit Trade-In' | translate }}</button>
                    <app-confirmation-modal :confirmation-question="'Do you really want to Remove?' | translate">
                        <button class="button-empty-light" @click="removePX()">{{ 'Remove' | translate }}</button>
                    </app-confirmation-modal>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
    import translateString from 'core/filters/Translate';
    import appModal from 'core/components/Elements/Modal';
    import appAccountPartExchange from 'bmw/components/MyAccount/PartExchange';
    import appConfirmationModal from 'core/components/Elements/ConfirmationModal';

    export default Vue.extend({
        props: {
            pxId: {
                required: true,
                type: Number
            },
            vrm: {
                required: true,
                type: String
            },
            title: {
                required: true,
                type: String
            },
            description: {
                required: true,
                type: String
            },
            removePxUrl: {
                required: true,
                type: String
            },
            pxValue: {
                required: true,
                type: Number
            },
            outstandingFinance: {
                required: true,
                type: Number
            },
            expireDate: {
                // Format 'DD.MM.YYYY
                required: true,
                type: String
            },
            mileage: {
                required: true,
                type: Number
            },
            additionalInfo: {
                required: true,
                type: Array
            },
            carConditions: {
                required: true,
                type: Array
            },
            activeCondition: {
                required: true,
                type: Number
            },
            makeUrl: {
                required: true,
                type: String
            },
            rangeUrl: {
                required: true,
                type: String
            },
            modelUrl: {
                required: true,
                type: String
            },
            yearUrl: {
                required: true,
                type: String
            },
            colourUrl: {
                required: true,
                type: String
            },
            derivativeUrl: {
                required: true,
                type: String
            },
            customCarUrl: {
                required: true,
                type: String
            },
            activePxUrl: {
                required: true,
                type: String
            },
            carAlternativeDetailsUrl: {
                required: true,
                type: String
            },
            saveValuationUrl: {
                required: true,
                type: String
            },
            valuationUrl: {
                required: true,
                type: String
            },
            carDetailsUrl: {
                required: true,
                type: String
            },
            resetUrl: {
                required: true,
                type: String
            },
            saveToSessionUrl: {
                required: true,
                type: String
            },
            saved: {
                required: false,
                type: Boolean,
                default: false
            },
            savedPxList: {
                required: false,
                type: Array,
                default() {
                    return [];
                }
            },
            savedPx: {
                required: false,
                default: false
            },
            explanatoryText: {
                required: false,
                type: String,
                default: ''
            },
            bookServiceUrl: {
                required: false,
                type: String,
                default: false
            }
        },

        data() {
            return {
                pxRemoved: false,
                ajaxLoading: false
            }
        },

        computed: {
            pxValueCalculated() {
                return this.pxValue - this.outstandingFinance;
            }
        },

        methods: {
            translateString,

            openPxModal() {
                this.$refs.myCurrentCarPxModal.show = true;
            },

            removePX() {
                this.ajaxLoading = true;
                this.$http({
                    url: this.removePxUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        px_id: this.pxId
                    }
                }).then(this.removePXSuccess, this.removePXFail);
            },

            removePXSuccess() {
                this.ajaxLoading = false;
                this.$destroy(true);
                document.getElementById('add-px').className = document.getElementById('add-px').className.replace(/hide-button/, '');
                this.$store.commit('setNotificationMessage', { message: this.translateString('Car is removed.'), type: 'success' });
            },

            removePXFail(error) {
                this.ajaxLoading = false;
                console.error('My Current Cars:', error);
            },

            bookService() {
                var details = JSON.parse(this.savedPx.serialized_object);
                var carYear = moment(`01.${details.cap_extended.introduced_month}.${details.cap_extended.introduced_year}`, 'DD.MM.YYYY');
                var duration = moment.duration(moment().diff(carYear));

                window.location.href = `${this.bookServiceUrl}?${jQuery.param({
                    name: details.cap_extended.product_name,
                    age: Math.floor(duration.asYears()),
                    brand: details.cap.manufacturer_code,
                    details: details.cap_extended.product_name,
                    fuel_type: details.cap.fueltype !== undefined ? details.cap.fueltype : null,
                    license_plate: this.savedPx.license_plate,
                    mileage: null,
                    model: details.cap.model,
                    transmission: details.cap_extended.transmission
                })}`;
            }
        },

        events: {
            'MyCurrentCars::valuationUpdate'(status) {
                if (status) {
                    var PX = this.$root.$refs.myPartExchange.$children[0];
                    this.pxValue = PX.valuationPrice;
                    this.outstandingFinance = PX.outstandingFinanceOriginal;
                    this.mileage = PX.mileageOriginal;
                    this.vrm = PX.$refs.partExchange.carInfo.vrm;
                    this.title = PX.$refs.partExchange.carInfo.model;
                    this.description = PX.$refs.partExchange.carInfo.title;
                    this.$root.$refs.myPartExchange.show = false;
                } else {
                    this.$root.$refs.myPartExchange.show = false;
                    console.error('PartExchange valuation save returned error');
                }
            }
        },

        components: {
            appModal,
            appAccountPartExchange,
            appConfirmationModal
        }
    });
</script>
