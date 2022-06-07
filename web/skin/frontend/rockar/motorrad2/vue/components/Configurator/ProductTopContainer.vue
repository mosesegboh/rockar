<script>
    import appProductTopContainer from 'dsp2/components/Configurator/ProductTopContainer';
    import appPodSummarySpecs from 'motorrad2/components/Elements/PodSummarySpecs';

    export default appProductTopContainer.extend({
        components: {
            appPodSummarySpecs
        },

        computed: {
            showInterior360() {
                return false;
            },

            showExteriorBackground() {
                return false;
            },

            bookTestDriveText() {
                return this.translateString('Book a test ride');
            }
        }
    });
</script>
