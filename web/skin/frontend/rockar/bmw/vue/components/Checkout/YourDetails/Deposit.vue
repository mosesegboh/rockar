<template>
    <div class="finance-credit">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <validator name="deposit">
            <div class="row">
                <div class="col-6">
                    <label class="side-label">{{ 'Deposit Amount' | translate }}</label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="getDisplayAmountForDepositField()"
                                class="currency"
                                disabled
                            />
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <label :class="getConditionalCssClass()">{{ 'Source of Deposit' | translate }}</label>
                    <div class="select-wrapper" :class="{'selected': activeFinanceGroupId > 0}">
                        <app-select
                            title="-"
                            :init-selected="formData.deposit.sourceOfDeposit"
                            :options="createSelect(true, depositSource)"
                            @select="selectDepositSource"
                            :disabled="depositIsZero"
                        ></app-select>
                        <input
                            type="hidden"
                            v-model="formData.deposit.sourceOfDeposit"
                            v-validate:source-of-deposit="{ requiredWhenGreaterZero : { rule: true, initial: 'off' }}"
                        />
                        <div class="validation-advice" v-if="!$deposit.sourceOfDeposit.valid">
                            {{ validationMessages.requiredField | translate }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" v-if="formData.deposit.sourceOfDeposit === 'SODOT' && !depositIsZero">
                <div class="col-6">
                    <label :class="cssClassForRequiredLabel">{{ 'Source of Deposit Description' | translate }}</label>
                    <div class="finance-filter-slider-input">
                        <div class="finance-filter-slider-input-wrapper">
                            <input
                                type="text"
                                v-model="formData.deposit.sourceDescription | lettersOnly"
                                v-validate:deposit-description="{ requiredWhenOther: { rule: true, initial: 'off' }}"
                            />
                        </div>
                        <div class="validation-advice" v-if="!$deposit.depositDescription.valid">
                            {{ requiredFieldErrorMessage | translate }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 padBottom">
                    <p class="accept-terms">
                        {{ 'BMW Financial Services (South Africa) (Pty) Ltd, ' +
                        'as a registered Financial Services Provider is mandated by the Financial Intelligence Centre (FIC) to take reasonable ' +
                        'measures to establish the source of funds from its customers as required by Section 21A of FIC Act. ' +
                        'As a result you are required to disclose the source of your deposit.' | translate }}
                    </p>
                </div>
            </div>
            <div class="row your-details-submit section-action-buttons">
                <div class="col-6">
                    <button class="button button-empty" @click="previousView">{{ 'Back' | translate }}</button>
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
    import yourDetailsHelpers from 'core/components/Checkout/YourDetails/helpers';
    import appMessages from 'core/components/Elements/Messages';
    import translateString from 'core/filters/Translate';
    import currentLocale from 'bmw/components/Shared/CurrentLocale';
    import uiVariables from 'bmw/components/Shared/UIVariables';
    import inputFilter from 'bmw/components/Shared/InputFilters';

    export default Vue.extend({
        mixins: [
            yourDetailsHelpers,
            currentLocale,
            uiVariables,
            inputFilter
        ],

        props: {
            initDepositDetails: {
                type: Object,
                required: true
            },

            depositSource: {
                type: Object,
                required: true
            },

            visible: {
                required: false,
                type: Boolean,
                default: false
            }
        },

        computed: {
            depositIsZero() {
                const financeVal = this.$root.$refs.financeQuote;
                if (financeVal) {
                    return parseInt(financeVal.financeParams.deposit) === 0;
                }

                return false;
            },

            financeVariables() {
                return this.$root.$refs.financeQuote.financeVariables;
            }
        },

        data() {
            return {
                formData: {
                    deposit: {
                        sourceOfDeposit: this.initDepositDetails.sourceOfDeposit || null,
                        sourceDescription: this.initDepositDetails.sourceDescription || null
                    }
                }
            }
        },

        methods: {
            translateString,

            mapFormData() {
                const obj = {};
                obj.amount = this.formData.deposit.amount;
                obj.sourceOfDeposit = this.formData.deposit.sourceOfDeposit;
                obj.sourceDescription = this.formData.deposit.sourceDescription;
                return obj;
            },

            formSubmit() {
                this.$validate(false, () => {
                    if (this.$deposit.valid) {
                        this.$emit('success', this.mapFormData());
                    }
                });
            },

            previousView() {
                this.$emit('fail');
            },

            selectDepositSource(val) {
                this.formData.deposit.sourceOfDeposit = val.value;
            },

            getDisplayAmountForDepositField() {
                let depositAmount = 0;

                if (this.financeVariables) {
                    const depositAmountElement = this.financeVariables.find(element => element.variable === 'customer_deposit');
                    depositAmount = depositAmountElement ? depositAmountElement.value : depositAmount;
                }

                return this.getNumeralFormatting(depositAmount).format();
            },

            getConditionalCssClass() {
                return this.getNumeralFormatting(this.getDisplayAmountForDepositField()).value() > 0 ? this.cssClassForRequiredLabel : 'side-label'
            }
        },

        ready() {
            Vue.validator('requiredWhenOther', (val) => {
                if (this.formData.deposit.sourceOfDeposit === 'SODOT') {
                    return val !== '';
                }

                return true;
            });
            Vue.validator('requiredWhenGreaterZero', (val) => {
                if (this.depositIsZero) {
                    return true;
                }

                return val !== '';
            });
        },

        watch: {
            visible: {
                immediate: true,
                handler(val) {
                    if (val && this.depositIsZero) {
                        this.$emit('disable');
                        this.$emit('success');
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
