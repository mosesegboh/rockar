<template>
    <slot></slot>
    <app-modal :show.sync="showConfirmation" class="confirmation-popup simple-popup" :width="width" v-ref:modal>
        <div slot="content">
            <p class="modal-header">{{ confirmationQuestion }} </p>
            <div class="align-right">
                <button class="button-narrow button-empty-light popup-cancel" @click="cancel()">
                    <span><span>{{ 'No' | translate }}</span></span>
                </button>
                <button class="button-default button-narrow button-blue-lagoon popup-confirm" @click="confirm()">
                    <span><span>{{ 'Yes' | translate }}</span></span>
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
                this.$refs.modal.closePopup();
                this.showConfirmation = false;
                this.originalContent[0].click();
            },

            cancel() {
                this.showConfirmation = false;
            },

            replaceTrigger() {
                // find button which should have confirmation
                this.originalContent = jQuery(this.$el.nextSibling);
                var clone = this.originalContent.clone();
                this.originalContent.hide();
                clone.insertAfter(this.originalContent);
                clone.on('click', (event) => {
                    this.showConfirmation = true;
                    event.preventDefault();
                });
            }
        },

        ready() {
            this.replaceTrigger();
        },

        components: {
            appModal
        }
    });
</script>
