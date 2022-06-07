<template>
    <div>
        <div class="dsp2-more-info" :class="{expanded: expand, disabled:disable}" @click="toggle()" v-show="!expand">
            <slot name="header-closed">
                <a class="dsp2-link-s"><span class="arrow-down"></span>{{ 'More info' | translate }}</a>
            </slot>
        </div>
        <div class="dsp2-more-info" :class="{expanded: expand}" @click="toggle()" v-show="expand">
            <slot name="header-opened">
                <a class="dsp2-link-s"><span class="arrow-up"></span>{{ 'Less info' | translate }}</a>
            </slot>
        </div>
        <div class="dsp2-more-info content" v-show="expand">
            <slot name="content"></slot>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            expand: {
                type: Boolean,
                required: false,
                default: false
            },

            disable: {
                type: Boolean,
                required: false,
                default: true
            }
        },

        watch: {
            disable(newVal) {
                this.expand = this.expand && newVal ? false : this.expand;
            }
        },

        methods: {
            toggle() {
                this.expand = !this.disable ? !this.expand : false;
            }
        },

        events: {
            'MoreInfo::enable'() {
                this.disable = false;
            },

            'MoreInfo::disable'() {
                this.disable = true;
                this.expand = false;
            }
        }
    });
</script>
