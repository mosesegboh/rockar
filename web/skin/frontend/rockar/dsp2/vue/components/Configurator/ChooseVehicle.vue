<script>
    import appChooseVehicle from 'core/components/Configurator/ChooseVehicle';
    import appSaveCar from 'dsp2/components/SaveCar';

    export default appChooseVehicle.extend({
        methods: {
            chooseCar() {
                this.ajaxLoading = true;
                this.$dispatch('Configurator::sendCarId', this.id);
                this.$dispatch('ChooseVehicleGrid::resetSelect');
                this.showPartExchangeNotification = false;
                this.carSelected = true;
                const accessories = this.$root.$refs.configurator.addedAccessories;

                const action = this.$root.$refs.configurator.prevActiveCar === this.id
                    ? 'update_finance'
                    : '';

                this.$http({
                    url: this.choosePreConfiguredUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        id: this.id,
                        configurable_id: this.$parent.product.id,
                        action,
                        accessories
                    }
                }).then(this.updatePDP, this.updatePDPFail).then(() => {
                    this.ajaxLoading = false;
                });
            }
        },

        components: {
            appSaveCar
        }
    });
</script>
