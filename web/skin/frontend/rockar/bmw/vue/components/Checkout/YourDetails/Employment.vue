<template>
    <div class="finance-credit">
        <div class="general-preloader" v-show="ajaxLoading">
            <div class="show-loading"></div>
        </div>
        <div>
            <validator name="employment">
                <div class="row">
                    <div class="col-6">
                        <label :class="cssClassForRequiredLabel">
                            {{ 'Employment Status' | translate }}
                        </label>
                        <app-select
                            id="employment-status-select-active"
                            @select="selectEmploymentStatus"
                            title="-"
                            :init-selected="formData.employment_status"
                            :valid="$employment.employeeStatus.valid"
                            :options="createSelect(true, employmentStatuses)"
                        ></app-select>
                        <div class="validation-advice" v-if="$employment.employeeStatus.required">
                            {{ requiredFieldErrorMessage | translate }}
                        </div>
                        <input
                            type="hidden"
                            data-id="employment_status-active"
                            v-model="formData.employment_status"
                            v-validate:employee-status="{ required: { rule: true, initial: 'off' }}"
                        />
                    </div>

                    <div class="col-6">
                        <div v-if="!isUnemployed">
                            <label :class="conditionalClassFromEmploymentStatus">
                                {{ 'Occupation' | translate }}
                            </label>
                            <input
                                type="text"
                                data-id="employee_occupation-active"
                                v-model="formData.occupation"
                                v-validate:employee-occupation="{ requiredWhenNotStudentOrRetired: { rule: true, initial: 'off' }}"
                            />
                            <div class="validation-advice" v-if="!$employment.employeeOccupation.valid">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" v-if="!isUnemployed">
                    <div class="col-6">
                        <div>
                            <label :class="conditionalClassFromEmploymentStatus">
                                {{ 'Industry' | translate }}
                            </label>
                            <app-select
                                id="industry-select"
                                @select="selectEmploymentIndustry"
                                title="-"
                                :init-selected="formData.employment_industry"
                                :valid="$employment.employmentIndustry.valid"
                                :options="createSelect(true, industryTypeOptions)"
                            ></app-select>
                            <input
                                type="hidden"
                                v-model="formData.employment_industry"
                                v-validate:employment-industry="{ requiredWhenNotStudentOrRetired: { rule: true, initial: 'off' }}"
                            />
                            <div class="validation-advice" v-if="!$employment.employmentIndustry.valid">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <label :class="conditionalClassFromEmploymentStatus">
                            {{ 'Employer' | translate }}
                        </label>
                        <input
                            type="text"
                            v-model="formData.current_employer"
                            v-validate:current-employer="{ requiredWhenNotStudentOrRetired: { rule: true, initial: 'off' }}"
                        />
                        <div class="validation-advice" v-if="!$employment.currentEmployer.valid">
                            {{ requiredFieldErrorMessage | translate }}
                        </div>
                    </div>
                </div>
                <div class="row" v-if="!isUnemployed">
                    <div class="col-6">
                        <time-span-selector
                            :label="'Duration at Current Employer'"
                            :label-class="conditionalClassFromEmploymentStatus"
                            @input="durationCurrentSelected"
                            :timespan="parseInt(formData.duration_at_current_employer)"
                        >
                            <div class="validation-advice" v-if="!$employment.durationCurrentEmployer.valid">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </time-span-selector>
                        <input
                            type="hidden"
                            v-model="formData.duration_at_current_employer"
                            initial="off"
                            v-validate:duration-current-employer="{ requiredWhenNotStudentOrRetired: { rule: true, initial: 'off', min: 1 }}"
                        >
                    </div>
                    <div class="col-6">
                        <label :class="conditionalClassFromEmploymentStatus">
                            {{ 'Current Employer Telephone Number' | translate }}
                        </label>
                        <input
                            type="tel"
                            data-id="employers_phone-active"
                            v-model="formData.employers_phone"
                            initial="off"
                            v-validate:validate-phone="employerTelephoneValidation"
                            @blur="mobileInputBlurManipulator"
                            @change="fireManipulateMobile"
                        />
                        <div class="validation-advice" v-if="!$employment.validatePhone.valid">
                            {{ phoneInvalidFormatErrorMessage | translate }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <label class="side-label">
                            {{ 'Previous Employer' | translate }}
                        </label>
                        <input
                            type="text"
                            data-id="previous_employer-active"
                            v-model="formData.previous_employer"
                        >
                    </div>
                    <div class="col-6">
                        <time-span-selector
                            :label="'Duration at Previous Employer'"
                            :label-class="'side-label'"
                            @input="durationPreviousSelected"
                            :timespan="parseInt(formData.duration_at_previous_employer)"
                        >
                            <div class="validation-advice" v-if="!$employment.durationPreviousEmployer.valid">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </time-span-selector>
                        <input
                            type="hidden"
                            v-model="formData.duration_at_previous_employer"
                            initial="off"
                            v-validate:duration-previous-employer="{ requiredWhenPreviousEmployeeCaptured: { rule: true, initial: 'off' } }"
                        >
                    </div>
                </div>
                <div class="row" v-if="pipEnabled">
                    <div class="col-12 padBottom">
                        <label :class="cssClassForRequiredLabel">{{ 'I consider myself to be, or to be associated with a Prominent Influential Person.' | translate }}</label>
                    </div>
                    <div class="col-2">
                        <input
                            type="checkbox"
                            id="pref-yes"
                            value="1"
                            v-model="formData.influential"
                            v-validate:influential-person="{ mandatoryInfluentialPerson: { rule: true, initial: 'off' }}"
                            @change="onlyOneSelected"
                        >
                        <label for="pref-yes"><span></span>
                            <p class="accept-terms-statement">{{ 'Yes' | translate }}</p>
                        </label>
                    </div>
                    <div class="col-2">
                        <input
                            type="checkbox"
                            id="pref-no"
                            value="0"
                            v-model="formData.influential"
                            v-validate:influential-person="{ mandatoryInfluentialPerson: { rule: true, initial:'off' }}"
                            @change="onlyOneSelected"
                        >
                        <label for="pref-no"><span></span>
                            <p class="accept-terms-statement">{{ 'No' | translate }}</p>
                        </label>
                    </div>
                    <div class="validation-advice" v-if="$employment.influentialPerson.mandatoryInfluentialPerson">
                        {{ requiredFieldErrorMessage | translate }}
                    </div>
                </div>
                <div class="row your-details-submit section-action-buttons">
                    <div class="col-6">
                        <button class="button button-empty" @click="previousScreen">
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
    </div>
</template>

<script>
    import VueValidator from 'vue-validator';
    Vue.use(VueValidator);

    import appSelect from 'core/components/Elements/Select';
    import yourDetailsHelpers from 'bmw/components/Checkout/YourDetails/helpers';
    import appMessages from 'core/components/Elements/Messages';
    import translateString from 'core/filters/Translate';
    import appTooltip from 'core/components/Elements/Tooltip';
    import TimeSpanSelector from 'bmw/components/Elements/TimeSpanSelector';
    import currentLocale from 'bmw/components/Shared/CurrentLocale';
    import uiVariables from 'bmw/components/Shared/UIVariables';

    export default Vue.extend({
        mixins: [
            yourDetailsHelpers,
            currentLocale,
            uiVariables
        ],

        props: {
            industryTypeOptions: {
                required: false,
                type: Object
            },

            employmentStatuses: {
                required: true,
                type: Object
            },

            initEmployment: {
                required: true,
                type: Object
            },

            unemployedStatus: {
                required: true,
                type: Array
            },

            retiredOrStudentStatus: {
                required: true,
                type: Array
            }
        },
        data() {
            return {
                formData: this.initEmployment,
                pipEnabled: false
            }
        },

        computed: {

            isUnemployed() {
                return this.unemployedStatus.includes(this.formData.employment_status);
            },

            isStudentOrRetired() {
                return this.retiredOrStudentStatus.includes(this.formData.employment_status);
            },

            conditionalClassFromEmploymentStatus() {
                return this.isStudentOrRetired ? 'side-label' : this.cssClassForRequiredLabel;
            },

            /**
             * generate the validation object for employer telephone number.
             */
            employerTelephoneValidation() {
                return {
                    [this.isStudentOrRetired ? 'mobileorempty' : 'mobile']: [this.currentLocale, this.localeValidation[this.currentLocale].mobileOrLandLine]
                };
            }
        },

        methods: {
            translateString,

            onlyOneSelected(eve) {
                this.formData.influential = [eve.target.value];
            },

            selectEmploymentStatus(data) {
                this.formData.employment_status = data.value;
            },

            selectEmploymentIndustry(data) {
                this.formData.employment_industry = data.value;
            },

            formSubmit() {
                this.$validate(false, () => {
                    if (this.$employment.valid) {
                        delete this.formData.id;
                        // do not add another if the status is retired/unemployed.
                        this.$emit('success', this.formData);
                    }
                });
            },
            previousScreen() {
                this.$emit('fail');
            },

            durationCurrentSelected(val) {
                this.formData.duration_at_current_employer = val;
            },

            durationPreviousSelected(val) {
                this.formData.duration_at_previous_employer = val;
            },

            mandatoryInfluentialPerson() {
                if (this.formData.influential.length === 0) return false;

                return ['1', '0'].includes(this.formData.influential[0]);
            },

            /**
             * Update form data with credit app response
             *
             * @param creditAppData
             */
            updateFormData(creditAppData) {
                const newFormData = {
                    employment_status: creditAppData.employmentInformation.employementStatus,
                    occupation: creditAppData.employmentInformation.occupation,
                    employment_industry: creditAppData.employmentInformation.industry,
                    current_employer: creditAppData.employmentInformation.employer,
                    employers_phone: creditAppData.employmentInformation.currentEmployerPhoneNo,
                    previous_employer: creditAppData.employmentInformation.previousEmployer
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
            Vue.validator('requiredWhenNotStudentOrRetired', (val) => {
                if (!this.isStudentOrRetired) {
                    return !(val === '') && val !== '0'
                }

                return true;
            });
            Vue.validator('mandatoryInfluentialPerson', () => this.mandatoryInfluentialPerson());

            Vue.validator('requiredWhenPreviousEmployeeCaptured', (val) => {
                if (this.formData.previous_employer) {
                    return parseInt(val) > 0;
                }

                return true;
            });
        },

        components: {
            appSelect,
            appTooltip,
            appMessages,
            TimeSpanSelector
        }
    });
</script>