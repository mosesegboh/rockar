<template>
    <div class="mobile-only finance-quote-wrapper">
        <div class="finance-paymenents-wrapper">
            <div class="finance-payments" v-if="!getIsHire">
                <div class="payment">
                    <p class="payment-amount">{{ getRockarPrice | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Offer price' | translate }}</p>
                </div>
                <div class="payment" v-if="getSaveOffRrp">
                    <p class="payment-amount">{{ getSaveOffRrp | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'off RRP' | translate }}</p>
                </div>
                <div class="payment" v-if="getMonthlyPrice != 0">
                    <p class="payment-amount">{{ getMonthlyPrice | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'per month' | translate }}</p>
                </div>
            </div>
            <div class="finance-payments" v-if="getIsHire">
                <div class="payment">
                    <p class="payment-amount">{{ getCashDeposit | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Initial payment' | translate }}</p>
                </div>
                <div class="payment" v-if="monthlyPrice != 0">
                    <p class="payment-amount">{{ getMonthlyPrice | numberFormat '0,0' true }}</p>
                    <p class="payment-label">{{ 'Monthly rental' | translate }}</p>
                </div>
            </div>
            <div class="finance-delivery" v-if="getLeadTime">
                <p class="lead-time">{{ getLeadTime | convertNCR }}</p>
            </div>
        </div>
    </div>
</template>

<script>
    import coreTopFinanceQuote from 'core/components/Configurator/TopFinanceQuote';
    export default coreTopFinanceQuote.extend();
</script>
