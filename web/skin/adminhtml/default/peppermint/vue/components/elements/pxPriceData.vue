<template>
    <table
        cellspacing="0"
        class="form-list"
    >
        <tbody>
            <tr>
                <td class="label">
                    Part Exchange Value:
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
                        :class="['input-text', 'required-entry', activeStep !== 'confirmation' ? 'disabled' : '']"
                        :disabled="activeStep !== 'confirmation'"
                    >
                    <div
                        v-if="isNegativeEquity"
                        class="validation-advice"
                    >
                        You have negative equity on your car.
                    </div>
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
    },

    data() {
        return {
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
            const outstandingFinance = parseFloat(this.outstandingFinanceValue ? this.outstandingFinanceValue.replace(/,/g, '').replace(replace, '') : 0);
            const pxValue = parseFloat(this.value ? this.value.replace(/,/g, '').replace(replace, '') : 0);
            const ruleIsApplicable = (outstandingFinance > 0 && pxValue > 0);

            return ruleIsApplicable && outstandingFinance >= pxValue;
        },
    },

    methods: {
        savePX() {
            if (this.saveButtonEnabled) {
                const replace = new RegExp(this.currencySymbol, 'g');
                window.EventsBus.$emit('Reorder::updateQuote', {
                    part_exchange: {
                        outstanding_finance: parseFloat(
                            this.outstandingFinanceValue ? this.outstandingFinanceValue.replace(/,/g, '').replace(replace, '') : 0
                        ),
                        outstanding_finance_settlement: this.negativeEquityPayment,
                        partexchange_value: parseFloat(
                            this.value ? this.value.replace(/,/g, '').replace(/[$£]/, '') : 0
                        )
                    }
                });

                this.editMode = false;
            }
        },
    }

};
</script>
