<script>
import translateString from 'core/filters/Translate';
import appFinanceFilterOverlay from 'dsp2/components/CarFinder/FinanceFilterOverlay';

export default appFinanceFilterOverlay.extend({
    components: {
        translateString
    },

    computed: {
        getVideButtonText() {
            let videoText = this.translateString('Watch Video');

            this.financeOptions.items.forEach((item) => {
                if (parseInt(item.group_id) === parseInt(this.groupId)) {
                    videoText = this.translateString(`Watch ${item.group_full_title} Video`);
                }
            });

            return videoText;
        },

        getTradeInButtonText() {
            return this.translateString('Add My Trade-In');
        },

        getCtaText() {
            let ctaText = this.translateString('Continue with payment option');

            this.financeOptions.items.forEach((item) => {
                if (parseInt(item.group_id) === parseInt(this.groupId)) {
                    ctaText = this.translateString(`Continue with ${item.group_full_title}`);
                }
            });

            return ctaText;
        }
    }
});
</script>
