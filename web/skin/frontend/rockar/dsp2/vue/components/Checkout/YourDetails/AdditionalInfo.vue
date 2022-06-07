<template>
    <div class="checkout-your-details">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <validator name="additional-info">

            <section class="your-details-section">
                <p class="h5">{{ 'Additional Information:' | translate }}</p>
                <p>{{ 'As part of the finance application we need a few more details' | translate }}</p>


                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Marital Status' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <app-select
                            id="marital_status-select"
                            @select="selectMaritalStatus"

                            title="-"
                            :init-selected="formData.marital_status"
                            :valid="$additionalInfo.marital_status.valid"
                            :options="createSelect(true, this.maritalStatuses)">
                        </app-select>

                        <div class="validation-advice" v-if="!$additionalInfo.marital_status.valid">
                            {{ 'This field is required.' | translate }}
                        </div>

                        <input type="hidden" v-model="formData.marital_status" initial="off" v-validate:marital_status="['required']">
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Nationality' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input type="text" v-model="formData.nationality" data-id="nationality" initial="off" v-validate:nationality="['required', 'alpha']" :classes="{ invalid: 'validation-advice' }">

                        <div class="validation-advice" v-if="$additionalInfo.nationality.alpha">
                            {{ 'Please use alphabetic characters.' | translate }}
                        </div>

                        <div class="validation-advice" v-if="$additionalInfo.nationality.required && !$additionalInfo.nationality.alpha">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Have you ever changed your name?' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input type="checkbox" id="changed_name" data-id="changed_name" v-model="formData.changed_name" v-bind:true-value="1" v-bind:false-value="0">
                        <label for="changed_name"><span></span></label>
                    </div>
                </div>

                <div v-if="formData.changed_name && formData.changed_name != '0'">
                    <div class="row">
                        <div class="col-3">
                            <label class="side-label required">
                                {{ 'Previous title' | translate }}
                            </label>
                        </div>

                        <div class="col-9">
                            <app-select
                                id="previous_title-select"
                                @select="selectPrefix"

                                title="-"
                                :init-selected="formData.previous_title"
                                :valid="$additionalInfo.prevTitle.valid"
                                :options="createSelect(false, this.prefixOptions)">
                            </app-select>

                            <input type="hidden" data-id="previous_title" v-model="formData.previous_title" initial="off" v-validate:prev-title="['required']" :classes="{ invalid: 'validation-advice' }">

                            <div class="validation-advice" v-if="!$additionalInfo.prevTitle.valid">
                                {{ 'This field is required.' | translate }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-3">
                            <label class="side-label required">
                                {{ 'Previous first name' | translate }}
                            </label>
                        </div>

                        <div class="col-9">
                            <input type="text" data-id="previous_first_name" v-model="formData.previous_first_name" initial="off" v-validate:prev-firstname="['required']" :classes="{ invalid: 'validation-advice' }">

                            <div class="validation-advice" v-if="!$additionalInfo.prevFirstname.valid">
                                {{ 'This field is required.' | translate }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-3">
                            <label class="side-label required">
                                {{ 'Previous last name' | translate }}
                            </label>
                        </div>

                        <div class="col-9">
                            <input type="text" data-id="previous_last_name" v-model="formData.previous_last_name" initial="off" v-validate:prev-lastname="['required']" :classes="{ invalid: 'validation-advice' }">

                            <div class="validation-advice" v-if="!$additionalInfo.prevLastname.valid">
                                {{ 'This field is required.' | translate }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Number of dependants' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input type="number" data-id="number_of_dependencies" v-model="formData.number_of_dependencies" initial="off" v-validate:dependencies="['required', 'number']" :classes="{ invalid: 'validation-advice' }">

                        <div class="validation-advice" v-if="$additionalInfo.dependencies.number">
                            {{ 'This is not a number.' | translate }}
                        </div>

                        <div class="validation-advice" v-if="$additionalInfo.dependencies.required && !$additionalInfo.dependencies.number">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Gross Annual Salary' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input 
                            type="number" 
                            data-id="gross_annual_salary" 
                            v-model="formData.gross_annual_salary" 
                            initial="off" 
                            v-validate:annual-salary="['required', 'number']" 
                            :classes="{ invalid: 'validation-advice' }" 
                            placeholder="R"
                        >

                        <div class="validation-advice" v-if="$additionalInfo.annualSalary.number">
                            {{ 'This is not a number.' | translate }}
                        </div>

                        <div class="validation-advice" v-if="$additionalInfo.annualSalary.required && !$additionalInfo.annualSalary.number">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Gross annual other income' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input 
                            type="number" 
                            data-id="gross_annual_other_income" 
                            v-model="formData.gross_annual_other_income" 
                            initial="off" 
                            v-validate:annual-income="['required', 'number']" 
                            :classes="{ invalid: 'validation-advice' }" 
                            placeholder="R"
                        >

                        <div class="validation-advice" v-if="$additionalInfo.annualIncome.number">
                            {{ 'This is not a number.' | translate }}
                        </div>

                        <div class="validation-advice" v-if="$additionalInfo.annualIncome.required && !$additionalInfo.annualIncome.number">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Number of credit cards' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input 
                            type="number" 
                            data-id="number_of_credit_cards" 
                            v-model="formData.number_of_credit_cards" 
                            initial="off" 
                            v-validate:creditcards="['required', 'number']" 
                            :classes="{ invalid: 'validation-advice' }"
                        >

                        <div class="validation-advice" v-if="$additionalInfo.creditcards.number">
                            {{ 'This is not a number.' | translate }}
                        </div>

                        <div class="validation-advice" v-if="$additionalInfo.creditcards.required && !$additionalInfo.creditcards.number">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Does this replace an existing finance agreement?' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input type="checkbox" data-id="replace_existing_agreement" id="replace_existing_agreement" v-model="formData.replace_existing_agreement"  v-bind:true-value="1" v-bind:false-value="0">
                        <label for="replace_existing_agreement"><span></span></label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Your monthly mortgage or rent payments' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input 
                            type="number" 
                            data-id="monthly_mortgage" 
                            v-model="formData.monthly_mortgage" 
                            initial="off" 
                            v-validate:rent-payments="['required', 'number']" 
                            :classes="{ invalid: 'validation-advice' }" 
                            placeholder="R"
                        >

                        <div class="validation-advice" v-if="$additionalInfo.rentPayments.number">
                            {{ 'This is not a number.' | translate }}
                        </div>

                        <div class="validation-advice" v-if="$additionalInfo.rentPayments.required && !$additionalInfo.rentPayments.number">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Other non-debt related committed monthly expenditure' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input 
                            type="number" 
                            data-id="other_monthly_expenditure" 
                            v-model="formData.other_monthly_expenditure" 
                            initial="off" 
                            v-validate:other-expenditure="['required', 'number']" 
                            :classes="{ invalid: 'validation-advice' }" 
                            placeholder="R"
                        >

                        <div class="validation-advice" v-if="$additionalInfo.otherExpenditure.number">
                            {{ 'This is not a number.' | translate }}
                        </div>

                        <div class="validation-advice" v-if="$additionalInfo.otherExpenditure.required && !$additionalInfo.otherExpenditure.number">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>

                <section class="your-details-section">
                    <p class="h5">{{ 'Direct Debit Details:' | translate }}</p>
                    <p>{{ 'Please enter the details for the account that you wish the finance repayments to be made from.' | translate }}</p>
                </section>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Name of bank account' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input type="text" data-id="name_of_bank_account" v-model="formData.name_of_bank_account" initial="off" v-validate:bank-account="['required']" :classes="{ invalid: 'validation-advice' }">

                        <div class="validation-advice" v-if="!$additionalInfo.bankAccount.valid">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Sort Code' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input type="text" data-id="sort_code" v-model="formData.sort_code" initial="off" v-validate:sort-code="['required']" :classes="{ invalid: 'validation-advice' }">

                        <div class="validation-advice" v-if="!$additionalInfo.sortCode.valid">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Account number' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <input type="text" data-id="account_number" v-model="formData.account_number" initial="off" v-validate:account-number="['required']" :classes="{ invalid: 'validation-advice' }">

                        <div class="validation-advice" v-if="!$additionalInfo.accountNumber.valid">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="side-label required">
                            {{ 'Time with bank' | translate }}
                        </label>
                    </div>

                    <div class="col-9">
                        <div class="row">

                            <div class="row-elements two-in-row form-elements">
                                <div class="row-element">
                                    <app-select
                                        id="years_time_with_bank-select"
                                        @select="selectTimeYears"

                                        title="-"
                                        :init-selected="formData.years_time_with_bank"
                                        :valid="$additionalInfo.yearsTime.valid"
                                        :options="createCustomSelect(36)">
                                    </app-select>

                                    <span class="details-label">
                                        {{ 'Year' | translate }}
                                    </span>

                                    <div class="validation-advice" v-if="!$additionalInfo.yearsTime.valid">
                                        {{ 'This field is required.' | translate }}
                                    </div>
                                </div>

                                <div class="row-element">
                                    <app-select
                                        id="months_time_with_bank-select"
                                        @select="selectTimeMonths"

                                        title="-"
                                        :init-selected="formData.months_time_with_bank"
                                        :valid="$additionalInfo.monthsTime.valid"
                                        :options="createCustomSelect(13)">
                                    </app-select>

                                    <span class="details-label">
                                        {{ 'Month' | translate }}
                                    </span>

                                    <div class="validation-advice" v-if="!$additionalInfo.monthsTime.valid">
                                        {{ 'This field is required.' | translate }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" data-id="years_time_with_bank" v-model="formData.years_time_with_bank" initial="off" v-validate:years-time="['required']">
                        <input type="hidden" data-id="months_time_with_bank" v-model="formData.months_time_with_bank" initial="off" v-validate:months-time="['required']">
                    </div>
                </div>

                <div class="row section-action-buttons">
                    <div class="shift-3 col-9">
                        <button class="button left button-dark" @click="formSubmit()">
                            {{ 'Save and Continue' | translate }}
                        </button>
                    </div>
                </div>
            </section>
        </validator>
    </div>
</template>

<script>
    import VueValidator from 'vue-validator';
    Vue.use(VueValidator);

    import appSelect from 'core/components/Elements/Select';
    import yourDetailsHelpers from 'core/components/Checkout/YourDetails/helpers';

    export default Vue.extend({
        mixins: [yourDetailsHelpers],

        props: {
            initInfo: {
                required: true,
                type: Object
            },

            maritalStatuses: {
                required: true,
                type: Object
            },

            prefixOptions: {
                required: true,
                type: Array
            }
        },

        data() {
            return {
                formsFilled: [],
                formData: this.initInfo
            }
        },

        methods: {
            selectMaritalStatus(data) {
                this.formData.marital_status = data.value;
            },

            selectPrefix(data) {
                this.formData.previous_title = data.value;
            },

            selectTimeYears(data) {
                this.formData.years_time_with_bank = data.value;
            },

            selectTimeMonths(data) {
                this.formData.months_time_with_bank = data.value;
            },

            formSubmit() {
                this.$validate(false, () => {
                    if (this.$additionalInfo.valid) {
                        this.formData.step = 'summary_step';
                        this.$emit('success', this.formData);

                        this.$nextTick(() => {
                            this.$resetValidation();
                        });
                    }
                });
            }
        },

        components: {
            appSelect
        }
    });
</script>
