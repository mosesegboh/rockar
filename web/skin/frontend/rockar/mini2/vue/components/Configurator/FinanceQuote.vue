<script>
    import appFinanceQuote from 'dsp2/components/Configurator/FinanceQuote';
    import translateString from 'core/filters/Translate';

    export default appFinanceQuote.extend({
        computed: {
            orderButtonText() {
                return this.translateString('Order This MINI');
            }
        },

        methods: {
            translateString
        }
    });
</script>
