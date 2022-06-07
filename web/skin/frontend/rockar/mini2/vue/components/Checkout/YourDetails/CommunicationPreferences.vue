<script>
    import CheckoutCommunicationPreferences from 'dsp2/components/Checkout/YourDetails/CommunicationPreferences';

    export default CheckoutCommunicationPreferences.extend({
        computed: {
            preferredCommunicationText() {
                return 'Preferred Communication Method:';
            },

            contractDocumentationText() {
                return 'How would you like to receive your contract?';
            },

            frequencyText() {
                return 'Statement Frequency:';
            }
        }
    })
</script>
