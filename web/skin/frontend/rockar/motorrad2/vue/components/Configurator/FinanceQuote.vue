<script>
import appConfiguratorFinanceQuote from 'dsp2/components/Configurator/FinanceQuote';

export default appConfiguratorFinanceQuote.extend({
    computed: {
        orderButtonText() {
            return this.translateString('Order This bike');
        }
    }
})
</script>