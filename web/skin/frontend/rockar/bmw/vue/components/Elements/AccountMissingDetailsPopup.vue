<template>
    <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
    <div class="missing-details-wrapper" v-if="show">
        <div class="popup popup-show">
            <div class="popup-overlay"></div>
            <div class="popup-container">
                <div class="popup-content">
                    <div class="content-wrapper">
                        <h3>{{ 'Please confirm your details.' | translate }}</h3>
                        <p class="details-description">{{ 'To complete your login, please confirm that your details are completed and correct.' | translate }}</p>
                        <div class="personal-details-header">
                            <h4> {{ 'Personal Details' | translate }}</h4>
                        </div>
                        <div class="personal-details">
                            <validator name="details" :classes="{ invalid: 'validation-error'}">
                                <form
                                    id="details_form"
                                    @submit.prevent="submitDetails"
                                    method="post"
                                    v-el:form-details
                                    >
                                    <div class="detailed-input-wrapper">
                                        <label for="title">{{ 'Title*:' | translate }}</label>
                                        <div class="select-wrapper">
                                            <select
                                                class="details-input select"
                                                v-model="salutation"
                                                v-validate:salutation="['required']"
                                            >
                                                <option value="">{{ 'Please select' | translate }}</option>
                                                <option v-for="(index, item) in salutations"
                                                    :value="index"
                                                    :selected="salutation === item"
                                                >
                                                    {{ item | translate }}
                                                </option>
                                            </select>
                                            <div
                                                class="validation-error-msg"
                                                v-if="!$details.salutation.valid"
                                            >
                                                {{ 'This field is required.' | translate }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="detailed-input-wrapper">
                                        <label for="first_name">{{ 'First Name(s)*:' | translate }}</label>
                                        <div>
                                            <input
                                                class="details-input"
                                                type="text"
                                                :placeholder="'Please enter your name (as in your ID or Passport)' | translate"
                                                v-model="givenName"
                                                v-validate:given-name="['required']"
                                                initial="off"
                                            />
                                            <div
                                                class="validation-error-msg"
                                                v-if="!$details.givenName.valid"
                                            >
                                                {{ 'This field is required.' | translate }}
                                            </div>
                                        </div>

                                    </div>
                                    <div class="detailed-input-wrapper">
                                        <label for="surname">{{ 'Surname*:' | translate }}</label>
                                        <div>
                                            <input
                                                class="details-input"
                                                type="text"
                                                :placeholder="'Please enter your surname (as in your ID or Passport)' | translate"
                                                v-model="surname"
                                                v-validate:surname="['required']"
                                                initial="off"
                                            />
                                            <div
                                                class="validation-error-msg"
                                                v-if="!$details.surname.valid"
                                            >
                                                {{ 'This field is required.' | translate }}
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </validator>
                        </div>

                        <div class="info-section">
                            <p>*{{ 'Required information' | translate }}.</p>
                            <p class="warning">{{ 'Please enter your full names (as in your ID Passport) correctly' | translate }}.</p>
                        </div>

                        <div class="missing-details-cta-wrapper">
                            <div class="missing-details-modal-ctas">
                                <button type="button" name="button" @click="closePopup()" class="button button-narrow button-empty-light popup-close">
                                    {{ 'Cancel' | translate }}
                                </button>
                                <button type="submit"
                                    form="details_form"
                                    name="button"
                                    class="button button-blue-lagoon"
                                >
                                    {{ 'Submit' | translate }}
                                </button>
                            </div>
                        </div>
                        <div v-if="error" class="error-msg">
                            <p>
                                {{ 'Sorry, an error occurred. Please, try again later.' | translate }}
                            </p>
                            <p v-if="error !== true">
                                {{ error }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({

        props: {
            show: {
                required: false,
                type: Boolean,
                default: true
            },
            surname: {
                required: false,
                type: String,
                default: ''
            },
            givenName: {
                required: false,
                type: String,
                default: ''
            },
            salutation: {
                required: false,
                type: String,
                default: ''
            },
            submitUrl: {
                required: true,
                type: String
            }
        },

        data() {
            return {
                error: false,
                ajaxLoading: false,
                salutations: {
                    SAL_MR: 'Mr.',
                    SAL_MS: 'Ms.',
                    SAL_MRS: 'Mrs.',
                    SAL_MISS: 'Miss',
                    SAL_DR: 'Dr.'
                }
            }
        },

        methods: {
            closePopup() {
                this.submitData({
                    logout: true
                })
            },

            submitDetails() {
                this.$validate(false, () => {
                    if (this.$details.valid) {
                        this.submitData({
                            customer: {
                                firstname: this.givenName,
                                lastname: this.surname,
                                prefix: this.salutations[this.salutation]
                            }
                        });
                    }
                });
            },

            submitData(data) {
                this.error = false;
                this.ajaxLoading = true;

                this.$http({
                    url: this.submitUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data
                }).then(this.submitSuccess);
            },

            submitSuccess(response) {
                if (response.data.logout) {
                    window.location.href = response.data.redirect;
                } else {
                    this.ajaxLoading = false;

                    if (response.data.success) {
                        this.show = false;
                    } else {
                        this.error = this.stripTags(response.data.error_message);

                        if (!this.error) {
                            this.error = true;
                        }
                    }
                }
            },

            stripTags(html) {
                return jQuery('<div />').html(html).text();
            }
        }
    })
</script>
