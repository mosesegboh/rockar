<script>
import appAccordionNav from 'dsp2/components/Checkout/AccordionNav';

export default appAccordionNav.extend({
    data() {
        return {
            stepTitles: [
                'Tell us about yourself',
                'Pick up or delivery?',
                'Trade-in (optional)',
                'Choose your payment option',
                'Get financed today',
                'That’s a wrap'
            ]
        };
    },

    methods: {
        changeStep(step, index) {
            if (index < this.activeStepIndex) {
                this.$root.$refs.accordionGroup.showStep(step);
                this.$root.$refs.checkoutHeader.title = this.stepTitles[index];
            }
        }
    }
});
</script>
