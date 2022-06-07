<template>
    <div class="finance-credit">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <div class="dsp2-subtitle-s">{{ 'Monthly Income*' | translate }}</div>
        <validator name="finances">
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div :class="cssClassForRequiredLabel">{{ 'Gross Monthly Salary' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.income.grossMonthlySalary | currency"
                                v-validate:gross-income="{ requiredNotZero: { rule: true } }"
                                initial="off"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                        <div class="validation-error-msg" v-if="!$finances.grossIncome.valid">
                            {{ requiredFieldErrorMessage | translate }}
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Commission (3 month average)' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.income.commission | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Car Allowance (included in Gross)' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.income.carAllowance | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Additional Income' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.income.additionalIncome | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label">
                        <div :class="additionalIncomeGreaterThanZero ? cssClassForRequiredLabel : 'side-label'">
                            {{ 'Source of Additional Income' | translate }}
                        </div>
                    </label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="formData.income.sourceOfAdditionalIncome"
                                v-validate:additional-income="{ additionalIncomeRequired: { rule: true }}"
                                initial="off"
                            />
                            <div class="validation-error-msg" v-if="!$finances.additionalIncome.valid">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div :class="cssClassForRequiredLabel">{{ 'Net/Take Home Salary' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.income.netSalary | currency"
                                v-validate:net-salary="{ requiredNotZero: { rule: true } }"
                                initial="off"
                                @click="clearContentsWhenZero"
                            />
                            <div class="validation-error-msg" v-if="!$finances.netSalary.valid">
                                {{ requiredFieldErrorMessage | translate }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="money-totals">
                <div class="dsp2-subtitle">{{ 'Your total monthly income:' | translate }}</div>
                <div class="h3">
                    {{ this.formatNumeral(calculateTotalIncome) }}
                </div>
            </div>
            <div class="dsp2-subtitle-s">{{ 'Monthly Expenses*' | translate }}</div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Bond' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.bondRepayment | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Rent Repayment' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.rentRepayment | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Maintenance Expenses' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.maintenance | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Household Expenses' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.householdExpenses | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Vehicle Instalment (Excl. to be settled)' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.vehicleInstallment | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Transport Cost' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.transportCosts | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Credit Card Repayment' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.ccRepayment | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Clothing Account' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.clothingAccount | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Policy Repayments' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.policyRepayments | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Personal Loan' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.personalLoan | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Overdraft Repayment' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.overdraftRepayment | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Furniture Account' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.furnitureAccount | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Education Cost' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.educationCosts | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Rates, Water and Electricity' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.ratesWaterElectricity | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Telephone Payment' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.phone | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Food and Entertainment' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.entertainment | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Medical Aid' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.medical | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div>{{ 'Other Expenses' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.other | currency"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid bottom-totals">
                <div class="col-6 money-totals">
                    <div class="dsp2-subtitle">{{ 'Total monthly expenses:' | translate }}</div>
                    <div class="h3">
                        {{ this.formatNumeral(calculateTotalExpenses) }}
                    </div>
                </div>
                <div class="col-6 money-totals">
                    <div class="dsp2-subtitle">{{ 'Disposable income:' | translate }}</div>
                    <div class="h3">
                        {{ this.formatNumeral(disposableIncome) }}
                    </div>
                    <input
                        type="hidden"
                        v-model="disposableIncome"
                        v-validate:disposable-income="{ min: 0}"
                        initial="off"
                    />
                    <div class="validation-error-msg" v-if="!$finances.disposableIncome.valid">
                        {{ 'Disposable income needs to be greater than zero.' | translate }}
                    </div>
                </div>
            </div>
            <div class="row-grid bottom-checkboxes">
                <div class="col">
                    <p class="dsp2-body-s">{{ 'I\'m liable as:' | translate }}</p>
                </div>
                <div class="col">
                    <input
                        type="checkbox"
                        id="surety"
                        v-model="formData.liableAs.surety"
                        v-bind:true-value="1"
                        v-bind:false-value="0"
                    >
                    <label for="surety"><span></span>
                        <p class="accept-terms-statement">{{ 'Surety' | translate }}</p>
                    </label>
                </div>
                <div class="col">
                    <input
                        type="checkbox"
                        id="guarantor"
                        v-model="formData.liableAs.guarantor"
                        v-bind:true-value="1"
                        v-bind:false-value="0"
                    >
                    <label for="guarantor"><span></span>
                        <p class="accept-terms-statement">{{ 'Guarantor' | translate }}</p>
                    </label>
                </div>
                <div class="col">
                    <input
                        type="checkbox"
                        id="co-debtor"
                        v-model="formData.liableAs.coDebtor"
                        v-bind:true-value="1"
                        v-bind:false-value="0"
                    >
                    <label for="co-debtor">
                        <span></span>
                        <p class="accept-terms-statement">{{ 'Co-Debtor' | translate }}</p>
                    </label>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-12">
                    <label class="hover-label">
                        <div :class="this.anyLiableSelected ? cssClassForRequiredLabel : 'side-label'">
                            {{ 'Liability Details' | translate }}
                        </div>
                    </label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="formData.liabilityDetails"
                                v-validate:liability-details="{ requiredWhenLiabilitySelected: { rule: true, initial: 'off' }}"
                            />
                        </div>
                        <div class="validation-error-msg" v-if="$finances.liabilityDetails.requiredWhenLiabilitySelected">
                            {{ 'This field is required.' | translate }}
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
    </div>
</template>

<script>
import VueValidator from 'vue-validator';
Vue.use(VueValidator);
import appTooltip from 'core/components/Elements/Tooltip';
import appSelect from 'core/components/Elements/Select';
import yourDetailsHelpers from 'core/components/Checkout/YourDetails/helpers';
import appMessages from 'core/components/Elements/Messages';
import translateString from 'core/filters/Translate';
import currentLocale from 'dsp2/components/Shared/CurrentLocale';
import uiVariables from 'dsp2/components/Shared/UIVariables';

export default Vue.extend({
    mixins: [
        yourDetailsHelpers,
        currentLocale,
        uiVariables
    ],
    props: {
        incomeData: {
            required: true,
            type: Object
        }
    },
    data() {
        return {
            formData: {
                income: {
                    grossMonthlySalary: this.incomeData.monthly_gross_salary,
                    commission: this.incomeData.average_of_three_months_salary,
                    carAllowance: this.incomeData.car_allowance,
                    additionalIncome: this.incomeData.additional_income,
                    sourceOfAdditionalIncome: this.incomeData
                        .souce_of_additional_income,
                    netSalary: this.incomeData.take_home_salary
                },
                expenses: {
                    bondRepayment: this.incomeData.bond_rent_payment,
                    rentRepayment: this.incomeData.rent_amount,
                    maintenance: this.incomeData.maintenance_expenses,
                    householdExpenses: this.incomeData.household_expenses,
                    vehicleInstallment: this.incomeData.vehicle_installments,
                    transportCosts: this.incomeData.transport_cost,
                    ccRepayment: this.incomeData.credit_card_repayments,
                    clothingAccount: this.incomeData.clothing_accounts,
                    policyRepayments: this.incomeData.policy_repayments,
                    personalLoan: this.incomeData.personal_loan_repayment,
                    overdraftRepayment: this.incomeData.over_draft_repayments,
                    furnitureAccount: this.incomeData.furniture_accounts,
                    educationCosts: this.incomeData.education_cost,
                    ratesWaterElectricity: this.incomeData
                        .water_electricity_expenses,
                    phone: this.incomeData.telephone_payments,
                    entertainment: this.incomeData.food_and_entertainment,
                    medical: this.incomeData.medical_expenses,
                    other: this.incomeData.other_expenses
                },
                liableAs: {
                    surety: this.incomeData.liable_as_surety,
                    guarantor: this.incomeData.liable_as_gaurantor,
                    coDebtor: this.incomeData.liable_as_co_debtor
                },
                liabilityDetails: this.incomeData.liable_as_comments
            }
        };
    },
    methods: {
        translateString,

        mapFormData() {
            return {
                monthly_gross_salary: this.getNumber(this.formData.income.grossMonthlySalary),
                average_of_three_months_salary: this.getNumber(this.formData.income.commission),
                car_allowance: this.getNumber(this.formData.income.carAllowance),
                take_home_salary: this.getNumber(this.formData.income.netSalary),
                additional_income: this.getNumber(this.formData.income.additionalIncome),
                souce_of_additional_income: this.formData.income.sourceOfAdditionalIncome,
                bond_rent_payment: this.getNumber(this.formData.expenses.bondRepayment),
                vehicle_installments: this.getNumber(this.formData.expenses.vehicleInstallment),
                credit_card_repayments: this.getNumber(this.formData.expenses.ccRepayment),
                clothing_accounts: this.getNumber(this.formData.expenses.clothingAccount),
                policy_repayments: this.getNumber(this.formData.expenses.policyRepayments),
                transport_cost: this.getNumber(this.formData.expenses.transportCosts),
                education_cost: this.getNumber(this.formData.expenses.educationCosts),
                household_expenses: this.getNumber(this.formData.expenses.householdExpenses),
                water_electricity_expenses: this.getNumber(this.formData.expenses
                    .ratesWaterElectricity),
                personal_loan_repayment: this.getNumber(this.formData.expenses.personalLoan),
                furniture_accounts: this.getNumber(this.formData.expenses.furnitureAccount),
                over_draft_repayments: this.getNumber(this.formData.expenses
                    .overdraftRepayment),
                telephone_payments: this.getNumber(this.formData.expenses.phone),
                food_and_entertainment: this.getNumber(this.formData.expenses.entertainment),
                maintenance_expenses: this.getNumber(this.formData.expenses.maintenance),
                other_expenses: this.getNumber(this.formData.expenses.other),
                rent_amount: this.getNumber(this.formData.expenses.rentRepayment),
                medical_expenses: this.getNumber(this.formData.expenses.medical),
                liable_as_surety: this.formData.liableAs.surety,
                liable_as_gaurantor: this.formData.liableAs.guarantor,
                liable_as_co_debtor: this.formData.liableAs.coDebtor,
                liable_as_comments: this.formData.liabilityDetails
            };
        },

        formSubmit() {
            this.$validate(false, () => {
                if (this.$finances.valid) {
                    this.$emit('success', this.mapFormData());
                }
            });
        },

        previousView() {
            this.$emit('fail');
        },

        getNumber(data) {
            return this.getNumeralFormatting(data).value();
        },

        formatNumeral(valueToFormat) {
            return this.getNumeralFormatting(valueToFormat).format();
        },

        clearContentsWhenZero(obj) {
            if (this.getNumber(obj.currentTarget.value) === 0) {
                obj.currentTarget.value = '';
            }
        },

        /**
         * Update form data with credit app response
         *
         * @param creditAppData
         */
        updateFormData(creditAppData) {
            const newFormData = {
                income: {
                    grossMonthlySalary: creditAppData.incomeAndExpenses.grossMonthySalary,
                    commission: creditAppData.incomeAndExpenses.commission,
                    carAllowance: creditAppData.incomeAndExpenses.carAllowance,
                    additionalIncome: creditAppData.incomeAndExpenses.additionalIncome,
                    sourceOfAdditionalIncome: creditAppData.incomeAndExpenses.sourceOfIncome,
                    netSalary: creditAppData.incomeAndExpenses.netSalary
                },
                expenses: {
                    bondRepayment: creditAppData.incomeAndExpenses.bond,
                    rentRepayment: creditAppData.incomeAndExpenses.rent,
                    maintenance: creditAppData.incomeAndExpenses.maintenanceExpenses,
                    householdExpenses: creditAppData.incomeAndExpenses.houseHoldExpenses,
                    vehicleInstallment: creditAppData.incomeAndExpenses.vehicleInstalments,
                    transportCosts: creditAppData.incomeAndExpenses.transportCost,
                    ccRepayment: creditAppData.incomeAndExpenses.creditCardPayment,
                    clothingAccount: creditAppData.incomeAndExpenses.clothingAccount,
                    policyRepayments: creditAppData.incomeAndExpenses.policyPayments,
                    personalLoan: creditAppData.incomeAndExpenses.personalLoans,
                    overdraftRepayment: creditAppData.incomeAndExpenses.overdraftPayment,
                    furnitureAccount: creditAppData.incomeAndExpenses.furnitureAccount,
                    educationCosts: creditAppData.incomeAndExpenses.educationCost,
                    ratesWaterElectricity: creditAppData.incomeAndExpenses.rateWaterAndElectricity,
                    phone: creditAppData.incomeAndExpenses.telephonePayment,
                    entertainment: creditAppData.incomeAndExpenses.foodEntertainment,
                    medical: creditAppData.incomeAndExpenses.medicalAid,
                    other: creditAppData.incomeAndExpenses.otherExpenses
                }
            }

            this.formData = Object.assign(this.formData, newFormData);

            Object.keys(this.formData.income).forEach((index) => {
                if (index !== 'sourceOfAdditionalIncome') {
                    this.formData.income[index] = this.formatNumeral(this.formData.income[index]);
                }
            });

            Object.keys(this.formData.expenses).forEach((index) => {
                this.formData.expenses[index] = this.formatNumeral(this.formData.expenses[index]);
            });
        }
    },

    computed: {
        additionalIncomeGreaterThanZero() {
            return (
                this.getNumeralFormatting(this.formData.income.additionalIncome).value() > 0
            );
        },

        calculateTotalIncome() {
            return (
                this.getNumber(this.formData.income.grossMonthlySalary) +
                this.getNumber(this.formData.income.commission) +
                this.getNumber(this.formData.income.additionalIncome)
            );
        },

        calculateTotalExpenses() {
            return (
                this.getNumber(this.formData.expenses.bondRepayment) +
                this.getNumber(this.formData.expenses.rentRepayment) +
                this.getNumber(this.formData.expenses.maintenance) +
                this.getNumber(this.formData.expenses.householdExpenses) +
                this.getNumber(this.formData.expenses.vehicleInstallment) +
                this.getNumber(this.formData.expenses.transportCosts) +
                this.getNumber(this.formData.expenses.ccRepayment) +
                this.getNumber(this.formData.expenses.clothingAccount) +
                this.getNumber(this.formData.expenses.policyRepayments) +
                this.getNumber(this.formData.expenses.personalLoan) +
                this.getNumber(this.formData.expenses.overdraftRepayment) +
                this.getNumber(this.formData.expenses.furnitureAccount) +
                this.getNumber(this.formData.expenses.educationCosts) +
                this.getNumber(this.formData.expenses.ratesWaterElectricity) +
                this.getNumber(this.formData.expenses.phone) +
                this.getNumber(this.formData.expenses.entertainment) +
                this.getNumber(this.formData.expenses.medical) +
                this.getNumber(this.formData.expenses.other)
            );
        },

        disposableIncome() {
            return (
                this.getNumber(this.formData.income.netSalary) +
                this.getNumber(this.formData.income.additionalIncome) -
                this.calculateTotalExpenses
            );
        },

        anyLiableSelected() {
            return (
                this.formData.liableAs.coDebtor === 1 ||
                this.formData.liableAs.guarantor === 1 ||
                this.formData.liableAs.surety === 1
            );
        }
    },

    events: {
        'YourDetails::creditAppUpdate'(creditAppData) {
            this.updateFormData(creditAppData);
        }
    },

    ready() {
        Vue.validator('requiredWhenLiabilitySelected', (val) => {
            if (this.anyLiableSelected) {
                return val !== '';
            }

            return true;
        });

        Vue.validator('additionalIncomeRequired', (val) => {
            if (this.additionalIncomeGreaterThanZero) {
                return val !== '';
            }

            return true;
        });
        Vue.validator('requiredNotZero', (val) => {
            if (!val) {
                return false;
            }

            return this.getNumeralFormatting(val).value() > 0;
        });

        Object.keys(this.formData.income).forEach((index) => {
            if (index !== 'sourceOfAdditionalIncome') {
                this.formData.income[index] = this.formatNumeral(this.formData.income[index]);
            }
        });

        Object.keys(this.formData.expenses).forEach((index) => {
            this.formData.expenses[index] = this.formatNumeral(this.formData.expenses[index]);
        });
    },

    components: {
        appSelect,
        appTooltip,
        appMessages
    }
});
</script>
