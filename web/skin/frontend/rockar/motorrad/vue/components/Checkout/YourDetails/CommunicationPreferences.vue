<template>
    <div class="finance-credit finance-credit-preferences">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <div class="row">
            <p>Please select your preferred communication options regarding your finance agreement in order for us to send you financial communication.</p>
        </div>
        <div class="row">
            <div class="col-5">
                <label :class="cssClassForRequiredLabel">{{ 'Preferred Communication Method' | translate }}</label>
            </div>
            <div class="col-2">
                <input
                    type="checkbox"
                    id="pref-email"
                    v-model="formData.preferredComsMethodEmail"
                    :true-value="1"
                    :false-value="0"
                    @change="uniqueCheckComs($event, 'email')"
                >
                <label for="pref-email"><span></span>
                    <p class="accept-terms-statement">{{ 'Email' | translate }}</p>
                </label>
            </div>
            <div class="col-2">
                <input
                    type="checkbox"
                    id="pref-post"
                    v-model="formData.preferredComsMethodNormal"
                    :true-value="1"
                    :false-value="0"
                    @change="uniqueCheckComs($event, 'post')"
                >
                <label for="pref-post"><span></span>
                    <p class="accept-terms-statement">{{ 'Post' | translate }}</p>
                </label>
            </div>
            <div class="col-2">
                <input
                    type="checkbox"
                    id="pref-sms"
                    v-model="formData.preferredComsMethodSms"
                    :true-value="1"
                    :false-value="0"
                >
                <label for="pref-sms"><span></span>
                    <p class="accept-terms-statement">{{ 'SMS' | translate }}</p>
                </label>
            </div>
        </div>
        <div class="row">
            <div class="col-4">
                <label :class="cssClassForRequiredLabel">Contract Documentation</label>
            </div>
            <div class="col-1">
                <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                    <span class="action-badge info-small" slot="tooltipElement"></span>
                    <div slot="tooltipContent">
                        <p>How would you like to receive your contractual documents.</p>
                    </div>
                </app-tooltip>
            </div>
            <div class="col-2">
                <input
                    type="checkbox"
                    id="docs-email"
                    v-model="formData.contractDocumentation"
                    value="0"
                    @change="uniqueCheckDocs"
                >
                <label for="docs-email"><span></span>
                    <p class="accept-terms-statement">{{ 'Email' | translate }}</p>
                </label>
            </div>
            <div class="col-2">
                <input
                    type="checkbox"
                    id="docs-post"
                    v-model="formData.contractDocumentation"
                    value="1"
                    @change="uniqueCheckDocs"
                >
                <label for="docs-post"><span></span>
                    <p class="accept-terms-statement">{{ 'Post' | translate }}</p>
                </label>
            </div>
        </div>
        <div class="row">
            <div class="col-5">
                <label :class="cssClassForRequiredLabel">Statement Frequency</label>
            </div>
            <div class="col-2">
                <input
                    type="checkbox"
                    id="stat-quart"
                    v-model="formData.statementFrequency"
                    value="0"
                    @change="uniqueCheckFrequency"
                >
                <label for="stat-quart"><span></span>
                    <p class="accept-terms-statement">{{ 'Quarterly' | translate }}</p>
                </label>
            </div>
            <div class="col-2">
                <input
                    type="checkbox"
                    id="stat-bi"
                    v-model="formData.statementFrequency"
                    value="1"
                    @change="uniqueCheckFrequency"
                >
                <label for="stat-bi"><span></span>
                    <p class="accept-terms-statement">{{ 'Bi-Monthly' | translate }}</p>
                </label>
            </div>
        </div>
        <div class="row your-details-submit section-action-buttons">
            <div class="col-6">
                <button class="button button-empty" @click="previousView">
                    {{ 'Back' | translate }}
                </button>
            </div>
            <div class="col-6">
                <button class="button left button-dark" @click="formSubmit">
                    {{ 'Save and Continue' | translate }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>

    import appSelect from 'core/components/Elements/Select';
    import yourDetailsHelpers from 'core/components/Checkout/YourDetails/helpers';
    import appMessages from 'core/components/Elements/Messages';
    import translateString from 'core/filters/Translate';
    import appTooltip from 'core/components/Elements/Tooltip';
    import uiVariables from 'motorrad/components/Shared/UIVariables';

    export default Vue.extend({
        mixins: [
            yourDetailsHelpers,
            uiVariables
        ],

        props: {
            initCommunicationDetails: {
                required: true,
                type: Object
            }
        },

        methods: {
            translateString,

            mapFormData() {
                const obj = {};
                obj.pref_method_contact_email = this.formData.preferredComsMethodEmail;
                obj.pref_method_contact_sms = this.formData.preferredComsMethodSms;
                obj.pref_method_contact_normal = this.formData.preferredComsMethodNormal;
                obj.contract_document = this.formData.contractDocumentation[0];
                obj.statement_frequency = this.formData.statementFrequency[0];

                return obj;
            },

            formSubmit() {
                this.$emit('success', this.mapFormData());
            },

            uniqueCheckComs(e, contactMethod) {
                const includeEmail = parseInt(this.formData.preferredComsMethodEmail);
                const includePost = parseInt(this.formData.preferredComsMethodNormal);

                if (!e.target.checked) {
                    if (contactMethod === 'post') {
                        this.formData.preferredComsMethodNormal = 1;
                    } else if (contactMethod === 'email') {
                        this.formData.preferredComsMethodEmail = 1;
                    }
                }

                if (includeEmail && includePost && contactMethod === 'post') {
                    this.formData.preferredComsMethodEmail = 0;
                }

                if (includeEmail && includePost && contactMethod === 'email') {
                    this.formData.preferredComsMethodNormal = 0;
                }
            },

            uniqueCheckDocs(e) {
                this.uniqueCheck(this.formData.contractDocumentation, e.target.value);
            },

            uniqueCheckFrequency(e) {
                this.uniqueCheck(this.formData.statementFrequency, e.target.value);
            },

            uniqueCheck(collection, value) {
                collection.splice(0, collection.length);
                collection.push(value);
            },

            previousView() {
                this.$emit('fail');
            }

        },

        data() {
            let commMethodEmail = this.initCommunicationDetails.preferredComsMethodEmail;
            const commMethodSms = this.initCommunicationDetails.preferredComsMethodSms;
            const commMethodNormal = this.initCommunicationDetails.preferredComsMethodNormal;

            if (!commMethodEmail && !commMethodSms && !commMethodNormal) {
                commMethodEmail = 1;
            }

            const defaultContractDoc = this.initCommunicationDetails.contractDocumentation ? this.initCommunicationDetails.contractDocumentation : '0';
            const defaultStatementFq = this.initCommunicationDetails.statementFrequency ? this.initCommunicationDetails.statementFrequency : '0';

            return {
                formData: {
                    preferredComsMethodEmail: commMethodEmail,
                    preferredComsMethodSms: commMethodSms,
                    preferredComsMethodNormal: commMethodNormal,
                    contractDocumentation: [defaultContractDoc],
                    statementFrequency: [defaultStatementFq]
                }
            }
        },

        components: {
            appSelect,
            appTooltip,
            appMessages
        }

    })
</script>