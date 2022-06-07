<script>
    import appPartExchangeBlock from 'dsp2/components/PartExchange/PartExchangeBlock/PartExchangeBlock';
    import translateString from 'core/filters/Translate';

    export default appPartExchangeBlock.extend({
        methods: {
            translateString
        },

        computed: {
            buttonClass() {
                return 'dsp2-money';
            },

            descriptionText() {
                return this.translateString('Thinking about trading in your vehicle for a BMW bike? You’ve made the right choice. Lets get started.');
            },

            buttonText() {
                return this.translateString('Add my trade-in');
            }
        }
    });
</script>