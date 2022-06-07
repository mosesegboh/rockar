<template>
    <ul :class="['accordion', 'accordion-blue', 'type-'+type, className, disabled ? 'disabled' : '', hidden ? 'no-display' : '']">
        <li :class="{ 'is-expanded': show }">
            <a href="#" class="js-accordion-trigger" @click.prevent="toggleAccordion(this.stepCode)">
                {{{ title }}}
                <span class="status text-right">{{{ status }}}</span>
            </a>
            <ul class="submenu" v-el="accordionContent" v-show="show" transition="accordionSlide">
                <slot></slot>
            </ul>
        </li>
    </ul>
</template>

<script>
    import Accordion from 'core/components/Shared/Accordion';

    export default Vue.extend({
        mixins: [Accordion],

        props: {
            open: {
                required: false,
                type: Boolean,
                default: false
            },
            stepCode: {
                required: true,
                type: String
            }
        },

        data() {
            return {
                status: '',
                hidden: false
            }
        },

        methods: {
            toggleAccordion() {
                if (!this.disabled) {
                    if (!this.show) {
                        this.$dispatch('CheckoutAccordionGroup::changeStep', this.stepCode);
                    }
                    this.show = !this.show;
                    this.$dispatch('CheckoutAccordionGroup::toggleAccordion', this.stepCode);
                }
            },

            getNextStepIndex() {
                return this.$parent.getNextStepIndex(this.stepCode);
            }
        },

        watch: {
            'show'(status) {
                if (this.$children.length) {
                    if (status === true && typeof this.$children[0].stepShow === 'function') {
                        this.$children[0].stepShow();
                    } else if (status === false && typeof this.$children[0].stepHide === 'function') {
                        this.$children[0].stepHide();
                    }
                }
            }
        },

        ready() {
            if (this.open) {
                this.show = true;
            }
        }
    });
</script>
