<template>
    <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
    <div class="policy-popups-wrapper">
        <app-modal class-name="policy-popup" :show="true" :show-close="false" v-ref:policy-modal>
            <div slot="content">
                <div class="modal-dialog">
                    <div class="modal-content policy-poup">
                        <div class="modal-header">
                            <p class="policy-popup-header">{{ 'Policy Update.' | translate }}</p>
                            <p class="policy-popup-subheader">{{ 'In accordance with the latest regulations, please confirm your consent below:' | translate }}</p>
                        </div>
                        <div class="grid">
                            <div class="modal-body">
                                <div class="popup-list" v-for="(policyId, policy) in parsedPolicies">
                                    <div class="popup-checkboxes">
                                        <input type="checkbox"
                                            :id="'policy-checkbox'+policyId"
                                            :value="policyId"
                                            v-model="checkedPolicies">
                                        <label :for="'policy-checkbox'+policyId"><span></span></label>
                                    </div>
                                    <div>
                                        <strong class="popup-subtitle">
                                            {{ policy.title | translate }}
                                            <span v-if="policy.isMandatory">*</span>
                                        </strong>
                                        <br>
                                        <span v-html="policy.description"></span>
                                    </div>
                                </div>
                                <p class="popup-mandatory-title">
                                    <strong>{{ '* Mandatory' | translate }}</strong>
                                </p>
                                <div class="row align-justified">
                                    <button type="button" @click="logout"
                                            class="button button-narrow button-grey">
                                        {{ 'Back' | translate }}
                                    </button>
                                    <button type="button" @click="submitPolicies"
                                            :disabled="!checkMandatory()"
                                            :class="[checkMandatory() ? 'button button-narrow popup-continue' : 'button button-narrow popup-continue button-disabled']">
                                        {{ 'Accept' | translate }}
                                    </button>
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
        </app-modal>
        <div class="account-missing-popup">
            <app-account-missing-details-popup :show='showMissingDetailsPopup'
                :given-name='givenName'
                :surname='surname'
                :salutation='salutation'
                :submit-url='submitMissingDetailsUrl'
            >
            </app-account-missing-details-popup>
        </div>
    </div>
</template>

<script>
    import appModal from 'core/components/Elements/Modal';
    import appAccountMissingDetailsPopup from 'bmw/components/Elements/AccountMissingDetailsPopup';

    export default Vue.extend({
        props: {
            customerPolicies: {
                required: false,
                type: String
            },

            submitUrl: {
                required: false,
                type: String
            },

            submitMissingDetailsUrl: {
                required: true,
                type: String
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

            showDetailsPopup: {
                required: true,
                type: Boolean
            }
        },

        data() {
            return {
                parsedPolicies: [],
                checkedPolicies: [],
                showMissingDetailsPopup: false,
                error: false,
                ajaxLoading: false
            }
        },

        methods: {
            logout() {
                this.ajaxLoading = true;
                this.$http({
                    url: this.submitUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        'logout': true
                    }
                }).then(this.submitSuccess);
            },

            submitPolicies() {
                const policyIds = Object.assign({}, this.checkedPolicies);
                this.error = false;
                this.ajaxLoading = true;

                this.$http({
                    url: this.submitUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        'policy_submit': Object.keys(policyIds).length > 0 ? policyIds : 0
                    }
                }).then(this.submitSuccess);
            },

            submitSuccess(response) {
                if (response.data.logout) {
                    window.location.href = response.data.redirect;
                } else {
                    this.ajaxLoading = false;

                    if (response.data.success) {
                        this.$refs.policyModal.closePopup();

                        if (this.showDetailsPopup) {
                            this.showMissingDetailsPopup = true;
                        }
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
            },

            checkMandatory() {
                let isMandatory = true;
                const mandatoryPolicyIds = [];

                Object.entries(this.parsedPolicies).forEach(([key, value]) => {
                    if (value.isMandatory === true) {
                        mandatoryPolicyIds.push(key);
                    }
                })

                if (mandatoryPolicyIds.length > 0) {
                    const allMandatoryPoliciesChecked = (arr, target) => target.every(el => arr.includes(el));

                    if (!allMandatoryPoliciesChecked(this.checkedPolicies, mandatoryPolicyIds)) {
                        isMandatory = false;
                    }
                }

                return isMandatory;
            }
        },

        created() {
            this.parsedPolicies = JSON.parse(this.customerPolicies);
        },

        components: {
            appModal,
            appAccountMissingDetailsPopup
        }
    });
</script>
