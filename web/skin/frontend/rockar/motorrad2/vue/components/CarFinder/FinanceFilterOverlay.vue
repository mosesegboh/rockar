<script>
import translateString from 'core/filters/Translate';
import appFinanceFilterOverlay from 'dsp2/components/CarFinder/FinanceFilterOverlay';

export default appFinanceFilterOverlay.extend({
    components: {
        translateString
    },

    computed: {
        getCtaText() {
            return this.translateString('Continue');
        },

        MobileHeaderText() {
            return this.translateString('Payment options');
        },

        ContractMileageTitle() {
            return this.translateString('Contract Mileage Limit');
        }
    }
});
</script>
