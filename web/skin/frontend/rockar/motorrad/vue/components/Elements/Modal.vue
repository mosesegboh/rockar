<template>
    <div :class="['popup', className ? className : '', show ? '' : 'popup-hide']" @click="showClose && closePopup()">
        <div class="popup-overlay"></div>
        <div class="popup-container">
            <div class="bg-close">
                <div class="popup-content" :style="{ width: width ? width : '' }" @click.stop>
                    <a v-if="showClose" class="popup-button-close popup-close" :class="[testibilityClass]" @click="closePopup" v-el:close-trigger>&nbsp;</a>
                    <div class="content-wrapper">
                        <slot name="content">{{{ popupContent }}}</slot>

                        <div class="row" v-if="addCloseButton">
                            <div class="col-12">
                                <button type="button" name="button" @click="closePopup" class="button button-blue-lagoon popup-close">{{ closeButtonText }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import appFormValidation from 'core/utils/FormValidation';

    export default Vue.extend({
        props: {
            show: {
                required: false,
                type: Boolean,
                default: false
            },
            width: {
                required: false,
                type: String
            },
            className: {
                required: false,
                type: String,
                default: null
            },
            url: {
                required: false,
                type: String,
                default: null
            },
            dom: {
                required: false,
                type: String,
                default: null
            },
            addCloseButton: {
                required: false,
                type: Boolean,
                default: false
            },
            closeButtonText: {
                required: false,
                type: String,
                default: 'OK'
            },
            showClose: {
                required: false,
                type: Boolean,
                default: true
            },
            testibilityClass: {
                required: false,
                type: String
            }
        },

        data() {
            return {
                popupContent: null,
                preloader: '<span class="popup-loader"></span>'
            }
        },

        methods: {
            closePopup() {
                this.show = false;
                this.popupContent = null;
                jQuery('body').removeClass('modal-open');
                this.$dispatch('close-popup');
            },

            handleAjaxSuccess(resp) {
                this.popupContent = resp.data;

                /**
                 * because vue, form is just added to $el and its not yet available in the variable, timeout fixes it
                 */
                setTimeout(() => {
                    var elements = this.$el.getElementsByTagName('form');

                    for (var i = 0; i < elements.length; i++) {
                        jQuery(elements[i]).validate({ // form validation for ajax added forms
                            errorClass: 'validation-advice',
                            errorElement: 'div',
                            ignoreTitle: true
                        });
                    }
                }, 10);
            },

            handleAjaxFail(error) {
                if (error.status === 401) {
                    this.$root.loggedOutPopup();
                }
                console.error('Modal:', error);
            }
        },

        watch: {
            'show'(isOpening) {
                if (typeof loginTimeout !== 'undefined' && loginTimeout !== false) {
                    clearTimeout(loginTimeout);
                    loginTimeout = false;
                }

                if (isOpening) {
                    jQuery('body').addClass('modal-open');
                    new appFormValidation();
                } else {
                    jQuery('body').removeClass('modal-open');
                }

                if (isOpening && this.url) {
                    this.popupContent = this.preloader;
                    this.$http.get(this.url).then(this.handleAjaxSuccess, this.handleAjaxFail);
                } else if (isOpening && this.dom) {
                    var domEl = document.querySelector(this.dom);
                    this.popupContent = domEl.innerHTML;
                }
            }
        },

        ready() {
            if (this.show) {
                jQuery('body').addClass('modal-open');
            }

            // Prevent popup auto-close when clicking inside it's body
            jQuery('.popup-content').on('click', (e) => {
                e.stopPropagation();
            });
        },

        destroyed() {
            jQuery('body').removeClass('modal-open');
        }
    });
</script>
