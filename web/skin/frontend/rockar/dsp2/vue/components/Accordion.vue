<template>
    <ul :class="['accordion', 'type-'+type, className, disabled ? 'disabled' : '' ]">
        <li :class="[ show ? 'is-expanded' : 'closed' ]">
            <a href="#" class="js-accordion-trigger" @click.prevent="toggleAccordion(id)">
                {{{ title | translate }}}
                <div class="use-button" @click.stop.prevent="$emit('button-action')" v-if="useButton">
                    <slot name="button"></slot>
                </div>
            </a>

            <ul :class="[ 'submenu', childClassName ]" v-el="accordionContent" v-show="show" transition="accordionSlide">
                <slot></slot>
            </ul>
        </li>
    </ul>
</template>

<script>
import appAccordion from 'core/components/Accordion';

export default Vue.extend({
    mixins: [appAccordion],

    props: {
        childClassName: {
            required: false,
            type: String,
            default: ''
        }
    }
});
</script>
