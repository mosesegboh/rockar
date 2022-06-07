<template>
    <table
        cellspacing="0"
        class="form-list"
    >
        <tbody>
            <tr>
                <td class="label">
                    Original Part Exchange Value:
                </td>
                <td class="value">
                    <strong>{{ value }}</strong>
                </td>
            </tr>
            <tr>
                <td class="label">
                    Outstanding Finance:
                </td>
                <td class="value">
                    <input
                        id="outstanding_finance"
                        v-model="outstandingFinanceValue"
                        name="outstanding_finance"
                        title="Outstanding Finance"
                        type="text"
                        :class="['input-text', 'required-entry', !enableOutstandingFinances ? 'disabled' : '']"
                        :disabled="!enableOutstandingFinances"
                    >
                    <div
                        v-if="isNegativeEquity"
                        class="validation-advice"
                    >
                        You have negative equity on your car.
                    </div>
                </td>
            </tr>
            <tr>
                <td class="label">
                    Part Exchange Value:
                </td>
                <td class="value">
                    <input
                        id="px_value"
                        v-model="manualPartExchangeValue"
                        name="px_value"
                        title="Part Exchange Value"
                        type="text"
                        :class="['input-text']"
                    >
                    <button
                        title="Use original price"
                        type="button"
                        :class="['save', saveButtonEnabled && value ? 'scalable' : 'disabled']"
                        :disabled="!saveButtonEnabled && value"
                        @click="savePX('original')"
                    >
                        <span><span><span>Use original price</span></span></span>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>
<script>
export default {
    props: {
        activeStep: {
            required: false,
            type: String,
            default: '',
        },
        outstandingFinance: {
            required: false,
            type: [String, Number],
            default: null,
        },
        value: {
            required: false,
            type: [String, Number],
            default: null,
        },
        saveButtonEnabled: {
            required: false,
            type: Boolean,
            default: false,
        },
        apiLessFormDataFilled: {
            required: false,
            type: Boolean,
            default: false,
        },
        pxData: {
            required: false,
            type: Object,
            default: null,
        },
        allowNegativeEquity: {
            required: false,
            type: Boolean,
            default: false,
        },
        negativeEquityPayment: {
            required: false,
            type: Number,
            default: null,
        },
        manualValue: {
            required: false,
            type: [Number, String],
            default: null,
        },
    },

    data() {
        return {
            manualPartExchangeValue: this.manualValue,
            outstandingFinanceValue: this.outstandingFinance,
        };
    },

    computed: {
        currencySymbol() {
            return this.$store.state.global.currencySymbol;
        },

        isNegativeEquity() {
            if (this.allowNegativeEquity) {
                return false;
            }
            const replace = new RegExp(this.currencySymbol, 'g');
            const outstandingFinance = parseFloat(
                this.outstandingFinanceValue
                    ? this.outstandingFinanceValue.replace(/,/g, '').replace(/\s+/g, '').replace(replace, '')
                    : 0
            );
            const pxValue = this.manualPartExchangeValue
                ? parseFloat(this.manualPartExchangeValue)
                : parseFloat(this.value ? this.value.replace(/,/g, '').replace(replace, '') : 0);
            const ruleIsApplicable = (outstandingFinance > 0 && pxValue > 0);

            return ruleIsApplicable && outstandingFinance >= pxValue;
        },

        enableOutstandingFinances() {
            return this.activeStep === 'confirmation' || this.apiLessFormDataFilled;
        }
    },

    watch: {
        manualValue(newVal) {
            this.manualPartExchangeValue = newVal;
        },

        outstandingFinance(newVal) {
            this.outstandingFinanceValue = newVal;
        }
    },

    methods: {
        savePX(type = false) {
            if (this.saveButtonEnabled) {
                const replace = new RegExp(this.currencySymbol, 'g');
                const data = this.prepareSaveData();

                if (type !== 'original') {
                    data.manual_value = parseFloat(
                        this.manualPartExchangeValue
                            ? this.manualPartExchangeValue
                            : 0
                    );
                }

                data.bidding_id = this.pxData.biddingId;

                data.outstanding_finance = parseFloat(
                    this.outstandingFinanceValue
                        ? this.outstandingFinanceValue.replace(/,/g, '').replace(/\s+/g, '').replace(replace, '')
                        : 0
                );
                data.outstanding_finance_settlement = this.negativeEquityPayment;

                window.EventsBus.$emit('Reorder::updateQuote', {
                    part_exchange: data
                });

                this.editMode = false;
            }
        },

        prepareSaveData() {
            if (!this.apiLessFormDataFilled) {
                return {};
            }

            const data = this.pxData;

            if (data.apiLessFormData) {
                data.manual_data = {};
                Object.keys(data.apiLessFormData).forEach((key) => {
                    const currentValue = data.apiLessFormData[key].value;
                    data.manual_data[key] = currentValue;
                    data[key] = currentValue;
                });
                data.apiless = true;
            }

            data.additionalInfo = [];
            data.checkboxes.forEach((checkbox) => {
                if (checkbox.checked !== false) {
                    data.additionalInfo.push(checkbox);
                }
            });

            this.$delete(data, 'apiLessFormData');
            this.$delete(data, 'checkboxes');

            return data;
        },
    }
};
</script>
