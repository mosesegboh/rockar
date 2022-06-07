<template>
    <slot></slot>
    <app-modal v-if="!renderInBody || showConfirmation"
               :show.sync="showConfirmation"
               class="confirmation-popup simple-popup"
               :class="customClass"
               :width="width"
               v-ref:modal
    >
        <div slot="content">
            <p class="confirmation-title">{{ title }} </p>
            <p class="confirmation-question">{{ confirmationQuestion }} </p>
            <div class="confirmation-buttons">
                <button class="button dsp2-outline" @click="cancel()">
                    {{ negative }}
                </button>
                <button class="button dsp2-money" @click="confirm()">
                    {{ affirmative }}
                </button>
            </div>
        </div>
    </app-modal>
</template>
<script>
    import appModal from 'core/components/Elements/Modal';

    export default Vue.extend({
        props: {
            confirmationQuestion: {
                required: true,
                type: String
            },

            width: {
                required: false,
                type: String,
                default: ''
            },

            title: {
                required: false,
                type: String,
                default: ''
            },

            affirmative: {
                required: false,
                type: String,
                default: 'Yes'
            },

            negative: {
                required: false,
                type: String,
                default: 'No'
            },

            waitForResponce: {
                required: false,
                type: Boolean,
                default: false
            },

            customClass: {
                required: false,
                type: String,
                default: ''
            },

            /**
             * If some of element ancestors has transform css property
             * The modal can be rendered in body element
             */
            renderInBody: {
                required: false,
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                showConfirmation: false,
                originalContent: null
            }
        },

        methods: {
            confirm() {
                if (!this.waitForResponce) {
                    this.$refs.modal.closePopup();
                    this.showConfirmation = false;
                }

                this.originalContent[0].click();

                if (this.renderInBody) {
                    this.$refs.modal.$remove();
                }
            },

            cancel() {
                this.showConfirmation = false;
                this.$dispatch('ConfirmationModal::cancel');

                if (this.renderInBody) {
                    this.$refs.modal.$remove();
                }
            },

            openConfirmationDialog(event) {
                this.showConfirmation = true;

                event.preventDefault();

                if (this.renderInBody) {
                    this.$nextTick(() => {
                        this.$refs.modal.$appendTo(document.body);
                    });
                }
            },

            replaceTrigger() {
                // find button which should have confirmation
                this.originalContent = jQuery(this.$el.nextSibling);
                this.clone = this.originalContent.clone();
                this.originalContent.hide();
                this.clone.insertAfter(this.originalContent);
                this.clone[0].addEventListener('click', this.openConfirmationDialog);
            }
        },

        ready() {
            this.replaceTrigger();
        },

        beforeDestroy() {
            this.clone[0].removeEventListener('click', this.openConfirmationDialog)
        },

        events: {
            'close-popup'() {
                if (this.renderInBody) {
                    this.cancel();
                }
            }
        },

        components: {
            appModal
        }
    });
</script>
