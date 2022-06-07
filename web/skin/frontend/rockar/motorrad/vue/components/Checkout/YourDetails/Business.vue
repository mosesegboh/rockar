<template>
    <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
    <div class="form-header checkout-details-disclaimer">
        {{{ gdprDetailsDisclaimer }}}
    </div>
    <div class="top-text">
        {{{ topText | cmsBlock }}}<br>
    </div>
    <form id="{{ formId }}" method="post">
        <input type="hidden" name="is_business" :value="1" />
        <section class="your-details-section">
            <div class="messages">
                <div class="warning-msg">{{ 'Please provide your company data' | translate }}</div>
            </div>
            <div class="row">
                <div class="col-3">
                    <label class="side-label required" for="company_type">{{ 'Company Type' | translate }}</label>
                </div>
                <div class="col-9">
                    <div class="input-box residence-country">
                        <app-select
                            id='company_type-select'
                            :valid="true"
                            :options="companyTypeOptions"
                            :init-selected="getCompanyTypeInitSelection()"
                            custom-event='YourDetailsBusiness::companyTypeSelected'
                        ></app-select>
                        <input id='company_type' class="required-entry" type="hidden" name='company_type' :value="quoteData.company_type">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <label class="side-label required" for="company_name">{{ 'Company Name' | translate }}</label>
                </div>
                <div class="col-9">
                    <div class="input-box">
                        <input class="required-entry" id="company_name" type="text" name="company_name" :value="quoteData.company_name" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <label class="side-label required" for="registration_number">{{ 'Registration Number' | translate }}</label>
                </div>
                <div class="col-9">
                    <div class="input-box">
                        <input class="required-entry uppercase" id="registration_number" type="text" name="registration_number" :value="quoteData.registration_number" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <label class="side-label required" for="vat_number">{{ 'VAT Number' | translate }}</label>
                </div>
                <div class="col-9">
                    <div class="input-box">
                        <input class="required-entry uppercase" id="vat_number" type="text" name="vat_number" :value="quoteData.vat_number" />
                    </div>
                </div>
            </div>
            <div class="row your-details-submit section-action-buttons">
                <div class="col-3">&nbsp;</div>
                <div class="col-9">
                    <button class="button-dark" @click="submit($event)">{{ 'Save and Continue' | translate }}</button>
                </div>
            </div>
        </section>
    </form>
</template>

<script>
    import VueValidator from 'vue-validator';
    Vue.use(VueValidator);

    import yourDetailsHelpers from 'core/components/Checkout/YourDetails/helpers';
    import appSelect from 'core/components/Elements/Select';
    import appFormValidation from 'core/utils/FormValidation';
    import appTooltip from 'core/components/Elements/Tooltip';

    export default Vue.extend({
        props: {
            quoteData: {
                required: false,
                type: Object
            },
            topText: {
                required: false,
                type: String,
                default: false
            },
            companyFormUrl: {
                required: true,
                type: String
            },
            companyTypes: {
                required: true,
                type: Array
            },
            gdprDetailsDisclaimer: {
                required: false,
                type: String,
                default: ''
            }
        },

        data() {
            return {
                ajaxLoading: false,
                form: null,
                formId: 'company_form',
                changedName: {
                    checked: false,
                    value: 1
                }
            }
        },

        methods: {
            submit(e) {
                e.preventDefault();
                const $form = this.form;

                if ($form.valid()) {
                    this.ajaxLoading = true;
                    this.$http({
                        url: this.companyFormUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data: jQuery.extend(this.collectFormData(), { step: this.$parent.$parent.getNextStepIndex() })
                    }).then(this.submitSuccess, this.submitFail);
                }
            },

            collectFormData() {
                const $form = this.form;
                const formData = {};

                $form.find('input, select').each((i, el) => {
                    const key = jQuery(el).attr('name');

                    if (jQuery(el).is(':checkbox')) {
                        if (jQuery(el).is(':checked')) {
                            formData[key] = 1;
                        } else {
                            formData[key] = 0
                        }
                    } else {
                        formData[key] = jQuery(el).val();
                    }
                });

                return formData;
            },

            updateValidation() {
                new appFormValidation();
            },

            submitSuccess(response) {
                this.ajaxLoading = false;
                const data = response.data;

                if (data && !data.error) {
                    this.$dispatch('CheckoutAccordionGroup::nextStep', this.$parent.$parent.stepCode);
                }

                if (!data) {
                    this.displayError('There was an error with your request');
                }

                if (data && data.error) {
                    this.displayError(data.message);
                }
            },

            submitFail(resp) {
                this.ajaxLoading = false;
                this.displayResponseError(resp);
            },

            displayError(message, element) {
                if (element) {
                    element.after(`<div class="validation-advice" style="display: block;">${message}</div>`);
                } else {
                    alert(message);
                }
            },

            displayResponseError(resp) {
                if (resp.data.redirect && resp.status === 401) {
                    this.ajaxLoading = false;
                    this.$root.loggedOutPopup(resp.data.redirect);
                    return;
                }
                if (resp.data.redirect && resp.status !== 401) {
                    this.ajaxLoading = false;
                    window.location.href = resp.data.redirect;
                    return;
                }
                if (resp.data.error) {
                    alert(resp.data.error);
                    return;
                }

                alert(resp.statusText);
            },

            createSelects() {
                if (!this.$parent) {
                    return;
                }

                jQuery(this.$parent.$el).find('select').each((i, el) => {
                    const select = jQuery(el);
                    let disabled = false;

                    if (select.is(':disabled')) {
                        disabled = true;
                    }

                    new Select(select, false, disabled, 4);
                });
            },

            saveStep() {
                this.$dispatch('CheckoutAccordionGroup::saveStep', this.$parent.$parent.stepCode);
            },

            getCompanyTypeInitSelection() {
                const index = this.companyTypeOptions.findIndex(e => e.value === this.quoteData.company_type);
                return index && index !== -1 ? index : 0;
            }
        },

        computed: {
            companyTypeOptions() {
                const parsedData = [];

                // Only parse data with expected format and values
                this.companyTypes.filter(
                    e => e.value && e.label
                ).forEach((type, index) => {
                    parsedData[index] = {
                        'title': type.label,
                        'value': type.value
                    };
                });

                return parsedData;
            },
        },

        components: {
            appSelect,
            appTooltip
        },

        events: {
            'YourDetailsBusiness::companyTypeSelected'(companyType) {
                this.quoteData.company_type = companyType.value;
            }
        },

        ready() {
            this.form = jQuery(`#${this.formId}`);
            this.updateValidation();

            setTimeout(() => {
                this.createSelects();
            }, 1);
        }
    });
</script>
