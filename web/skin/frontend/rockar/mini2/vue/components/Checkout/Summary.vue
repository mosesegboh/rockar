<script>
import appCheckoutSummary from 'dsp2/components/Checkout/Summary';

export default appCheckoutSummary.extend({
    computed: {
        extraFeatureTitle() {
            return this.translateString('included Extras');
        },

        standardFeaturesTitle() {
            return this.translateString('included Standard features');
        }
    }
});
</script>