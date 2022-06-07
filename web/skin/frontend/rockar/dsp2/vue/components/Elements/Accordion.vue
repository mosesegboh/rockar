<template>
    <ul :class="['accordion', 'type-'+type, className, disabled ? 'disabled' : '' ]">
        <li :class="[ show ? 'is-expanded' : 'closed' ]">
            <a href="#" class="js-accordion-trigger" @click.prevent="toggleAccordion(id)">
                {{{ title | translate }}}
                <div class="use-button" @click.stop.prevent="$emit('button-action')" v-if="useButton">
                    <slot name="button"></slot>
                </div>
            </a>

            <ul class="submenu" v-el="accordionContent" v-show="show" transition="accordionSlide">
                <slot></slot>
            </ul>
        </li>
    </ul>
</template>

<script>
    import Accordion from 'core/components/Shared/Accordion';
    import perfectScrollbar from 'perfect-scrollbar';
    import accordionSlide from 'core/transitions/Accordion-Slide';

    export default Vue.extend({
        mixins: [Accordion],

        props: {
            id: {
                required: false,
                default: null
            },

            scrollable: {
                required: false,
                type: Boolean,
                default: false
            },

            open: {
                required: false,
                type: Boolean,
                default: false
            },

            useButton: {
                required: false,
                type: Boolean,
                default: false
            },

            features: {
                required: false,
                type: Array,
            }
        },

        transitions: {
            'accordionSlide': Object.assign(accordionSlide, {
                afterLeave() {
                    if (this.scrollInstanceElement && this.scrollable) {
                        perfectScrollbar.destroy(this.scrollInstanceElement);
                        this.scrollInstanceElement = false;
                    }
                    EventsBus.$emit('AccordionGroup::animationEnd');
                },

                enter(el, done) {
                    jQuery(el).slideDown(400, done);
                    EventsBus.$emit('AccordionGroup::animationStart', el);
                },

                afterEnter(el) {
                    const title = jQuery(this.$el);

                    if (this.scrollShow && this.scrollOnShow) {
                        this.$nextTick(() => {
                            const offset = jQuery('.fixed-header-wrapper').outerHeight();
                            jQuery('html, body').animate({
                                scrollTop: this.device === 'desktop' || this.device === 'ldesktop' ? title.offset().top - offset : title.offset().top
                            }, 400);
                        });
                    }

                    if (!this.scrollInstanceElement && this.scrollable) {
                        this.scrollInstanceElement = el;

                        perfectScrollbar.initialize(
                            el
                        );
                    }
                }
            })
        },

        data() {
            return {
                scrollInstanceElement: false,
                openCarFilterAfterPartExchange: true
            }
        },

        computed: {
            device() {
                return this.$store.state.general.device;
            }
        },

        methods: {
            toggleAccordion() {
                if (!this.disabled) {
                    this.show = !this.show;
                    this.$dispatch('AccordionGroup::toggleAccordion', this.id, this.show);
                    EventsBus.$emit('AccordionGroup::toggleAccordion', this.id, this.show);

                    if (!this.show) {
                        if (this.id === 'part_exchange' && this.openCarFilterAfterPartExchange) {
                            this.$root.$refs.carFilter.$parent.toggleAccordion();
                            this.openCarFilterAfterPartExchange = false;
                        }
                    }
                }
            },

            openAccordion() {
                if (!this.show) {
                    this.toggleAccordion();
                }
            },

            closeAccordion() {
                if (this.show) {
                    this.toggleAccordion();
                }
            },

            resize(e) {
                if (this.scrollInstanceElement && this.scrollable) {
                    perfectScrollbar.update(this.scrollInstanceElement);
                }
            }
        },

        events: {
            'Accordion::closeAllAccordion'(expand) {
                this.scrollShow = false;
                this.show = !expand;

                setTimeout(() => {
                    this.scrollShow = true;
                }, 500)
            }
        },

        ready() {
            window.addEventListener('resize', this.resize);

            if (this.open) {
                this.show = true;

                if (this.scrollOnShow) {
                    this.scrollShow = false;
                    setTimeout(() => {
                        this.scrollShow = true;
                    }, 500);
                }
            }

            if (this.features) {
                this.features.find((feature) => {
                    if (feature.title === this.$root.$refs.configurator.selectedFeatureTitle) {
                        this.open = true;
                        this.show = true;
                    }
                })
            }
        },

        destroyed() {
            // Unbind event to avoid memory leak
            window.removeEventListener('resize', this.resize);
        }
    });
</script>
