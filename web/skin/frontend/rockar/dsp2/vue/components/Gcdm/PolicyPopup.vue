<template>
    <div class="policy-popups-wrapper">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <app-modal class-name="policy-popup" :show="true" :show-close="false" v-ref:policy-modal>
            <div slot="content">
                <div class="header-mobile">
                    <a class="popup-button-previous popup-close" @click="logout()">&nbsp;</a>
                    {{ 'Policy update' | translate }}
                    <a class="popup-button-close popup-close" @click="logout()">&nbsp;</a>
                </div>
                <div class="modal-dialog">
                    <div class="modal-content policy-poup">
                        <div class="modal-header">
                            <p class="policy-popup-header">{{ 'Policy Update' | translate }}</p>
                            <p class="policy-popup-subheader">{{ 'In accordance with the latest regulations, please confirm your consent below:' | translate }}</p>
                            <a class="popup-button-close popup-close" @click="logout()">&nbsp;</a>
                        </div>
                        <div class="grid">
                            <div class="modal-body">
                                <div class="popup-list" v-for="(policyId, policy) in parsedPolicies" :key="policyId">
                                    <div class="popup-checkboxes">
                                        <input type="checkbox"
                                            :id="'policy-checkbox'+policyId"
                                            :value="policyId"
                                            v-model="checkedPolicies">
                                        <label :for="'policy-checkbox'+policyId"><span></span></label>
                                    </div>
                                    <div>
                                        <div class="popup-subtitle">
                                            {{ policy.title | translate }}
                                            <span v-if="policy.isMandatory">*</span>
                                        </div>
                                        <div class="popup-text" v-html="policy.description"></div>
                                    </div>
                                </div>

                                <div class="policy-popup-buttons">
                                    <div class="policy-popup-buttons-wrapper">
                                        <div>
                                            <button type="button" @click="logout()" class="button dsp2-outline">
                                                {{ 'Back' | translate }}
                                            </button>
                                        </div>
                                        <div>
                                            <button type="button"
                                                @click="submitPolicies"
                                                :disabled="!checkMandatory()"
                                                :class="[checkMandatory() ? 'button dsp2-money' : 'button dsp2-money disabled']"
                                            >
                                                {{ 'Accept' | translate }}
                                            </button>
                                            <p class="popup-mandatory-title">
                                                {{ '* Indicates a required field.' | translate }}
                                            </p>
                                        </div>
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
    import appAccountMissingDetailsPopup from 'dsp2/components/Elements/AccountMissingDetailsPopup';

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

        ready() {
            this.$nextTick(() => {
                jQuery('body').css({ overflow: 'hidden' });
            });

            jQuery(this.$el).click(() => {
                this.logout();
            });
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
                    jQuery('body').css({ overflow: 'visible' });
                } else {
                    this.ajaxLoading = false;

                    if (response.data.success) {
                        this.$refs.policyModal.closePopup();
                        jQuery('body').css({ overflow: 'visible' });

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
