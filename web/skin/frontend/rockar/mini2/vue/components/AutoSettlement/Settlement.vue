<script>
import appSettlement from 'dsp2/components/AutoSettlement/Settlement';

export default appSettlement.extend({
    computed: {
        carMatched() {
            if (this.settlementQuoteIndex === '') {
                return true;
            }

            return this.selectedSettlementCapCode === this.currentPxCapId && this.selectedPxPlateYear === this.currentPxPlateYear;
        }
    }
});
</script>