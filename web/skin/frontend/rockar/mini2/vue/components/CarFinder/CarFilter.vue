<script>
    import appCarFilter from 'dsp2/components/CarFinder/CarFilter.vue';
    import appPartExchangeBlock from 'mini2/components/PartExchange/PartExchangeBlock/PartExchangeBlock';
    import translateString from 'core/filters/Translate';

    export default appCarFilter.extend({
        methods: {
            translateString
        },

        computed: {
            resetFilterApproveText() {
                return this.translateString('Yes. Reset my filters.');
            },

            resetFilterRefuseText() {
                return this.translateString('No. Keep my filters.');
            },

            resetFilterTitleText() {
                return this.translateString('Reset all filters.');
            }
        },

        components: {
            appPartExchangeBlock
        }
    });
</script>
