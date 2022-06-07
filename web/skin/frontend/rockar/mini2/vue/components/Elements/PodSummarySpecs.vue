<script>
    import appPodSummarySpecs from 'dsp2/components/Elements/PodSummarySpecs';

    export default appPodSummarySpecs.extend({
        data() {
            return {
                carAttributesToShow: {
                    combustion: [
                        'engine_power',
                        'n_0_to_100',
                        'ec_combined_mpg'
                    ],
                    electric: [
                        'engine_power',
                        'n_0_to_100'
                    ],
                    hybrid: [
                        'engine_power',
                        'n_0_to_100'
                    ]
                }
            }
        }
    });
</script>
