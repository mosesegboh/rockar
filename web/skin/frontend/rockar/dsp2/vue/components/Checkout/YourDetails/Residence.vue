<template>
    <div class="finance-credit">
        <section class="your-details-section">
            <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
            <validator name="residence">
                <div class="row-grid">
                    <div class="col-6">
                        <label class="hover-label">
                            <div :class="cssClassForRequiredLabel">{{ 'Residential Status' | translate }}</div>
                        </label>
                        <div class="input-box">
                            <app-select
                                title="-"
                                :init-selected="formData.ownership"
                                :options="createSelect(true, ownershipOptions)"
                                @select="selectOwnership"
                            ></app-select>
                            <input
                                type="hidden"
                                v-model="formData.ownership"
                                v-validate:ownership="{ required: { rule: true, initial: 'off' }}"
                            />
                        </div>
                        <div class="validation-advice" v-if="$residence.ownership.required">
                            {{ requiredFieldErrorMessage | translate }}
                        </div>
                    </div>

                    <div class="col-6" v-if="isOwner">
                        <label class="hover-label">
                            <div :class="cssClassForRequiredLabel">{{ 'Property Owner' | translate }}</div>
                        </label>
                        <div class="input-box">
                            <app-select
                                title="-"
                                :init-selected="formData.accommodation_type"
                                :options="createSelect(true, accommodationTypes)"
                                @select="selectAccommodation"
                            ></app-select>
                            <input
                                type="hidden"
                                v-model="formData.accommodation_type"
                                v-validate:property-owner="{ required: { rule: true, initial: 'off' }}"
                            >
                        </div>
                        <div class="validation-advice" v-if="$residence.propertyOwner.required">
                            {{ requiredFieldErrorMessage | translate }}
                        </div>
                    </div>
                </div>
                <div class="row-grid row-grid-hard">
                    <div class="col-6" v-if="isOwner">
                        <label class="hover-label">
                            <div :class="cssClassForRequiredLabel">{{ 'Bond Status' | translate }}</div>
                        </label>
                        <div class="input-box">
                            <app-select
                                title="-"
                                :init-selected="formData.house_status"
                                :options="createSelect(true, bondOptions)"
                                @select="setBondStatus"
                            >
                            </app-select>
                            <input
                                type="hidden"
                                v-model="formData.house_status"
                                v-validate:house-status="{ required: { rule: true, initial: 'off' }}"
                            />
                        </div>
                        <div class="validation-advice" v-if="!$residence.houseStatus.valid">
                            {{ requiredFieldErrorMessage | translate }}
                        </div>
                    </div>
                </div>
                <div class="row-grid">
                    <div class="col-6">
                        <time-span-selector
                            :label="'Duration at current address'"
                            :label-class="cssClassForRequiredLabel"
                            @input="durationCurrentSelected"
                            :timespan="parseInt(formData.duration_at_current_address)">
                            <div class="validation-advice" v-if="!$residence.currentAddress.valid">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </time-span-selector>
                        <input
                            type="hidden"
                            v-model="formData.duration_at_current_address"
                            initial="off"
                            v-validate:current-address="{ required: { rule: true }, min: 1}"
                        />
                    </div>
                    <div class="col-6">
                        <time-span-selector
                            :label="'Duration at previous address'"
                            :label-class="cssClassForRequiredLabel"
                            @input="durationPreviousSelected"
                            :timespan="parseInt(formData.duration_at_previous_address)">
                            <div class="validation-advice" v-if="!$residence.previousAddress.valid">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </time-span-selector>
                        <input
                            type="hidden"
                            v-model="formData.duration_at_previous_address"
                            initial="off"
                            v-validate:previous-address="{ required: { rule: true }, min: 1}"
                        />
                    </div>
                </div>
                <div class="row-grid">
                    <div class="col-12 accept-terms pad-bottom">
                        <div class="dsp2-subtitle-s">{{ 'Postal Address*' | translate }}</div>
                        <input
                            type="checkbox"
                            id="postal-add"
                            v-model="formData.same_as_residential"
                            v-bind:true-value="1"
                            v-bind:false-value="0"
                            v-on:change="cbxPostalAddChanged"
                        />
                        <label for="postal-add"><span></span>
                            <p class="accept-terms-statement">{{ 'Same as residential address' | translate }}</p>
                        </label>
                    </div>
                </div>
                <div class="row-grid">
                    <div class="col-12">
                        <label class="hover-label">
                            <div :class="cssClassForRequiredLabel">{{ 'Residential Address 1' | translate }}</div>
                        </label>
                        <div class="input-box">
                            <input
                                type="text"
                                v-model="formData.address_1"
                                v-validate:address-one="{ required: { rule: true, initial: 'off' }}"
                                id="res-street1"
                            />
                            <div class="validation-advice" v-if="$residence.addressOne.required">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row-grid">
                    <div class="col-12">
                        <label class="hover-label">
                            <div :class="cssClassForRequiredLabel">{{ 'Residential Address 2' | translate }}</div>
                        </label>
                        <div class="input-box">
                            <input
                                type="text"
                                v-model="formData.address_2"
                                v-validate:address-two="{ required : { rule: true, initial: 'off' }}"
                                id="res-street2"
                            />
                            <div class="validation-advice" v-if="$residence.addressTwo.required">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row-grid">
                    <div class="col-12">
                        <label class="hover-label">
                            <div :class="cssClassForRequiredLabel">{{ 'Suburb' | translate }}</div>
                        </label>
                        <div class="input-box">
                            <input
                                type="text"
                                v-model="formData.region"
                                v-validate:postal-region="{ required : { rule: true, initial: 'off' }}"
                                id="res-region"
                            />
                            <div class="validation-advice" v-if="$residence.postalRegion.required">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row-grid">
                    <div class="col-6">
                        <label class="hover-label">
                            <div :class="cssClassForRequiredLabel">{{ 'City' | translate }}</div>
                        </label>
                        <div class="input-box">
                            <input
                                type="text"
                                v-model="formData.city"
                                v-validate:postal-city="{ required: { rule: true, initial: 'off' }}"
                                id="res-city"
                            >
                            <div class="validation-advice" v-if="$residence.postalCity.required">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="hover-label">
                            <div :class="cssClassForRequiredLabel">{{ 'Postal Code' | translate }}</div>
                        </label>
                        <div class="input-box">
                            <input
                                type="text"
                                v-model="formData.postcode"
                                v-validate:post-code="{ required: { rule: true, initial: 'off' }}"
                                id="res-postcode"
                            >
                            <div class="validation-advice" v-if="$residence.postCode.required">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row-grid">
                    <div class="col-6 shift-6">
                        <button class="button dsp2-money" @click="formSubmit">
                            {{ 'Save and Continue' | translate }}
                        </button>
                        <div class="mandatory-title">
                            {{ '* Indicates a required field' | translate }}
                        </div>
                    </div>
                </div>
            </validator>
        </section>
    </div>
</template>

<script>
    import VueValidator from 'vue-validator';
    Vue.use(VueValidator);

    import appSelect from 'core/components/Elements/Select';
    import yourDetailsHelpers from 'core/components/Checkout/YourDetails/helpers';
    import translateString from 'core/filters/Translate';
    import appMessages from 'core/components/Elements/Messages';
    import appTooltip from 'core/components/Elements/Tooltip';
    import TimeSpanSelector from 'dsp2/components/Elements/TimeSpanSelector';
    import uiVariables from 'dsp2/components/Shared/UIVariables';

    export default Vue.extend({
        mixins: [
            yourDetailsHelpers,
            uiVariables
        ],

        props: {
            bondOptions: {
                type: Object,
                required: false
            },

            initResidence: {
                required: true,
                type: Object
            },

            billingAddress: {
                required: false,
                type: Object
            },

            accommodationTypes: {
                required: true,
                type: Object
            },

            ownershipOptions: {
                required: true,
                type: Object
            },

            completed: {
                required: true,
                type: Boolean
            },

            residenceMinYears: {
                required: true,
                type: Number
            },

            gdprDetailsDisclaimer: {
                required: false,
                type: String,
                default: ''
            },

            ownerStatus: {
                required: true,
                type: String
            }
        },

        data() {
            return {
                formData: this.initResidence
            }
        },

        computed: {
            isOwner() {
                return !this.formData.ownership || this.formData.ownership === this.ownerStatus;
            }
        },

        methods: {
            translateString,

            durationCurrentSelected(val) {
                this.formData.duration_at_current_address = val;
            },

            durationPreviousSelected(val) {
                this.formData.duration_at_previous_address = val;
            },

            cbxPostalAddChanged(eve) {
                if (eve.target.checked) {
                    this.formData.address_1 = this.billingAddress.address_1;
                    this.formData.address_2 = this.billingAddress.address_2;
                    this.formData.city = this.billingAddress.city;
                    this.formData.region = this.billingAddress.region;
                    this.formData.postcode = this.billingAddress.postcode;
                } else {
                    this.formData.address_1 = '';
                    this.formData.address_2 = '';
                    this.formData.city = '';
                    this.formData.region = '';
                    this.formData.postcode = '';
                }
            },

            cleanSelect(val) {
                return val.value.toUpperCase() === 'SELECT' ? '' : val.value;
            },

            selectOwnership(data) {
                this.formData.ownership = this.cleanSelect(data);
            },

            selectAccommodation(data) {
                this.formData.accommodation_type = this.cleanSelect(data);
            },

            setBondStatus(data) {
                this.formData.house_status = this.cleanSelect(data);
            },

            formSubmit() {
                this.$validate(false, () => {
                    if (this.$residence.valid) {
                        Reflect.deleteProperty(this.formData, 'id');
                        this.showManual = false;
                        this.$emit('success', this.formData);
                    } else {
                        this.showManual = true;
                    }
                });
            },

            previousScreen() {
                this.$emit('fail');
            },

            /**
             * Update form data with credit app response
             *
             * @param creditAppData
             */
            updateFormData(creditAppData) {
                const newFormData = {
                    ownership: creditAppData.residentialInformation.residentialStatus,
                    accommodation_type: creditAppData.residentialInformation.propertyOwner,
                    house_status: creditAppData.residentialInformation.bondStatus,
                    address_1: creditAppData.residentialInformation.present_Postal_AddressLine1,
                    address_2: creditAppData.residentialInformation.present_Postal_AddressLine2,
                    region: creditAppData.residentialInformation.present_Postal_Suburb,
                    city: creditAppData.residentialInformation.present_Postal_City,
                    postcode: creditAppData.residentialInformation.present_Postal_PostalCode
                }

                this.formData = Object.assign(this.formData, newFormData);
            }
        },

        events: {
            'YourDetails::creditAppUpdate'(creditAppData) {
                this.updateFormData(creditAppData);
            }
        },

        ready() {
            pca.magento.fields.push({ Line1: 'res-street1', City: 'res-city', Line2: 'res-region', Postcode: 'res-postcode' });
            pca.magento.load();
        },

        components: {
            appSelect,
            appTooltip,
            appMessages,
            TimeSpanSelector
        }
    });
</script>
