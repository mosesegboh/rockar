<template>
    <div class="modal-dialog">
        <div class="modal-content gcdm-popup">
            <p class="modal-header gcdm-popup-header">
                {{ 'Registration.' || translate }}
            </p>
            <div class="grid">
                <div class="modal-body">
                    <p>
                        <strong>{{ 'You have not yet confirmed your registration.' || translate }}</strong>
                    </p>
                    <p>{{ 'You recently received an e-mail with an activation link to confirm your registration. Please confirm your registration by opening the link, or a new activation link:' || translate }}</p>
                    <strong>{{ 'Your e-mail address:' || translate }}</strong>
                    <span>{{ customerEmail }}</span>

                    <div class="row align-center">
                        <button type="button" @click="logout"
                                name="gcdm_submit" value="logout"
                                class="button button-narrow button-grey">
                            {{ 'Continue to homepage' || translate }}
                        </button>

                        <button type="button" @click="sendRequestEmail"
                                name="gcdm_submit" value="email_sent"
                                class="button button-narrow popup-continue">
                            {{ 'Request new link' || translate }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <p class="align-center error-message">
            {{ errorMessage }}
        </p>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            customerEmail: {
                required: false,
                type: String
            },
            applyUrl: {
                required: false,
                type: String
            },
        },

        data() {
            return {
                errorMessage: ''
            }
        },

        methods: {
            logout() {
                this.$http({
                    url: this.applyUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {}
                }).then(this.submitSuccess);
            },

            sendRequestEmail() {
                this.errorMessage = '';

                this.$http({
                    url: this.applyUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        'gcdm_submit': 'email_sent'
                    }
                }).then(this.submitSuccess);
            },

            submitSuccess(response) {
                if (response.data.error_message) {
                    this.errorMessage = response.data.error_message;
                }

                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                }
            },
        }
    });
</script>
