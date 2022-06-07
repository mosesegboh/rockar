<script>
import appCarFilter from 'dsp2/components/CarFinder/CarFilter.vue';
import translateString from 'core/filters/Translate';
import appPartExchangeBlock from 'motorrad2/components/PartExchange/PartExchangeBlock/PartExchangeBlock';

export default appCarFilter.extend({
    methods: {
        translateString
    },

    computed: {
        resetFilterTitleText() {
            return this.translateString('Reset all filters.');
        },

        resetFilterApproveText() {
            return this.translateString('Yes. Reset my filters');
        },

        resetFilterRefuseText() {
            return this.translateString('No. Keep my filters');
        }
    },

    components: {
        appPartExchangeBlock
    }
});
</script>