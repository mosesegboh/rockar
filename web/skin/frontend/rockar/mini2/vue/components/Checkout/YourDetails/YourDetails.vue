<script>
    import appCheckoutYourDetails from 'dsp2/components/Checkout/YourDetails/YourDetails.vue';
    import CheckoutIncomeExpenses from 'mini2/components/Checkout/YourDetails/IncomeExpenses.vue';
    import CheckoutCommunicationPreferences from 'mini2/components/Checkout/YourDetails/CommunicationPreferences.vue';

    export default appCheckoutYourDetails.extend({
        computed: {
            stepHeaderText() {
                return 'Apply For Finance';
            },

            detailsTitleText() {
                return 'Don’t let financial worries ruin the fun. Apply for MINI Finance right here.';
            },

            communicationsTitleText() {
                return 'Communication Preferences';
            }
        },

        components: {
            CheckoutIncomeExpenses,
            CheckoutCommunicationPreferences
        }
    });
</script>
