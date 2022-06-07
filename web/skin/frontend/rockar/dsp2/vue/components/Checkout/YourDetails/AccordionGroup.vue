<script>
    import AccordionGroupBase from 'dsp2/components/Shared/AccordionGroup';

    export default Vue.extend({
        mixins: [AccordionGroupBase],

        props: {
            childItems: {
                required: true,
                type: Array
            }
        },

        events: {
            'CheckoutCreditAppAccordionGroup::changeStep'(stepCode) {
                this.uiHelper(stepCode);
            },

            'CheckoutCreditAppAccordionGroup::toggleAccordion'(stepCode) {
                this.uiHelper(stepCode);
            }
        },

        methods: {

            /**
             *  helper method, performs the hiding of other accordion items, as well as disabling "future" items.
             * @param stepCode the step code to show
             *
             * @return void
             */
            uiHelper(stepCode) {
                const activeStep = this.getStepIndex(stepCode);
                this.$children.forEach((accordion, index) => {
                    const thisIndex = this.getStepIndex(accordion.stepCode);
                    accordion.disabled = activeStep < thisIndex;
                    if (thisIndex !== activeStep) {
                        accordion.show = false;
                    }
                });
            },

            /**
             * gets an index associated with the given step code
             * @param stepCode, string stepCode prop for the accordion
             * @returns {number} index of the given stepCode
             */
            getStepIndex(stepCode) {
                return this.childItems.indexOf(stepCode);
            }
        },

        ready() {
            if (this.activeStep) {
                this.uiHelper(this.activeStep);
            } else {
                this.uiHelper(this.childItems[0])
            }
        },

        watch: {
            'activeStep'(newVal) {
                this.uiHelper(newVal);
            }
        }
    });
</script>
