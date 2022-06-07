<script>
    import miniPartExchangeVrm from 'mini/components/Configurator/PartExchangeVRM';

    export default miniPartExchangeVrm.extend({
        methods: {
            carDetailsSuccess(details) {
                this.$super(miniPartExchangeVrm, 'carDetailsSuccess', details);

                this.PX.disableSelects = false;
            },

            softReset() {
                this.$dispatch('PartExchange::softReset', true);
                this.$dispatch('PartExchange::resetSlider');
                this.$parent.disableMoreInfo = true;
                this.carInfo.registrationYear = null;
            },
        },
    });
</script>