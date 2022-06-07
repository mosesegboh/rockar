<template>
    <div class="your-details">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <template v-if="financeOptionsIsBusiness">
            <your-details-business
                :top-text="topText"
                :quote-data="quoteData"
                :company-form-url="companyFormUrl"
                :company-types="companyTypes"
                :gdpr-details-disclaimer="gdprDetailsDisclaimer"
            ></your-details-business>
        </template>
        <template v-else>
            <accordion-group
                :active-step="activeStep"
                :open-all="false"
                :child-items="componentIndexes"
            >
                <accordion
                    title="Terms and Conditions"
                    type="right-down"
                    class-name="accordion-light"
                    step-code="credit-app-terms-and-conditions"
                    id="terms-and-conditions"
                    accordion-group-name="CheckoutCreditAppAccordionGroup"
                    :show="screenIndexShow(componentIndexes[indexHelper.terms])"
                >
                    <checkout-terms-and-conditions
                        @success="submitTerms">
                    </checkout-terms-and-conditions>

                    <modal class-name="otp-popup" :show="showOtpPopup" :show-close="false">
                        <div slot="content">
                            <otp-popup
                                :credit-app-data="creditAppData"
                                :otp-data="otpData"
                            />
                        </div>
                    </modal>
                </accordion>
                <accordion
                    title="Deposit"
                    type="right-down"
                    class-name="accordion-light"
                    step-code="credit-app-deposit"
                    id="deposit"
                    accordion-group-name="CheckoutCreditAppAccordionGroup"
                    :show="screenIndexShow(componentIndexes[indexHelper.deposit])"
                >
                    <checkout-deposit
                        :init-deposit-details="depositQuoteData"
                        :deposit-source="depositSource"
                        :deposit-form-url="depositFormUrl"
                        :visible="screenIndexShow(componentIndexes[indexHelper.deposit])"
                        @disable="disableAccordion(indexHelper.deposit)"
                        @fail="revertIndex"
                        @success="submitDeposit">
                    </checkout-deposit>
                </accordion>
                <accordion v-if="isIndividual"
                    title="Personal Details"
                    type="right-down"
                    class-name="accordion-light"
                    step-code="credit-app-personal-details"
                    id="personal-details"
                    accordion-group-name="CheckoutCreditAppAccordionGroup"
                    :show="screenIndexShow(componentIndexes[indexHelper.personalDetails])"
                >
                    <checkout-personal
                        :gender="genderOptions"
                        :init-personal-details="quoteData"
                        :ethnic-group="ethnicOptions"
                        :preferred-language="preferredLanguageOptions"
                        :marital-status="maritalStatuses"
                        :marital-contract="marriageTypeOptions"
                        :spouse-id-type-options="spouseIdTypeOptions"
                        :married-living-apart-status="marriedLivingApartStatus"
                        :marital-contract-type-requires-spouse-details="maritalContractTypeRequiresSpouseDetails"
                        :za-id-type="zaIdType"
                        @fail="revertIndex"
                        @success="submitPersonal">
                    </checkout-personal>
                </accordion>
                <accordion v-if="isIndividual"
                    title="Residential Information"
                    type="right-down"
                    class-name="accordion-light"
                    step-code="credit-app-residency"
                    id="residency"
                    accordion-group-name="AccordionGroup"
                    :show="screenIndexShow(componentIndexes[indexHelper.residency])"
                    :completed="activeComponentIndex > indexHelper.residency"
                >
                    <checkout-residence
                        :accommodation-types="accommodationTypes"
                        :countries="availableCountries"
                        :ownership-options="ownershipOptions"
                        :residence-min-years="residenceMinYears"
                        :init-residence="residenceData"
                        :get-address-url="getAddressUrl"
                        :billing-address="billingAddress"
                        :gdpr-details-disclaimer="gdprDetailsDisclaimer"
                        :owner-status="ownerStatus"
                        :bond-options="bondOptions"
                        :completed="!screenIndexShow(componentIndexes.residence)"
                        @fail="revertIndex"
                        @success="submitResidence">
                    </checkout-residence>
                </accordion>
                <accordion v-if="isIndividual"
                    title="Employment Details"
                    type="right-down"
                    class-name="accordion-light"
                    step-code="credit-app-employment-history"
                    id="employment-history"
                    accordion-group-name="AccordionGroup"
                    :show="screenIndexShow(componentIndexes[indexHelper.employmentHistory])"
                >
                    <checkout-employment
                        :company-types="companyTypes"
                        :countries="availableCountries"
                        :init-employment="employmentData"
                        :employment-min-years="employmentMinYears"
                        :employment-statuses="employmentStatuses"
                        :industry-type-options="industryTypeOptions"
                        :unemployed-status="unemployedStatus"
                        :retired-or-student-status="retiredOrStudentStatus"
                        @fail="revertIndex"
                        @success="submitEmployment">
                    </checkout-employment>
                </accordion>
                <accordion v-if="isIndividual"
                    title="Income & Expenses"
                    type="right-down"
                    class-name="accordion-light"
                    step-code="credit-app-income-and-expenses"
                    id="income-and-expenses"
                    accordion-group-name="CheckoutCreditAppAccordionGroup"
                    :show="screenIndexShow(componentIndexes[indexHelper.incomesExpenses])"
                >
                    <checkout-income-expenses
                        :income-data="incomeData"
                        @fail="revertIndex"
                        @success="submitIncomeExpenses">
                    </checkout-income-expenses>
                </accordion>
                <accordion
                    title="Bank Account Details"
                    type="right-down"
                    class-name="accordion-light"
                    step-code="credit-app-banking-details"
                    id="banking-details"
                    accordion-group-name="AccordionGroup"
                    :show="screenIndexShow(componentIndexes[indexHelper.bankingDetails])"
                >
                    <checkout-bank-account-details
                        :account-type="accountType"
                        :bank-name="bankName"
                        :bank-options="bankOptions"
                        :branch-name="branchName"
                        :quote-data="quoteData"
                        :get-branch-url="getBranchUrl"
                        @fail="revertIndex"
                        @success="submitBankAccount">
                    </checkout-bank-account-details>
                </accordion>
                <accordion
                    title="Financial Communication Options"
                    type="right-down"
                    class-name="accordion-light"
                    step-code="credit-app-communication-preferences"
                    id="communication-preferences"
                    accordion-group-name="AccordionGroup"
                    :show="screenIndexShow(componentIndexes[indexHelper.communicationPreferences])"
                >
                    <checkout-communication-preferences
                        :init-communication-details="communicationPreferencesData"
                        @fail="revertIndex"
                        @success="submitCommunicationPreferences">
                    </checkout-communication-preferences>
                </accordion>
            </accordion-group>
        </template>
    </div>
</template>
<script>
    import VueValidator from 'vue-validator';
    Vue.use(VueValidator);

    import AccordionGroup from 'motorrad/components/Checkout/YourDetails/AccordionGroup';
    import Accordion from 'motorrad/components/Checkout/YourDetails/Accordion';
    import CheckoutResidence from 'motorrad/components/Checkout/YourDetails/Residence';
    import CheckoutEmployment from 'motorrad/components/Checkout/YourDetails/Employment';
    import CheckoutAdditionalInfo from 'motorrad/components/Checkout/YourDetails/AdditionalInfo';
    import YourDetailsBusiness from 'core/components/Checkout/YourDetails/Business';
    import CheckoutTermsAndConditions from 'motorrad/components/Checkout/YourDetails/TermsAndConditions';
    import CheckoutDeposit from 'motorrad/components/Checkout/YourDetails/Deposit';
    import CheckoutPersonal from 'motorrad/components/Checkout/YourDetails/PersonalDetails';
    import CheckoutIncomeExpenses from 'motorrad/components/Checkout/YourDetails/IncomeExpenses';
    import CheckoutBankAccountDetails from 'motorrad/components/Checkout/YourDetails/BankAccountDetails';
    import CheckoutCommunicationPreferences from 'motorrad/components/Checkout/YourDetails/CommunicationPreferences';
    import Modal from 'core/components/Elements/Modal';
    import OtpPopup from 'motorrad/components/Checkout/YourDetails/OtpPopup';

    export default Vue.extend({
        props: {
            stepCode: {
                required: false,
                type: String
            },
            dateParts: {
                required: false,
                type: Object
            },

            defaultCountry: {
                required: false,
                type: String
            },

            ownershipOptions: {
                required: true,
                type: Object
            },

            accommodationTypes: {
                required: true,
                type: Object
            },

            bondOptions: {
                type: Object,
                required: false
            },

            employmentStatuses: {
                required: true,
                type: Object
            },

            industryTypeOptions: {
                required: false,
                type: Object
            },

            companyTypes: {
                required: true,
                type: Array
            },

            maritalStatuses: {
                required: true,
                type: Object
            },

            availableCountries: {
                required: true,
                type: Array
            },

            detailsFormUrl: {
                required: true,
                type: String
            },

            bankFormUrl: {
                required: true,
                type: String
            },

            companyFormUrl: {
                required: true,
                type: String
            },

            residenceFormUrl: {
                required: true,
                type: String
            },

            employmentFormUrl: {
                required: true,
                type: String
            },

            depositFormUrl: {
                required: true,
                type: String
            },

            residenceAddressVisibility: {
                required: false,
                type: Number
            },

            employmentAddressVisibility: {
                required: false,
                type: Number
            },

            billingAddress: {
                required: false,
                type: Object
            },

            quoteData: {
                required: false,
                type: Object
            },

            customerData: {
                required: false,
                type: Object
            },

            residenceMinYears: {
                required: true,
                type: Number
            },

            residenceRepeats: {
                required: false,
                type: Number,
                default: 1
            },

            residenceMonthAmount: {
                required: false,
                type: Number,
                default: 0
            },

            initialResidenceMonthAmount: {
                required: false,
                type: Number
            },

            residenceData: {
                required: true,
                type: Object
            },

            employmentData: {
                required: true,
                type: Object
            },

            communicationPreferencesData: {
                required: true,
                type: Object
            },

            getAddressUrl: {
                required: true,
                type: String
            },

            retiredStatus: {
                required: false,
                type: Boolean,
                default: false
            },

            employmentVisible: {
                required: false,
                type: Boolean,
                default: false
            },

            additionalDetailsVisible: {
                required: false,
                type: Boolean,
                default: false
            },

            topText: {
                required: false,
                type: String,
                default: false
            },

            gdprDetailsDisclaimer: {
                required: false,
                type: String,
                default: ''
            },

            genderOptions: {
                required: true,
                type: Object
            },

            ethnicOptions: {
                required: true,
                type: Object
            },

            preferredLanguageOptions: {
                required: true,
                type: Object
            },

            marriageTypeOptions: {
                required: false,
                type: Object
            },

            spouseIdTypeOptions: {
                required: true,
                type: Object
            },

            marriedLivingApartStatus: {
                required: true,
                type: Array
            },

            maritalContractTypeRequiresSpouseDetails: {
                required: true,
                type: Array
            },

            zaIdType: {
                required: false,
                type: String,
                default: 'CIDSA'
            },

            ownerStatus: {
                required: true,
                type: String
            },

            bondStatus: {
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
            },

            accountType: {
                type: Object,
                required: true
            },

            bankName: {
                type: Array,
                required: true
            },

            branchName: {
                type: Array,
                required: true
            },

            depositQuoteData: {
                type: Object,
                required: true
            },

            depositSource: {
                type: Object,
                required: true
            },

            incomeFormUrl: {
                required: true,
                type: String
            },

            incomeData: {
                required: true,
                type: Object
            },

            getBranchUrl: {
                required: true,
                type: String
            },

            bankOptions: {
                required: true,
                type: Array
            },

            creditAppData: {
                required: true,
                type: Object
            },
        },

        data() {
            return {
                businessCustomerComponents: [
                    'credit-app-terms-and-conditions',
                    'credit-app-deposit',
                    'credit-app-banking-details',
                    'credit-app-communication-preferences'
                ],
                individualCustomerComponents: [
                    'credit-app-terms-and-conditions',
                    'credit-app-deposit',
                    'credit-app-personal-details',
                    'credit-app-residency',
                    'credit-app-employment-history',
                    'credit-app-income-and-expenses',
                    'credit-app-banking-details',
                    'credit-app-communication-preferences'
                ],
                individualCustomerIndex: {
                    terms: 0,
                    deposit: 1,
                    personalDetails: 2,
                    residency: 3,
                    employmentHistory: 4,
                    incomesExpenses: 5,
                    bankingDetails: 6,
                    communicationPreferences: 7
                },
                businessCustomerIndex: {
                    terms: 0,
                    deposit: 1,
                    bankingDetails: 2,
                    communicationPreferences: 3
                },
                disabledAccordions: [],
                ajaxLoading: false,
                financeOptionsIsBusiness: 0,
                activeComponentIndex: -1,
                showOtpPopup: false,
                otpData: {
                    refreshed: false,
                    failAttempt: false,
                    failResponse: false,
                    retryAttempts: this.creditAppData.retryAttempts,
                    response: false
                },
                skipOtpPopup: this.creditAppData.skipOtpPopup
            }
        },

        computed: {
            /**
             * @returns the currently active step.
             */
            activeStep() {
                return this.componentIndexes[this.activeComponentIndex];
            },

            /**
             * effectively an enumeration of current accordions on the page depends on individual/business customer type.
             * @returns object
             */
            indexHelper() {
                return this.isIndividual ? this.individualCustomerIndex : this.businessCustomerIndex;
            },

            /**
             * @returns {Array}
             */
            componentIndexes() {
                return this.isIndividual ? this.individualCustomerComponents : this.businessCustomerComponents;
            },

            /**
             * @returns the selected customer type.
             */
            customerType() {
                return this.$store.state.general.businessPicker;
            },

            /**
             * @returns isIndividual boolean value.
             */
            isIndividual() {
                return this.customerType === 'individual';
            }
        },

        methods: {
            submitAdditionalInfo(data) {
                if (data) {
                    this.ajaxLoading = true;
                    this.$http({
                        url: this.detailsFormUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data
                    }).then(this.submitDetailsSuccess, this.submitDetailsFail);
                }
            },

            /**
             * This specific accordion should be disabled - not shown skipped.
             * @param {Number} index, index of the accordion to be disabled.
             */
            disableAccordion(index) {
                if (!this.disabledAccordions.includes(index)) {
                    this.disabledAccordions.push(index);
                }
            },

            /**
             * moves the screen index to the appropriate index.
             * @param {Number} previousIndex, previous index to increment from.
             */
            incrementScreenIndex(previousIndex) {
                this.activeComponentIndex = previousIndex + 1;
                if (this.disabledAccordions.includes(this.activeComponentIndex)) {
                    this.incrementScreenIndex(this.activeComponentIndex);
                }
            },

            /**
             * This method will decrement the activeComponent index
             * until the current index is not contained within the
             * disabledAccordions.
             */
            revertIndex() {
                this.activeComponentIndex -= 1;
                if (this.disabledAccordions.includes(this.activeComponentIndex)) {
                    this.revertIndex();
                }
            },

            /**
             * finished, dispatch to the container.
             * @return void
             */
            submitDetailsSuccess() {
                this.ajaxLoading = false;
                this.$dispatch('CheckoutAccordionGroup::nextStep', this.stepCode);
            },

            submitDetailsFail(data) {
                console.error(data);
            },

            submitEmployment(data) {
                if (data) {
                    this.ajaxLoading = true;
                    this.$http({
                        url: this.employmentFormUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data
                    }).then(this.submitEmploymentSuccess, this.submitEmploymentFail);
                }
            },

            submitEmploymentSuccess(data) {
                this.ajaxLoading = false;
                this.incrementScreenIndex(this.indexHelper.employmentHistory);
            },

            submitEmploymentFail(data) {
                this.ajaxLoading = false;
                console.error(data);
            },

            submitTerms() {
                if (this.skipOtpPopup) {
                    this.incrementScreenIndex(this.indexHelper.terms);
                    return;
                }

                this.ajaxLoading = true;
                this.otpData.refreshed = false;
                this.otpData.retryAttempts = this.creditAppData.totalAttempts;
                this.$http({
                    url: this.creditAppData.getUserInfoUrl,
                    method: 'POST',
                    emulateJSON: true
                }).then(this.submitTermsSuccess, this.submitTermsFail);
            },

            submitTermsSuccess(resp) {
                this.ajaxLoading = false;
                let showOtpPopup = false;
                const data = resp.data;

                if (!data) {
                    console.error('There was an error with your request');
                } else if (data && data.error) {
                    console.error(data.error);
                }

                if (
                    !data.authentication ||
                    !data.authentication.isClientPresent ||
                    data.authentication.retryAttempts === 0
                ) {
                    this.incrementScreenIndex(this.indexHelper.terms);
                } else {
                    if (this.otpData.response !== false) {
                        this.otpData.refreshed = true;
                    }
                    this.otpData.response = data;
                    showOtpPopup = true;
                }

                this.showOtpPopup = showOtpPopup;
            },

            submitTermsFail(resp) {
                if (resp.data) {
                    console.error('There was an error with your request', resp.data);
                }

                this.ajaxLoading = false;
            },

            clearOtpErrors() {
                this.otpData.failAttempt = false;
                this.otpData.failResponse = false;
            },

            submitOtpCode(code) {
                this.ajaxLoading = true;
                this.$http({
                    url: this.creditAppData.submitCodeUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        otp_code: code
                    }
                }).then(this.submitOtpCodeSuccess, this.submitOtpCodeFail);
            },

            submitOtpCodeSuccess(resp) {
                const data = resp.data;
                this.ajaxLoading = false;
                this.otpData.refreshed = false;

                if (data.success) {
                    this.showOtpPopup = false;
                    this.$emit('CheckoutCreditAppAccordionGroup::Success', data.creditAppData);
                } else {
                    this.otpData.failAttempt = true;
                    this.otpData.retryAttempts = data.retryAttempts;
                }
            },

            submitOtpCodeFail(resp) {
                console.error(resp);
                this.ajaxLoading = false;
                this.otpData.failResponse = true;
            },

            submitDeposit(data) {
                if (data) {
                    this.ajaxLoading = true;
                    this.$http({
                        url: this.depositFormUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data
                    }).then(this.submitDepositSuccess, this.submitDepositFail);
                }
                this.incrementScreenIndex(this.indexHelper.deposit);
            },

            submitDepositSuccess(data) {
                data = data.data;

                if (!data) {
                    console.error('There was an error with your request');
                } else if (data && data.error) {
                    console.error(data.message);
                }

                this.ajaxLoading = false;
            },

            submitDepositFail(resp) {
                console.error(resp);
            },

            /**
             * Ajax call to save form data in db
             *
             * @param data form data
             */
            submitPersonal(data) {
                if (data) {
                    this.ajaxLoading = true;
                    this.$http({
                        url: this.detailsFormUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data
                    }).then(this.submitPersonalSuccess, this.submitPersonalFail);
                }
            },

            /**
             * display console message and stop spinner, increment index
             *
             * @param resp response from be
             */
            submitPersonalSuccess(resp) {
                const data = resp.data;

                if (!data) {
                    console.error('There was an error with your request');
                } else if (data && data.error) {
                    console.error(data.message);
                }

                this.ajaxLoading = false;
                this.incrementScreenIndex(this.indexHelper.personalDetails);
            },

            /**
             * display console message and stop spinner
             *
             * @param resp response from be
             */
            submitPersonalFail(resp) {
                if (resp.data) {
                    console.error('There was an error with your request', resp.data);
                }

                this.ajaxLoading = false;
            },

            /**
             * Ajax call to save form data in db
             *
             * @param data form data
             */
            submitIncomeExpenses(data) {
                if (data) {
                    this.ajaxLoading = true;
                    this.$http({
                        url: this.incomeFormUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data
                    }).then(this.submitIncomeExpensesSuccess, this.submitIncomeExpensesFail);
                }
            },

            submitIncomeExpensesSuccess(resp) {
                const data = resp.data;

                if (!data) {
                    console.error('There was an error with your request');
                } else if (data && data.error) {
                    console.error(data.message);
                }

                this.ajaxLoading = false;
                this.incrementScreenIndex(this.indexHelper.incomesExpenses);
            },

            submitIncomeExpensesFail(resp) {
                const data = resp.data;

                console.error('There was an error with your request', data);

                this.ajaxLoading = false;
            },

            submitBankAccount(data) {
                if (data) {
                    this.ajaxLoading = true;
                    this.$http({
                        url: this.bankFormUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data
                    }).then(this.submitBankAccountSuccess, this.submitBankAccountFail);
                }
            },
            submitBankAccountSuccess() {
                this.ajaxLoading = false;
                this.incrementScreenIndex(this.indexHelper.bankingDetails);
            },

            submitBankAccountFail(data) {
                console.error(data);
                this.ajaxLoading = false;
            },

            submitCommunicationPreferences(data) {
                if (data) {
                    this.ajaxLoading = true;
                    this.$http({
                        url: this.detailsFormUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data
                    }).then(this.submitCommunicationPreferencesSuccess, this.submitCommunicationPreferencesFail);
                }
                this.submitDetailsSuccess(this.indexHelper.communicationPreferences);
            },

            submitCommunicationPreferencesSuccess(data) {
                data = data.data;
                if (!data) {
                    console.error('There was an error with your request');
                } else if (data && data.error) {
                    console.error(data.message);
                }
                this.ajaxLoading = false;
                this.$dispatch('CheckoutAccordionGroup::nextStep', this.$parent.stepCode);
            },

            submitCommunicationPreferencesFail(resp) {
                console.error(resp);
            },

            submitResidence(data) {
                if (data) {
                    this.ajaxLoading = true;
                    this.$http({
                        url: this.residenceFormUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data
                    }).then(this.submitResidenceSuccess, this.submitResidenceFail);
                }
            },

            submitResidenceSuccess(data) {
                data = data.data;

                if (!data) {
                    console.error('There was an error with your request');
                }

                if (data && data.error) {
                    console.error(data.message);
                }

                this.ajaxLoading = false;
                this.incrementScreenIndex(this.indexHelper.residency);
            },

            submitResidenceFail(resp) {
                console.error(resp);
            },

            getAddress() {
                this.ajaxLoading = true;
                this.$http({
                    url: this.getAddressUrl,
                    method: 'GET',
                    emulateJSON: true
                }).then(this.getAddressSuccess, this.getAddressFail);
            },

            getAddressSuccess(resp) {
                this.ajaxLoading = false;
                this.billingAddress = resp.data;
            },

            getAddressFail(error) {
                if (error) {
                    return;
                }

                this.ajaxLoading = false;
            },

            /**
             * provides a means for the container to create a status for this part of the accordion
             * @returns {{action: *, message: *}}
             */
            getStatusBar() {
                const statusAction = 'edit';
                const statusBar = 'Your Details';

                return { message: statusBar, action: statusAction };
            },

            /**
             * used to identify if this part of the accordion should be shown.
             * @param val
             * @returns {boolean}
             */
            screenIndexShow(val) {
                return this.componentIndexes[this.activeComponentIndex] === val;
            },

            /**
             * get the index of the given value
             * @param val step code to find
             * @returns {number} index of the given step code.
             */
            getIndex(val) {
                let toReturn = 0;
                this.componentIndexes.forEach((item, index) => {
                    if (item === val) {
                        toReturn = index;
                    }
                });

                return toReturn;
            },

            /**
             * Checks finance option to skip the credit application if Cash if selected
             * @param accordion
             */
            onAccordionShow(accordion) {
                if (this.$root.$refs.financeQuote.payInFullPayment.find(payment => payment.group_id === this.$root.$refs.financeQuote.activePayment.group_id) !== undefined) {
                    this.$dispatch('CheckoutAccordionGroup::nextStep', this.stepCode, this.stepCode);
                    accordion.hidden = true;
                } else {
                    this.getAddress();
                    accordion.hidden = false;
                    this.activeComponentIndex = 0;
                    this.disabledAccordions = [];
                }
            }
        },

        ready() {
            this.getAddress();
            this.activeComponentIndex += 1;
        },

        events: {
            'CheckoutCreditAppAccordionGroup::showAccordion'(stepCode) {
                this.activeComponentIndex = this.getIndex(stepCode);
            },

            'CheckoutCreditAppAccordionGroup::Cancel'() {
                this.clearOtpErrors();
                this.showOtpPopup = false;
                this.otpData.response = false;
                this.incrementScreenIndex(this.indexHelper.terms);
            },

            'CheckoutCreditAppAccordionGroup::Submit'(code) {
                this.clearOtpErrors();
                this.submitOtpCode(code);
            },

            'CheckoutCreditAppAccordionGroup::Success'(creditAppData) {
                this.clearOtpErrors();
                this.showOtpPopup = false;
                this.skipOtpPopup = true;
                this.$broadcast('YourDetails::creditAppUpdate', creditAppData);
                this.incrementScreenIndex(this.indexHelper.terms);
            },

            'CheckoutCreditAppAccordionGroup::RefreshOTP'() {
                this.clearOtpErrors();
                this.submitTerms();
            }
        },

        components: {
            Accordion,
            AccordionGroup,
            CheckoutTermsAndConditions,
            CheckoutDeposit,
            CheckoutPersonal,
            CheckoutResidence,
            CheckoutEmployment,
            CheckoutIncomeExpenses,
            CheckoutBankAccountDetails,
            CheckoutCommunicationPreferences,
            CheckoutAdditionalInfo,
            Modal,
            OtpPopup,
            YourDetailsBusiness
        }
    });
</script>
