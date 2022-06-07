<template>
    <div class="finance-credit">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <br>
        <validator name="summary">
            <div class="row">
                <div class="col-12">
                    <input
                        type="checkbox"
                        id="terms-cond"
                        v-model="termsCond"
                        v-validate:terms="{required: { rule: true, initial:'off'}}"
                    >
                    <label for="terms-cond"><span></span>
                        <div class="accept-terms-statement">{{ 'By accepting the' | translate }}</div>
                        <a
                            href="javascript:void(0);"
                            @click.prevent="this.$root.openInModal('termsConditionsModal')"
                            class="terms-conditions">{{'Terms & Conditions' | translate}}
                        </a>
                        <div class="accept-terms-statement">
                            {{ ', you confirm that you have read and understood the Terms and Conditions. Further, you consent to and agree that ' +
                            'BMW Financial Services (South Africa) (Pty) Ltd will process your information as described in the' | translate }}
                            <a href="https://www.bmw.co.za/en/footer/metanavigation/legal-disclaimer-pool/privacy-statement.html"
                               target="_blank"
                               class="terms-conditions">{{'Privacy Statement' | translate}}
                            </a>
                        </div>
                    </label>
                    <div class="validation-advice" v-if="!$summary.terms.valid">{{ acceptTCSValidationMessage | translate }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p class="accept-terms">{{ 'By continuing with this application I confirm that:' | translate }}</p>
                    <ol class="confirm">
                        <li>{{ 'I am not a minor' | translate }}</li>
                        <li>{{ 'I have never been declared mentally unfit by a court.' | translate }}</li>
                        <li>{{ 'I am not subject to an Administration Order.' | translate }}</li>
                        <li>{{ 'I do not have any current applications pending for debt restructuring or alleviation.' | translate }}</li>
                        <li>{{ 'I do not have any current debt re-arrangement in existence.' | translate }}</li>
                        <li>{{ 'I have not previously applied for a debt re-arrangement.' | translate }}</li>
                        <li>{{ 'I am not under sequestration.' | translate }}</li>
                        <li>{{ 'I do not have applications pending for credit, nor open quotations as envisaged in section 92 of the National Credit Act.' | translate }}</li>
                        <li>{{ 'I am a South African citizen or have a valid permit to live and work in South Africa.' | translate }}</li>
                        <li>{{ 'I have a valid driver\'s license (South African or International)' | translate }}</li>
                    </ol>
                </div>
            </div>
            <div class="row">
                <div class="col-12 accept-terms padBottom">
                    <input
                        type="checkbox"
                        id="accept-info"
                        v-model="acceptInfo"
                        v-validate:info-correct="{ required: { rule: true, initial:'off' }}"
                    >
                    <label for="accept-info">
                        <span></span>
                        <p class="accept-terms-statement">{{ 'I accept that the information provided is true, accurate, current and complete.' | translate }}</p>
                    </label>
                    <div class="validation-advice" v-if="!$summary.infoCorrect.valid">{{ acceptTCSValidationMessage | translate }}</div>
                </div>
            </div>
            <div class="row your-details-submit section-action-buttons">
                <div class="col-6">
                    <button class="button button-empty" @click="previousView">{{ 'Back' | translate }}</button>
                    <div class="empty" v-else>&nbsp</div>
                </div>
                <div class="col-6">
                    <button class="button left button-dark" @click="formSubmit">
                        {{ 'Save and Continue' | translate }}
                    </button>
                </div>
            </div>
        </validator>
    </div>
</template>

<script>
    import appSelect from 'core/components/Elements/Select';
    import yourDetailsHelpers from 'core/components/Checkout/YourDetails/helpers';
    import appMessages from 'core/components/Elements/Messages';
    import translateString from 'core/filters/Translate';
    import timeSpanSelector from 'motorrad/components/Elements/TimeSpanSelector';
    import uiVariables from 'motorrad/components/Shared/UIVariables';

    export default Vue.extend({
        mixins: [
            yourDetailsHelpers,
            uiVariables
        ],

        methods: {
            translateString,

            mapFormData() {
                const obj = {};

                return obj;
            },

            formSubmit() {
                this.$validate(false, () => {
                    if (this.$summary.valid) {
                        this.$emit('success', this.mapFormData());
                    }
                });
            },
            previousView() {
                this.$emit('fail');
            }
        },

        data() {
            return {
                termsCond: null,
                acceptInfo: null
            }
        },

        components: {
            appSelect,
            appMessages,
            timeSpanSelector
        }

    })
</script>
