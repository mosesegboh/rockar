<script>
    import appCheckoutFinanceOverlay from 'dsp2/components/Checkout/FinanceOverlay';

    export default appCheckoutFinanceOverlay.extend({
        computed: {
            stepHeader() {
                return this.translateString('Choose Your Payment Option');
            },

            numberOfInstalments() {
                return this.translateString('Number of instalments');
            },

            contractEndMileage() {
                return this.translateString('Contract mileage limit');
            }
        }
    });
</script>
