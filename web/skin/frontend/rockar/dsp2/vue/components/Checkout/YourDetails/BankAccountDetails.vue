<template>
    <div class="finance-credit">
        <validator name="banking-details">
            <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
            <div class="row-grid bank-account-text">
                <p>{{ `We take the privacy and protection of your personal information very seriously
                    and will only process your personal information in accordance with the
                    current South African data privacy laws and the terms of our ` | translate }}
                    <a class="dsp2-link" target="_blank" href="https://www.bmw.co.za/en/footer/metanavigation/legal-disclaimer-pool/privacy-statement.html">
                        {{ 'Privacy Statement' | translate }}
                    </a>.
                </p>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div :class="cssClassForRequiredLabel">{{ 'Account Holder Name' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="formData.account_holder | lettersOnly"
                                :value="quoteData.name_of_bank_account"
                                v-validate:account-holder-name="{ required: { rule: true, initial: 'off' }}"
                            >
                        </div>
                    </div>
                    <div class="validation-error-msg" v-if="$bankingDetails.accountHolderName.required">
                        {{ 'This field is required.' | translate }}
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div :class="cssClassForRequiredLabel">{{ 'Bank Name' | translate }}</div></label>
                    <div class="select-wrapper" :class="{'selected': activeFinanceGroupId > 0}">
                        <app-select
                            :options="createSelect(false, bankOptions, 'text', 'value')"
                            @select="setBankName"
                            :init-selected="quoteData.bank_name"
                            :valid="$bankingDetails.bankName.valid"
                            :show-items="5"
                            title="-"
                        ></app-select>
                        <input
                            type="hidden"
                            :value="quoteData.bank_name"
                            v-model="formData.bank_name"
                            v-validate:bank-name="{ required: { rule: true, initial: 'off' }}"
                        />
                        <div class="validation-error-msg" v-if="$bankingDetails.bankName.required">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>

            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div :class="cssClassForRequiredLabel">{{ 'Branch Name' | translate }}</div></label>
                    <div class="select-wrapper" :class="{ 'selected': activeFinanceGroupId > 0 }">
                        <app-select
                            :options="createSelect(false, branchName, 'text', 'value')"
                            @select="setBranchName"
                            v-model="formData.branch_select"
                            :disabled="checkIfLengthIsOne()"
                            :init-selected="formData.branch_option_selected"
                            title="-"
                        ></app-select>
                        <input
                            type="hidden"
                            :value="quoteData.branch_name"
                            v-model="formData.branch_name"
                            v-validate:branch-name="{ required: { rule: true, initial: 'off' }}"
                        />
                        <div class="validation-error-msg" v-if="$bankingDetails.branchName.required">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div :class="cssClassForRequiredLabel">{{ 'Branch Code' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                disabled
                                :value="quoteData.branch_code"
                                v-model="formData.branch_code"
                                initial="off"
                                v-validate:branch-code="{ required: { rule: true }}"
                            >
                        </div>
                        <div class="validation-error-msg" v-if="$bankingDetails.branchCode.required">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-grid">
                <div class="col-6">
                    <label class="hover-label"><div :class="cssClassForRequiredLabel">{{ 'Account Type' | translate }}</div></label>
                    <div class="select-wrapper" :class="{'selected': activeFinanceGroupId > 0}">
                        <app-select
                            :options="createSelect(true, accountType)"
                            @select="setAccountType"
                            :init-selected="quoteData.account_type_code"
                            :valid="$bankingDetails.accountType.valid"
                            title="-"
                        ></app-select>
                        <input
                            type="hidden"
                            :value="quoteData.account_type_code"
                            v-model="formData.account_type"
                            v-validate:account-type="{ required: { rule: true, initial: 'off' }}"
                        />
                        <div class="validation-error-msg" v-if="$bankingDetails.accountType.required">
                            {{ 'This field is required.' | translate }}
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="hover-label"><div :class="cssClassForRequiredLabel">{{ 'Account Number' | translate }}</div></label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                :value="quoteData.account_number"
                                v-model="formData.account_number | numbersOrEmpty"
                                v-validate:account-number="{ required: { rule: true, initial: 'off' }}"
                            >
                        </div>
                        <div class="validation-error-msg" v-if="$bankingDetails.accountNumber.required">
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
    import appSelect from 'core/components/Elements/Select';
    import yourDetailsHelpers from 'core/components/Checkout/YourDetails/helpers';
    import appMessages from 'core/components/Elements/Messages';
    import translateString from 'core/filters/Translate';
    import uiVariables from 'dsp2/components/Shared/UIVariables';
    import inputFilter from 'dsp2/components/Shared/InputFilters'

    Vue.use(VueValidator);

    export default Vue.extend({
        mixins: [
            yourDetailsHelpers,
            uiVariables,
            inputFilter
        ],

        props: {
            accountType: {
                type: Object,
                required: true
            },
            bankName: {
                type: Object,
                required: true
            },
            branchName: {
                type: [Object, Array],
                required: true
            },
            getBranchUrl: {
                required: false,
                type: String
            },
            bankOptions: {
                default: () => [],
                type: Array,
                required: true
            },
            quoteData: {
                required: false,
                type: Object
            }
        },

        data() {
            return {
                formData: {
                    account_holder: null,
                    bank_name: null,
                    branch_name: null,
                    account_type: null,
                    branch_code: null,
                    account_number: null,
                    branch_option_selected: null
                },
                ajaxLoading: false
            }
        },

        methods: {
            translateString,

            checkIfLengthIsOne() {
                return Object.keys(this.branchName).length === 1;
            },

            mapFormData() {
                return {
                    account_number: this.formData.account_number,
                    branch_code: this.formData.branch_code,
                    account_type_code: this.formData.account_type,
                    bank_name: this.formData.bank_name,
                    branch_name: this.formData.branch_name,
                    name_of_bank_account: this.formData.account_holder
                };
            },

            formSubmit() {
                this.$validate(false, () => {
                    if (this.$bankingDetails.valid) {
                        this.$emit('success', this.mapFormData());
                    }
                })
            },
            previousView() {
                this.$emit('fail');
            },

            setAccountType(val) {
                this.formData.account_type = val.value;
            },

            setBranchName(val) {
                this.formData.branch_name = val.title;
                this.formData.branch_code = val.value;
            },

            setBankName(val) {
                this.formData.bank_name = val.value;
                this.ajaxLoading = true;
                this.branchName = [];
                this.formData.branch_code = '';
                this.$http({
                    url: this.getBranchUrl,
                    params: {
                        bankCode: val.value
                    },
                    method: 'POST'
                }).then(this.getBranchSuccess).catch(this.getBranchFail);
            },

            getBranchSuccess(resp) {
                this.ajaxLoading = false;
                this.branchName = resp.data;
            },

            getBranchFail(data) {
                this.ajaxLoading = false;
                console.error(data);
            }
        },

        watch: {
            branchName: {
                deep: true,
                handler(newVal) {
                    if (newVal.length === 1) {
                        this.$nextTick(() => {
                            const keyBranch = newVal[0].value;
                            this.formData.branch_option_selected = keyBranch;
                            this.formData.branch_code = keyBranch;
                            this.formData.branch_name = newVal[0].text;
                        })
                    }
                }
            }
        },

        components: {
            appSelect,
            appMessages
        }
    })
</script>
