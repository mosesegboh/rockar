<script>
    import appPodSummarySpecs from 'dsp2/components/Elements/PodSummarySpecs';

    export default appPodSummarySpecs.extend({
        data() {
            return {
                carAttributesToShow: {
                    combustion: [
                        'engine_capacity',
                        'engine_power',
                        'torque_only'
                    ]
                }
            }
        }
    });
</script>
