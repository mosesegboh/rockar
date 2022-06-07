<script>
    import coreProductTopContainer from 'dsp2/components/Configurator/ProductTopContainer';
    import appCarCompare from 'mini2/components/CarFinder/CarCompare';
    import appSaveCar from 'mini2/components/SaveCar';
    import appPodSummarySpecs from 'mini2/components/Elements/PodSummarySpecs';

    export default coreProductTopContainer.extend({
        components: {
            appSaveCar,
            appCarCompare,
            appPodSummarySpecs
        }
    });
</script>
