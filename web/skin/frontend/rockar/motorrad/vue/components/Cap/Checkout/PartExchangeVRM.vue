<script>
    import motorradPartExchangeVrm from 'motorrad/components/Configurator/PartExchangeVRM';

    export default motorradPartExchangeVrm.extend({
        methods: {
            carDetailsSuccess(details) {
                this.$super(motorradPartExchangeVrm, 'carDetailsSuccess', details);

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
