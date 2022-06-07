export default {
    props: {
        openFirst: {
            required: false,
            type: Boolean,
            default: false
        },

        openAll: {
            required: false,
            type: Boolean,
            default: false
        },

        activeStep: {
            required: false,
            type: String
        }
    },

    data() {
        return {
            cName: 'accordionGroup'
        };
    },

    events: {
        'AccordionGroup::toggleAccordion': {
            handler(id) {
                if (!this.openAll) {
                    this.$children.forEach((accordion) => {
                        if (accordion.id !== id) {
                            accordion.show = false;
                        }
                    });
                }
            }
        }
    },

    ready() {
        if (this.openFirst && this.$children[0]) {
            this.$children[0].show = true;
        }

        if (this.openAll) {
            this.$broadcast('Accordion::closeAllAccordion', false);
        }
    }
};
