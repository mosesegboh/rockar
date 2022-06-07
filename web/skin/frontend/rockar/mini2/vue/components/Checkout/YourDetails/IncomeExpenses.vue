<template>
    <div class="finance-credit">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <div class="dsp2-subtitle-s">{{ 'Monthly Income*' | translate }}</div>
        <validator name="finances">
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label">
                        <span :class="cssClassForRequiredLabel">{{ 'Gross Monthly Salary' | translate }}</span>
                    </label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.income.grossMonthlySalary | numberFormat '0,0' true"
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
                    <label class="hover-label"><span>{{ 'Commission (3 month average)' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.income.commission | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Car Allowance (included in Gross)' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.income.carAllowance | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Additional Income' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.income.additionalIncome | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label">
                        <span :class="additionalIncomeGreaterThanZero ? cssClassForRequiredLabel : 'side-label'">
                            {{ 'Source of Additional Income' | translate }}
                        </span>
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
                    <label class="hover-label">
                        <span :class="cssClassForRequiredLabel">{{ 'Net / Take Home Salary' | translate }}</span>
                    </label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.income.netSalary | numberFormat '0,0' true"
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
                    {{ this.formatNumeral(calculateTotalIncome) | numberFormat '0,0' true }}
                </div>
            </div>
            <div class="dsp2-subtitle-s">{{ 'Monthly Expenses*' | translate }}</div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Bond' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.bondRepayment | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Rent Repayment' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.rentRepayment | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Maintenance Expenses' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.maintenance | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Household Expenses' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.householdExpenses | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Vehicle Instalment (Excl. to be settled)' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.vehicleInstallment | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Transport Cost' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.transportCosts | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Credit Card Repayment' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.ccRepayment | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Clothing Account' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.clothingAccount | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Policy Repayments' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.policyRepayments | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Personal Loan' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.personalLoan | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Overdraft Repayment' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.overdraftRepayment | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Furniture Account' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.furnitureAccount | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Education Cost' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.educationCosts | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Rates, Water & Electricity' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.ratesWaterElectricity | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Telephone Payment' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.phone | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Food & Entertainment' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.entertainment | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Medical Aid' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.medical | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><span>{{ 'Other Expenses' | translate }}</span></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                class="currency"
                                v-model="formData.expenses.other | numberFormat '0,0' true"
                                @click="clearContentsWhenZero"
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid bottom-totals">
                <div class="col-6 money-totals">
                    <div class="dsp2-subtitle">{{ 'Your total monthly expenses:' | translate }}</div>
                    <div class="h3">
                        {{ this.formatNumeral(calculateTotalExpenses) | numberFormat '0,0' true }}
                    </div>
                </div>
                <div class="col-6 money-totals">
                    <div class="dsp2-subtitle">{{ 'Total disposable income:' | translate }}</div>
                    <div class="h3">
                        {{ this.formatNumeral(disposableIncome) | numberFormat '0,0' true}}
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
                        <div class="accept-terms-statement">{{ 'Surety' | translate }}</div>
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
                        <div class="accept-terms-statement">{{ 'Guarantor' | translate }}</div>
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
                        <div class="accept-terms-statement">{{ 'Co-Debtor' | translate }}</div>
                    </label>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-12">
                    <label class="hover-label">
                        <span :class="this.anyLiableSelected ? cssClassForRequiredLabel : 'side-label'">
                            {{ 'Liability Details' | translate }}
                        </span>
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
    import CheckoutIncomeExpenses from 'dsp2/components/Checkout/YourDetails/IncomeExpenses.vue';

    export default CheckoutIncomeExpenses.extend({});
</script>
