<template>
    <div class="finance-credit">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <div class="row">
            <div class="col-12">
                <p class="accept-terms">{{ 'To apply for vehicle finance securely online, please provide us with the following details' | translate }}</p>
            </div>
        </div>
        <validator name="personalDetails">
            <div class="row">
                <div class="col-6">
                    <label class="side-label">{{ 'Home Number' | translate }}</label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="form.homePhone"
                                v-validate:home-phone="{ mobileorempty: [this.currentLocale, this.localeValidation[this.currentLocale].mobileOrLandLine] }"
                                @blur="mobileInputBlurManipulator"
                                @keyup="fireManipulateMobile"
                            />
                        </div>
                        <div class="validation-error-msg" v-if="$personalDetails.homePhone.mobileorempty">
                            {{ phoneInvalidFormatErrorMessage | translate }}
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Gender' | translate }}</label>
                    <div class="select-wrapper" :class="{'selected': activeFinanceGroupId > 0}" name="gender" >
                        <app-select
                            title="-"
                            @select="setGender"
                            :init-selected="form.gender"
                            :options="createSelect(true, gender)"
                        ></app-select>
                        <input
                            type="hidden"
                            v-model="form.gender"
                            v-validate:gender="{ required: { rule: true, initial: 'off' }}"
                        >
                    </div>
                    <div class="validation-error-msg" v-if="$personalDetails.gender.required">
                        {{ requiredFieldErrorMessage | translate }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Ethnic Group' | translate }}</label>
                    <div class="select-wrapper" :class="{'selected': activeFinanceGroupId > 0}">
                        <app-select
                            title="-"
                            @select="setEthnicGroup"
                            :init-selected="form.ethnicGroup"
                            :options="createSelect(true, ethnicGroup)"
                        ></app-select>
                        <input
                            type="hidden"
                            v-model="form.ethnicGroup"
                            id="ethnic-group"
                            v-validate:ethnic-group="{ required: { rule: true, initial: 'off' }}"
                        >
                    </div>
                    <div class="validation-error-msg" v-if="$personalDetails.ethnicGroup.required">
                        {{ requiredFieldErrorMessage | translate }}
                    </div>
                </div>
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Preferred Language' | translate }}</label>
                    <div class="select-wrapper" :class="{'selected': activeFinanceGroupId > 0}">
                        <app-select
                            title="-"
                            @select="setPreferredLanguage"
                            :init-selected="form.preferredLanguage"
                            :options="createSelect(true, preferredLanguage)"
                        >
                        </app-select>
                        <input
                            type="hidden"
                            v-model="form.preferredLanguage"
                            id="preferred-language"
                            v-validate:preferred-language="{ required: { rule: true, initial: 'off' }}"
                        />
                    </div>
                    <div class="validation-error-msg" v-if="$personalDetails.preferredLanguage.required">
                        {{ requiredFieldErrorMessage | translate }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Marital Status' | translate }}</label>
                    <div class="select-wrapper" :class="{'selected': activeFinanceGroupId > 0}">
                        <app-select
                            title="-"
                            @select="setMaritalStatus"
                            :init-selected="form.maritalStatus"
                            :options="createSelect(true, maritalStatus)"
                        ></app-select>
                        <input
                            type="hidden"
                            v-model="form.maritalStatus"
                            id="hdn-marital-status"
                            v-validate:marital-status="{ required: { rule: true, initial: 'off' }}"
                        />
                    </div>
                    <div class="validation-error-msg" v-if="$personalDetails.maritalStatus.required">
                        {{ requiredFieldErrorMessage | translate }}
                    </div>
                </div>
                <div class="col-6" v-if="isMarried">
                    <label :class="cssClassForRequiredLabel">{{ 'Marital Contract' | translate }}</label>
                    <div class="select-wrapper" :class="{'selected': activeFinanceGroupId > 0}">
                        <app-select
                            title="-"
                            :init-selected="form.maritalContract"
                            :options="createSelect(true, maritalContract)"
                            :valid="$personalDetails.maritalContract.required"
                            @select="setMaritalContract"
                        ></app-select>
                        <input
                            type="hidden"
                            v-model="form.maritalContract"
                            id="hdn-marital-contract"
                            v-validate:marital-contract="{ required: { rule: isMarried, initial:'off' }}"
                        />
                    </div>
                    <div class="validation-error-msg" v-if="!$personalDetails.maritalContract.valid">
                        {{ requiredFieldErrorMessage | translate }}
                    </div>
                </div>
            </div>
            <div class="row" v-if="spouseDetailsRequired">
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Spouse Name' | translate }}</label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="form.spouse.name | lettersOnly"
                                v-validate:spouse-name="{ required: { rule: true, initial: 'off' }} "
                            >
                        </div>
                        <div class="validation-error-msg" v-if="$personalDetails.spouseName.required">
                            {{ requiredFieldErrorMessage | translate }}
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Spouse Surname' | translate }}</label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="form.spouse.surname | lettersOnly"
                                v-validate:spouse-surname="{ required: { rule:true, initial: 'off' }} "
                            >
                        </div>
                        <div class="validation-error-msg" v-if="$personalDetails.spouseSurname.required">
                            {{ requiredFieldErrorMessage | translate }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" v-if="spouseDetailsRequired">
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Spouse ID Type' | translate }}</label>
                    <div class="select-wrapper" :class="{'selected': activeFinanceGroupId > 0}">
                        <app-select
                            title="-"
                            @select="setSpouseIdType"
                            :init-selected="form.spouse.idType"
                            :options="createSelect(true, spouseIdTypeOptions)"
                        ></app-select>
                        <input
                            type="hidden"
                            v-model="form.spouse.idType"
                            v-validate:spouse-id-type="{ required: { rule: true, initial: 'off' }}"
                        />
                    </div>
                    <div class="validation-error-msg" v-if="$personalDetails.spouseIdType.required">
                        {{ requiredFieldErrorMessage | translate }}
                    </div>
                </div>
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Spouse ID Number' | translate }}</label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="form.spouse.idNumber"
                                initial="off"
                                v-validate:spouse-za-id="{ required: { rule: true, initial: 'off'}, said: saidTypeID }"
                            >
                        </div>
                        <div class="validation-error-msg" v-if="$personalDetails.spouseZaId.said">
                            {{ invalidIdNumberFormatValidationMessage | translate }}
                        </div>
                        <div class="validation-error-msg" v-if="$personalDetails.spouseZaId.required">
                            {{ requiredFieldErrorMessage | translate }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" v-if="spouseDetailsRequired">
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Spouse Mobile Number' | translate }}</label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="form.spouse.mobile"
                                initial="off"
                                v-validate:spouse-number="{ mobile: [this.currentLocale, this.localeValidation[this.currentLocale].mobileRegEx] }"
                                @blur="mobileInputBlurManipulator"
                                @keyup="fireManipulateMobile"
                            >
                        </div>
                    </div>
                    <div class="validation-error-msg" v-if="$personalDetails.spouseNumber.mobile">
                        {{ phoneInvalidFormatErrorMessage | translate }}
                    </div>
                </div>
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Spouse Email Address' | translate }}</label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="form.spouse.mail"
                                v-validate:spouse-mail="{ email: { rule: true, initial: 'off' }}"
                            >
                        </div>
                    </div>
                    <div class="validation-error-msg" v-if="$personalDetails.spouseMail.email">
                        {{ invalidEmailFormatValidationMessage | translate }}
                    </div>
                </div>
            </div>
            <div class="row" v-if="spouseDetailsRequired">
                <div class="col-12 accept-terms">
                    <input
                        type="checkbox"
                        id="accept"
                        v-model="form.spouse.consent"
                        :true-value="1"
                        :false-value="0"
                        v-validate:terms="{ required: { rule: isMarried, initial: 'off' }}"
                    >
                    <label for="accept"><span></span>
                        <p class="accept-terms-statement">{{ 'I warrant and declare that the consent of my spouse, ' +
                            'to the extent required by applicable laws, has been obtained.' | translate }}
                        </p>
                    </label>
                    <div class="validation-error-msg" v-if="$personalDetails.terms.required">
                        {{ requiredFieldErrorMessage | translate }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h5>{{ 'Next of kin details.' | translate }}</h5>
                </div>
            </div>
            <div class="row padBottom">
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Next of kin Full Name' | translate }}</label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="form.nextOfKinName | lettersOnly"
                                v-validate:next-of-kin="{ required: { rule: true, initial: 'off' }}"
                            >
                        </div>
                        <div class="validation-error-msg" v-if="$personalDetails.nextOfKin.required">
                            {{ requiredFieldErrorMessage | translate }}
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Next of kin Mobile Number' | translate }}</label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                initial="off"
                                v-model="form.nextOfKinMobile"
                                v-validate:next-of-kin-mobile="{ mobile: [this.currentLocale, this.localeValidation[this.currentLocale].mobileRegEx] }"
                                @blur="mobileInputBlurManipulator"
                                @keyup="fireManipulateMobile"
                            >
                        </div>
                        <div class="validation-error-msg" v-if="$personalDetails.nextOfKinMobile.mobile">
                            {{ phoneInvalidFormatErrorMessage | translate }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row your-details-submit section-action-buttons">
                <div class="col-6">
                    <button class="button button-empty" @click="previousView">
                        {{ 'Back' | translate }}
                    </button>
                </div>
                <div class="col-6">
                    <button class="button left button-dark" @click="formSubmit">
                        {{ 'Save and Continue' | translate }}
                    </button>
                </div>
            </div>
        </validator>
    </div>
</template>

<script>
    import VueValidator from 'vue-validator';
    Vue.use(VueValidator);

    import appSelect from 'core/components/Elements/Select';
    import yourDetailsHelpers from 'motorrad/components/Checkout/YourDetails/helpers';
    import appMessages from 'core/components/Elements/Messages';
    import translateString from 'core/filters/Translate';
    import currentLocale from 'motorrad/components/Shared/CurrentLocale';
    import uiVariables from 'motorrad/components/Shared/UIVariables';
    import inputFilter from 'motorrad/components/Shared/InputFilters';

    export default Vue.extend({
        mixins: [
            yourDetailsHelpers,
            currentLocale,
            uiVariables,
            inputFilter
        ],

        props: {
            initPersonalDetails: {
                required: true,
                type: Object
            },

            gender: {
                type: Object,
                required: true
            },

            ethnicGroup: {
                type: Object,
                required: true
            },

            preferredLanguage: {
                type: Object,
                required: true
            },

            maritalStatus: {
                type: Object,
                required: true
            },

            maritalContract: {
                type: Object,
                required: true
            },

            spouseIdTypeOptions: {
                type: Object,
                required: true
            },

            marriedLivingApartStatus: {
                type: Array,
                required: true
            },

            maritalContractTypeRequiresSpouseDetails: {
                type: Array,
                required: true
            },

            zaIdType: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                form: {
                    homePhone: this.initPersonalDetails.home_tel,
                    gender: this.preferSavedGender(this.initPersonalDetails.gender),
                    ethnicGroup: this.initPersonalDetails.race,
                    preferredLanguage: this.initPersonalDetails.preferred_language,
                    maritalStatus: this.initPersonalDetails.marital_status,
                    maritalContract: this.initPersonalDetails.marriage_type,
                    nextOfKinName: this.initPersonalDetails.kin_name,
                    nextOfKinMobile: this.initPersonalDetails.kin_tel,
                    spouse: {
                        name: this.initPersonalDetails.spouse_first_name,
                        surname: this.initPersonalDetails.spouse_last_name,
                        idType: this.initPersonalDetails.spouse_id_type,
                        idNumber: this.initPersonalDetails.spouse_id_no,
                        mail: this.initPersonalDetails.spouse_email,
                        mobile: this.initPersonalDetails.spouse_cell_number,
                        consent: this.initPersonalDetails.spouse_consent
                    }
                },
                idNumber: null
            }
        },

        computed: {
            isMarried() {
                return this.marriedLivingApartStatus.includes(this.form.maritalStatus);
            },

            spouseDetailsRequired() {
                return this.isMarried && this.maritalContractTypeRequiresSpouseDetails.includes(this.form.maritalContract);
            },

            spouseIdDocument() {
                return this.form.spouse.idType === this.zaIdType;// when data is received,
            },

            saidTypeID() {
                if (this.spouseIdDocument) {
                    return [true, true];
                }

                return [1, 3];
            },

            genderFromIdNumber() {
                if (this.idNumber) {
                    return parseInt(this.idNumber.substring(6, 7)) <= 4 ? 'GRFML' : 'GRMAL';
                }

                return '';
            }

        },

        methods: {
            translateString,

            mapFormData() {
                return {
                    marital_status: this.form.maritalStatus,
                    home_tel: this.form.homePhone,
                    gender: this.form.gender,
                    race: this.form.ethnicGroup,
                    preferred_language: this.form.preferredLanguage,
                    marriage_type: this.form.maritalContract,
                    spouse_first_name: this.form.spouse.name,
                    spouse_last_name: this.form.spouse.surname,
                    spouse_id_type: this.form.spouse.idType,
                    spouse_id_no: this.form.spouse.idNumber,
                    spouse_cell_number: this.form.spouse.mobile,
                    spouse_email: this.form.spouse.mail,
                    kin_name: this.form.nextOfKinName,
                    kin_tel: this.form.nextOfKinMobile,
                    spouse_consent: this.form.spouse.consent
                };
            },

            formSubmit() {
                this.$validate(false, () => {
                    if (this.$personalDetails.valid) {
                        this.$emit('success', this.mapFormData());
                    }
                });
            },

            cleanSelect(data) {
                return data.value.toUpperCase() === 'SELECT' ? '' : data.value;
            },

            setGender(data) {
                this.form.gender = this.cleanSelect(data);
            },

            setMaritalStatus(data) {
                this.form.maritalStatus = this.cleanSelect(data);
            },

            setSpouseIdType(data) {
                this.form.spouse.idType = this.cleanSelect(data)
            },

            setEthnicGroup(data) {
                this.form.ethnicGroup = this.cleanSelect(data);
            },

            setPreferredLanguage(data) {
                this.form.preferredLanguage = this.cleanSelect(data);
            },

            setMaritalContract(data) {
                this.form.maritalContract = this.cleanSelect(data);
            },

            previousView() {
                this.$emit('fail');
            },

            /**
             * method returns either the saved gender when present or the gender
             * calculated from the users id number
             * @param {String} val, the saved gender
             */
            preferSavedGender(val) {
                return val || this.genderFromIdNumber;
            },

            /**
             * Update form data with credit app response
             *
             * @param creditAppData
             */
            updateDetails(creditAppData) {
                const newFormData = {
                    homePhone: creditAppData.personalDetails.homeNumber,
                    gender: creditAppData.personalDetails.gender,
                    ethnicGroup: creditAppData.personalDetails.ethenicGroup,
                    preferredLanguage: creditAppData.personalDetails.preferredLanguage,
                    maritalStatus: creditAppData.personalDetails.maritalStatus,
                    maritalContract: creditAppData.personalDetails.maritalContract,
                    nextOfKinName: creditAppData.personalDetails.nokFullName,
                    nextOfKinMobile: creditAppData.personalDetails.nokMobileNumber,
                    spouse: {
                        name: creditAppData.personalDetails.spouseName,
                        surname: creditAppData.personalDetails.spouseSurname,
                        idType: creditAppData.personalDetails.spouseIDType,
                        idNumber: creditAppData.personalDetails.spouseIDNumber,
                        mail: creditAppData.personalDetails.spouseEmailAddress,
                        mobile: creditAppData.personalDetails.spouseMobileNo
                    }
                }

                this.formData = Object.assign(this.form, newFormData);
            }
        },

        events: {
            'YourDetails::creditAppUpdate'(creditAppData) {
                this.updateDetails(creditAppData);
            }
        },

        created() {
            this.$root.$on('Address::SA_ID_NUMBER_SET', (val) => {
                this.idNumber = val;
                if (!this.form.gender) {
                    this.form.gender = this.genderFromIdNumber;
                }
            });
        },

        beforeDestroy() {
            this.$root.$off('Address::SA_ID_NUMBER_SET');
        },

        components: {
            appSelect,
            appMessages
        }
    });
</script>
