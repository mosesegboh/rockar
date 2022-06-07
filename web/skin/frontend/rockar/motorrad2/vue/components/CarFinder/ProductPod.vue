<script>
    import appProductPod from 'dsp2/components/CarFinder/ProductPod';
    import appPodSummarySpecs from 'motorrad2/components/Elements/PodSummarySpecs';

    export default appProductPod.extend({
        components: {
            appPodSummarySpecs
        },

        computed: {
            showImageSwitcher() {
                return false;
            },

            viewVehicleText() {
                return this.translateString('View Bike');
            }
        }
    });
</script>
