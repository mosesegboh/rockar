<template>
    <div class="my-current-cars">
        <div class="preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <app-modal testibility-class="px-close" width="60%" class-name="px-acc" v-ref:my-current-car-px-modal>
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

                <!--<template v-if="bookServiceUrl">
                    <button class="button-narrow book-a-service" @click="bookService()">
                        {{ 'Book a Service' | translate }}
                    </button>
                </template>-->
            </div>

            <div class="my-current-cars-right my-px-carvalue">
                <div class="part-exchange-summary">
                    <table class="table table-borderless no-margin">
                        <tr>
                            <td class="my-px-value-label">{{ 'Estimated Trade in Value' | translate }}</td>
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
                            <td><button class="button-narrow edit-px" @click="openPxModal()">{{ 'Edit Trade in' | translate }}</button></td>
                            <td>
                                <app-confirmation-modal :confirmation-question="'Do you really want to Remove?' | translate">
                                    <button class="button-empty-light button-narrow remove-px" @click="removePX()">{{ 'Remove' | translate }}</button>
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
                            <td class="my-px-value-label">{{ 'Estimated Trade in Value' | translate }}</td>
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

                    <button class="button" @click="openPxModal()">{{ 'Edit Trade in' | translate }}</button>
                    <app-confirmation-modal :confirmation-question="'Do you really want to Remove?' | translate">
                        <button class="button-empty-light" @click="removePX()">{{ 'Remove' | translate }}</button>
                    </app-confirmation-modal>
                    <!--<button class="button" @click="bookService()">{{ 'Book a Service' | translate }}</button>-->
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import coreMyCurrentCars from 'mini/components/MyAccount/MyCurrentCars';
    export default coreMyCurrentCars.extend({

        methods: {
            removePXSuccess() {
                this.ajaxLoading = false;
                this.resetCarInfoAndDetails();
                this.$destroy(true);
                document.getElementById('add-px').className = document.getElementById('add-px').className.replace(/hide-button/, '');
                this.$store.commit('setNotificationMessage', { message: this.translateString('Car is removed.'), type: 'success' });
            },

            resetCarInfoAndDetails() {
                this.PXVrmStoreState.carInfo.capId = null;
                this.PXVrmStoreState.carInfo.derivative = null;
                this.PXVrmStoreState.carInfo.model = null;
                this.PXVrmStoreState.carInfo.title = null;
                this.PXVrmStoreState.carInfo.vrm = null;
                this.PXVrmStoreState.carInfo.vrmInput = '';
                this.PXVrmStoreState.carDetails.result = false;
                this.PXVrmStoreState.carDetails.vrmDisabled = false;
                this.PXVrmStoreState.carNotFound = false;
                this.PXVrmStoreState.carAlternatives = [];
            }
        },

        computed: {
            PXVrmStoreState() {
                return this.$store.state.general.PX.Vrm;
            }
        }
    });
</script>
