<template>
    <div>
        <slot></slot>
        <app-modal :show="!!this.checkoutErrorMessage" :show-close="false" class-name="simple-popup error-popup">
            <div slot="content">
                <h2 v-if="checkoutErrorMessageTitle">{{ checkoutErrorMessageTitle }}</h2>
                <h2 v-else>{{ 'Something went wrong!' | translate }}</h2>
                <div v-html="checkoutErrorMessage"></div>
                <div class="align-right row">
                    <div class="col-12">
                        <button type="button" name="button" class="button button-confirm" @click.prevent="processFailure()">{{ 'Back' | translate }}</button>
                    </div>
                </div>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import appCoreCheckout from 'core/components/Checkout/Checkout';
    import appModal from 'core/components/Elements/Modal';
    import translateString from 'core/filters/Translate';

    export default appCoreCheckout.extend({

        props: {
            validateProductUrl: {
                required: true,
                type: String
            },
            carFinderUrl: {
                required: false,
                type: String
            }
        },

        data() {
            return {
                checkoutErrorMessage: false,
                checkoutErrorMessageTitle: false,
                invalidProductRedirectUrl: false
            }
        },

        ready() {
            jQuery('body').addClass('on-light');
            jQuery('#cart-amount').text(1);
            jQuery('#cart-amount-tooltip').text(this.translateString('Cart'));
        },

        events: {
            'Checkout::nextStep'(stepCode) {
                jQuery('html, body').animate({ scrollTop: 0 }, 400);
                this.$broadcast('FinanceOverlay::clearCache');
                this.validateProduct(stepCode);
            }
        },

        methods: {
            translateString,

            validateProduct(step) {
                this.ajaxLoading = true;

                this.$http({
                    url: this.validateProductUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        currentStep: step
                    }
                }).then(this.validateProductSuccess, this.validateProductFail);
            },

            validateProductSuccess(resp) {
                this.ajaxLoading = false;
            },

            validateProductFail(resp) {
                this.ajaxLoading = false;

                if (resp.data.message) {
                    this.checkoutErrorMessage = resp.data.message;

                    if (resp.data.message_title) {
                        this.checkoutErrorMessageTitle = resp.data.message_title;
                    }
                }

                if (resp.data.redirect && resp.status !== 401 && !resp.data.out_of_stock) {
                    window.location.href = resp.data.redirect;
                    return;
                }

                if (resp.data && resp.data.message) {
                    if (resp.data.out_of_stock) {
                        this.invalidProductRedirectUrl = resp.data.redirect ? resp.data.redirect : this.carFinderUrl;
                    }
                }
            },

            processFailure() {
                window.location.href = this.invalidProductRedirectUrl ? this.invalidProductRedirectUrl : '';
            }
        },

        components: {
            appModal
        }
    });
</script>
