<script>
    import bmwPartExchangeVrm from 'bmw/components/Configurator/PartExchangeVRM';

    export default bmwPartExchangeVrm.extend({
        methods: {
            carDetailsSuccess(details) {
                this.$super(bmwPartExchangeVrm, 'carDetailsSuccess', details);

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