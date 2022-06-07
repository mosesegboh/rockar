<template>
    <div class="part-exchange-negative-equity" v-if="hasNegativeEquity">
        <div class="row">
            <div class="col-12">
                <h3>Trade in your current vehicle</h3>
                <h3 class="negative-equity-value">
                    Trade-in shortfall:
                    {{ computeNegativeEquity | numberFormat '0,0.00' true }}
                </h3>
            </div>
            <div class="col-12" v-if="!isPayInFull">
                <p>Based on the the information provided, you are required to settle the net amount due on your Trade-in
                    vehicle.
                    This is the difference between the trade-in value and the finance amount still owing on the vehicle.
                    You can continue with your journey by selecting one of the three options below.
                </p>
            </div>
            <div class="col-12" v-if="isPayInFull">
                <p>Based on the the information provided, you are required to settle the net amount due on your Trade-in
                    vehicle.
                    This is the difference between the trade-in value and the finance amount still owing on the vehicle.
                    You can continue with your journey, your trade-in shortfall will be settled in a single lump sum
                    payment.
                </p>
            </div>
        </div>
        <div class="row" v-if="!isPayInFull">
            <div class="col-4">
                <div class="row">
                    <div class="col-12">
                        <button @click="settleOutstandingFinance(TRADEIN_SHORTFALL_MONTHLY_PAYMENT)">
                            Add to monthly payment
                        </button>
                    </div>
                    <div class="col-12">
                        <p>Pay-off your trade-in shortfall on a monthly basis without the requirement of a lump sum cash
                            payment.</p>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="row">
                    <div class="col-12">
                        <button @click="settleOutstandingFinance(tradeinShortfallOnecashPayment)">
                            Once-off payment
                        </button>
                    </div>
                    <div class="col-12">
                        <p>Settle your trade-in shortfall in a single lump sum payment.</p>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="row">
                    <div class="col-12">
                        <button @click="settleOutstandingFinance(tradeinShortfallNone)">
                            Remove trade-in vehicle.
                        </button>
                    </div>
                    <div class="col-12">
                        <p>Keep your existing vehicle or trade-in your vehicle privately.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        data() {
            return {
                negativeEquity: 0,
                outstandingFinanceSettlement: 0,
                outstandingFinance: this.$parent.outstandingFinance,
                partExchangeValue: this.$parent.partExchangeValue,
                tradeinShortfallNone: 0, // Not set / None.
                TRADEIN_SHORTFALL_MONTHLY_PAYMENT: 1, // Pay-off trade-in shortfall on a monthly basis.
                tradeinShortfallOnecashPayment: 2 // Settle trade-in shortfall in a single lump sum payment.
            }
        },

        computed: {
            PartExchangeFilter() {
                return this.$root.$refs.partExchangeFilter;
            },

            FinanceFilter() {
                return this.$root.$refs.financeFilter;
            },

            computeNegativeEquity() {
                this.negativeEquity = 0;

                if (this.hasNegativeEquity) {
                    this.negativeEquity = this.$parent.partExchangeValue - this.$parent.outstandingFinance;
                    this.negativeEquity += this.payInFull ? 0 : this.FinanceFilter.initialPaymentAmount;
                }

                if (this.isPayInFull) {
                    if (this.hasNegativeEquity) {
                        this.settleOutstandingFinance(this.tradeinShortfallOnecashPayment, false);
                    } else {
                        this.settleOutstandingFinance(this.tradeinShortfallNone, false);
                    }
                }

                return this.negativeEquity;
            },

            hasNegativeEquity() {
                return this.$parent.outstandingFinance > (
                    this.$parent.partExchangeValue +
                    this.FinanceFilter.initialPaymentAmount
                );
            },

            isPayInFull() {
                return this.$root.$refs.financeFilter.isPayInFull;
            }
        },

        methods: {
            settleOutstandingFinance(settlementChoice = 0, nextStep = true) {
                this.$store.commit('setPXOutstandingFinanceSettlement', settlementChoice);
                this.$store.commit('setPXValuationOutstandingFinance', this.$parent.outstandingFinance);
                EventsBus.$emit('PartExchangeFilter::outstandingFinanceSettlement', settlementChoice);

                if (settlementChoice === this.tradeinShortfallNone) {
                    EventsBus.$emit('PartExchangeFilter::PXWithOutPX', null);
                }

                if (nextStep) {
                    this.$parent.saveValuation();
                }
            }
        }
    })
</script>
