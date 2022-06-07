<template>
    <ul class="accordion accordion-blue" :class="['type-' + type, className, { 'disabled': disabled }, { 'no-display': hidden }]">
        <li :class="{ 'is-expanded': show }">
            <a href="#" class="js-accordion-trigger" @click.prevent="toggleAccordion">
                {{ title }}
                <div :class="{ 'checked': checked }"><div></div></div>
            </a>
            <ul class="submenu" v-el="accordionContent" v-show="show" transition="accordionSlide">
                <slot></slot>
            </ul>
        </li>
    </ul>
</template>

<script>
    import Accordion from 'core/components/Shared/Accordion';
    import accordionSlide from 'core/transitions/Accordion-Slide';

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

        transitions: {
            'accordionSlide': Object.assign(accordionSlide, {
                afterEnter() {
                    if (this.$root.$refs.yourDetails) {
                        if (jQuery('.navigation-bar-mobile').css('display') !== 'none') {
                            const firstAccordion = 288;
                            const accordionHeader = 72;
                            const nav = jQuery('#accordion-nav').outerHeight();
                            const header = jQuery('.main-nav').outerHeight();

                            if (typeof this.$parent.getStepIndex !== 'undefined') {
                                const offset = (this.$parent.getStepIndex(this.stepCode) * accordionHeader) + firstAccordion;
                                jQuery('html, body').animate({ scrollTop: offset - (header + nav) }, 400);
                            }
                        }
                    }
                }
            })
        },

        methods: {
            /**
             *  turn the accordion on or off
             *  @return void
             */
            toggleAccordion() {
                if (!this.disabled) {
                    if (!this.show) {
                        this.$dispatch('CheckoutCreditAppAccordionGroup::changeStep', this.stepCode);
                    }
                    this.show = !this.show;
                    this.$dispatch('CheckoutCreditAppAccordionGroup::toggleAccordion', this.stepCode);
                }
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

                if (status) {
                    this.$dispatch('CheckoutCreditAppAccordionGroup::showAccordion', this.stepCode);
                }
            }
        },

        computed: {
            checked() {
                return !this.disabled && this.stepCode !== this.$parent.$parent.activeStep;
            }
        },

        ready() {
            if (this.open) {
                this.show = true;
            }
        }
    });
</script>
