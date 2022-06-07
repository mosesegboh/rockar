<template>
    <div class="personal-details my-account-wrapper">

        <!-- Details block -->
        <div v-show="state.default" class="details-block">
            <div class="col-9 col-md-12">
                <div class="content-wrapper">
                    <div :class="(!bothAddressesAreEqual && splitAddresses) ? 'col-4 col-md-12' : 'col-6 col-md-12'">
                        <div class="my-account-header">
                            <div class="my-account-heading-left h-common">
                                {{ 'Contact details' | translate }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-5 col-md-6 details-label"><p>{{ 'Name' | translate }}</p></div>
                            <div class="col-7 col-md-6"><p>{{ fullName }}</p></div>
                        </div>

                        <div class="row">
                            <div class="col-5 col-md-6 details-label"><p>{{ 'Email Address' | translate }}</p></div>
                            <div class="col-7 col-md-6"><p>{{ customer.email }}</p></div>
                        </div>

                        <div class="row">
                            <div class="col-5 col-md-6 details-label"><p>{{ 'Mobile Number' | translate }}</p></div>
                            <div class="col-7 col-md-6"><p>{{ customer.primary_number }}</p></div>
                        </div>

                        <div class="row" v-show="dob.full">
                            <div class="col-5 col-md-6 details-label"><p>{{ 'Date of Birth' | translate }}</p></div>
                            <div class="col-7 col-md-6"><p>{{ dobFull }}</p></div>
                        </div>
                    </div>

                    <div v-if="!bothAddressesAreEqual && splitAddresses">
                        <div v-show="billingAddress.id" class="address-fields col-4 col-md-6">
                            <div class="my-account-header">
                                <div class="my-account-heading-left h-common">
                                    {{ 'Billing Address' | translate }}
                                </div>
                            </div>

                            <address>
                                {{ billingAddress.street_1 }}<br>
                                <template v-if="billingAddress.street_2">{{ billingAddress.street_2 }}<br></template>
                                <template v-if="billingAddress.street_3">{{ billingAddress.street_3 }}<br></template>
                                {{ billingAddress.city }}, {{ billingAddress.region }}<br>
                                {{ getCountryName(billingAddress.country) }}<br>
                                {{ billingAddress.postcode }}
                            </address>
                        </div>

                        <div v-show="shippingAddress.id" class="address-fields col-4 col-md-6">
                            <div class="my-account-header">
                                <div class="my-account-heading-left h-common">
                                    {{ 'Shipping Address' | translate }}
                                </div>
                            </div>

                            <address>
                                {{ shippingAddress.street_1 }}<br>
                                <template v-if="shippingAddress.street_2">{{ shippingAddress.street_2 }}<br></template>
                                <template v-if="shippingAddress.street_3">{{ shippingAddress.street_3 }}<br></template>
                                {{ shippingAddress.city }}, {{ shippingAddress.region }}<br>
                                {{ getCountryName(shippingAddress.country) }}<br>
                                {{ shippingAddress.postcode }}
                            </address>
                        </div>
                    </div>

                    <div v-else>
                        <div v-show="billingAddress.id" class="col-6 col-md-12">
                            <div class="my-account-header">
                                <div class="my-account-heading-left h-common">
                                    {{ 'Address' | translate }}
                                </div>
                            </div>

                            <address>
                                {{ billingAddress.street_1 }}<br>
                                <template v-if="billingAddress.street_2">{{ billingAddress.street_2 }}<br></template>
                                <template v-if="billingAddress.street_3">{{ billingAddress.street_3 }}<br></template>
                                {{ billingAddress.city }}, {{ billingAddress.region }}<br>
                                {{ getCountryName(billingAddress.country) }}<br>
                                {{ billingAddress.postcode }}
                            </address>
                        </div>
                    </div>
                </div>
            </div>

            <div class="details-buttons col-3 col-md-12">
                <button class="button button-empty button-narrow edit-details" @click.prevent="updateState('details', 'toggle')">
                    {{ 'Edit my details' | translate }}
                </button>
            </div>
        </div>

        <!-- Details edit block -->
        <div v-show="state.details" class="edit-block">
            <validator name="mydetails" :classes="{ invalid: 'validation-error' }">
                <form class="row" :action="editForm" method="post" @submit="submitForm" v-el:contact-details-form>
                    <input type="hidden" name="form_key" :value="formKey">

                    <div class="row">
                        <!-- Edit details-->
                        <div class="col-6 col-md-12">
                            <div class="my-account-header">
                                <p class="my-account-heading h-common">{{ 'Edit contact details' | translate }}</p>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <label class="side-label required" for="prefix">
                                        {{ 'Title' | translate }}
                                    </label>
                                </div>

                                <div class="col-8">
                                    <app-select
                                            @select="changeCustomerPrefix"
                                            title="-"
                                            :init-selected="customer.prefix"
                                            :valid="$mydetails.customerPrefix.valid"
                                            :disabled="false"
                                            :options="createSelect(false, false, this.prefixOptions)">
                                    </app-select>
                                    <input type="hidden" v-model="customer.prefix" v-validate:customer-prefix="['required']" id="prefix" name="customer[prefix]">

                                    <div class="validation-error-msg" v-if="!$mydetails.customerPrefix.valid">
                                        {{ 'This field is required.' | translate }}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4">
                                    <label class="side-label required" for="details_firstname">{{ 'First Name(s)' | translate }}</label>
                                </div>

                                <div class="col-8">
                                    <input class="required-entry keyboard-name" type="text" id="details_firstname" name="customer[firstname]" v-model="customer.firstname" initial="off" v-validate:customer-firstname="['required']">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4">
                                    <label class="side-label required" for="details_lastname">{{ 'Surname' | translate }}</label>
                                </div>

                                <div class="col-8">
                                    <input class="required-entry keyboard-name" type="text" id="details_lastname" name="customer[lastname]" v-model="customer.lastname" initial="off" v-validate:customer-lastname="['required']">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4">
                                    <div class="info-label-wrap edit-profile">
                                        <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                                            <span class="action-badge info-small" slot="tooltipElement"></span>

                                            <div slot="tooltipContent">
                                                <p>If you would like to update your email address, please contact BMW Customer Service on 080 060 0555.</p>
                                            </div>
                                        </app-tooltip>
                                    </div>
                                    <label class="side-label required" for="details-email">
                                        {{ 'Email Address' | translate }}
                                    </label>
                                </div>

                                <div class="col-8">
                                    <input
                                        class="required-entry keyboard-email"
                                        type="email"
                                        autocapitalize="off"
                                        autocorrect="off"
                                        id="details-email"
                                        name="customer[email]"
                                        v-model="customer.email"
                                        initial="off"
                                        v-validate:customer-email="['required', 'email']"
                                        disabled
                                    >
                                </div>
                            </div>

                            <div class="row" v-if="isAffiliateCodeEnabled && showAffiliateCodeField">
                                <div class="col-4">
                                    <div class="side-badge-block">
                                        <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                                            <span class="action-badge side-badge info-small" slot="tooltipElement"></span>

                                            <div slot="tooltipContent">
                                                <p>{{ affiliateCodeTooltipText }}</p>
                                            </div>
                                        </app-tooltip>
                                        <label for="affiliate_code" class="side-label required">{{ 'Affiliate Code' | translate }}</label>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="input-box">
                                        <input type="text" name="affiliate_code" autocomplete="off" :title="'Affiliate Code' | translate" id="affiliate_code" class="required-entry" initial="off" v-validate:affiliate-code="{ affiliatecodeexists: { rule: affiliateCodes } }"/>
                                    </div>
                                    <div class="validation-error-msg" v-if="!$mydetails.affiliateCode.valid">
                                        {{ 'Please, enter valid affiliate code.' | translate }}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4">
                                    <label class="side-label required" for="primary-number">{{ 'Mobile Number' | translate }}</label>
                                </div>

                                <div class="col-8">
                                    <input
                                        type="tel"
                                        id="primary-number"
                                        name="customer[primary_number]"
                                        v-model="customer.primary_number"
                                        v-validate:customer-primarynumber="{ mobile: [this.currentLocale, this.localeValidation[this.currentLocale].mobileRegEx] }"
                                        initial="off"
                                        @blur="mobileInputBlurManipulator"
                                        @keyup="fireManipulateMobile"
                                    >
                                    <div class="validation-error-msg" v-if="!$mydetails.customerPrimarynumber.valid">
                                        {{ phoneInvalidFormatErrorMessage | translate }}
                                    </div>
                                </div>
                            </div>

                            <div class="row" v-show="dob">
                                <div class="col-4">
                                    <label class="side-label" for="day">{{ 'Date of Birth' | translate }}</label>
                                </div>

                                <div class="col-8 customer-dob">
                                    <div class="input-box row-elements three-in-row">
                                        <div class="row-element">
                                            <app-select
                                                    @select="changeDobDay"

                                                    title="dd"
                                                    :init-selected="dob.day"
                                                    :valid="true"
                                                    :disabled="false"
                                                    :options="createSelect(false, true, this.dob.days)">
                                            </app-select>
                                            <input type="hidden" v-model="dob.day" id="day" name="customer[day]">
                                        </div>

                                        <div class="row-element">
                                            <app-select
                                                    @select="changeDobMonth"

                                                    title="mm"
                                                    :init-selected="dob.month"
                                                    :valid="true"
                                                    :disabled="false"
                                                    :options="createSelect(false, true, this.dob.months)">
                                            </app-select>
                                            <input type="hidden" v-model="dob.month" id="month" name="customer[month]">
                                        </div>

                                        <div class="row-element">
                                            <app-select
                                                    @select="changeDobYear"

                                                    title="yyyy"
                                                    :init-selected="dob.year"
                                                    :valid="true"
                                                    :disabled="false"
                                                    :options="createSelect(false, true, this.dob.years)">
                                            </app-select>
                                            <input type="hidden" v-model="dob.year" id="year" name="customer[year]">
                                        </div>
                                    </div>

                                    <input type="hidden" id="dob" name="customer[dob]" v-model="dobFull">

                                    <!--                                    <div class="validation-error-msg" v-if="!$mydetails.customerDob.valid">
                                                                            {{ 'Please enter proper Date of Birth.' | translate }}
                                                                        </div>-->
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-12 address-form">
                            <div class="billing-address">
                                <input type="hidden" name="address[id]" v-model="billingAddress.id">
                                <input type="hidden" name="address[default_billing]" :value="1">
                                <input type="hidden" name="address[default_shipping]" :value="splitAddresses ? 0 : 1">
                                <input type="hidden" name="address[firstname]" v-model="billingAddress.firstname">
                                <input type="hidden" name="address[lastname]" v-model="billingAddress.lastname">
                                <input type="hidden" name="address[telephone]" v-model="billingAddress.telephone">

                                <div class="my-account-header">
                                    <p class="my-account-heading h-common" v-if="!splitAddresses">{{ 'Address' | translate }}</p>
                                    <p class="my-account-heading h-common" v-else>{{ 'Billing Address' | translate }}</p>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label class="side-label required" for="street1-billing">{{ 'Address line 1' | translate }}</label>
                                    </div>

                                    <div class="col-8">
                                        <input class="required-entry" id="street1-billing" type="text" name="address[street][0]" v-model="billingAddress.street_1" initial="off" v-validate:customer-billing-street="['required']">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label class="side-label" for="street1-billing">{{ 'Address line 2' | translate }}</label>
                                    </div>

                                    <div class="col-8">
                                        <input id="street2-billing" type="text" name="address[street][1]" v-model="billingAddress.street_2">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label class="side-label required" for="region-billing">{{ 'Suburb' | translate }}</label>
                                    </div>

                                    <div class="col-8">
                                        <input
                                            class="required-entry"
                                            type="text"
                                            id="region-billing"
                                            name="address[region]"
                                            v-model="billingAddress.region"
                                            initial="off"
                                            v-validate:customer-billing-region="['required']"
                                        />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label class="side-label required" for="city-billing">{{ 'City' | translate }}</label>
                                    </div>

                                    <div class="col-8">
                                        <input
                                            class="required-entry"
                                            id="city-billing"
                                            type="text"
                                            name="address[city]"
                                            v-model="billingAddress.city"
                                            initial="off"
                                            v-validate:customer-billing-city="['required']"
                                        />
                                    </div>
                                </div>

                                <div class="row " v-show="false">
                                    <div class="col-4">
                                        <label class="side-label required" for="country-id-billing">{{ 'Country' | translate }}</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="customer-country">
                                            <app-select
                                                @select="changeCustomerBillingCountry"
                                                title="-"
                                                init-selected="ZA"
                                                :valid="$mydetails.customerBillingCountry.valid"
                                                :disabled="false"
                                                :options="createSelect(true, true, this.availableCountries, 'label', 'value')">
                                            </app-select>
                                            <input
                                                type="hidden"
                                                v-model="billingAddress.country"
                                                value="ZA"
                                                v-validate:customer-billing-country="['required']"
                                                name="address[country_id]"
                                                id="country-id-billing"
                                            />
                                            <div class="validation-error-msg" v-if="!$mydetails.customerBillingCountry.valid">
                                                {{ 'This field is required.' | translate }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label class="side-label required" for="postcode-billing">{{ 'Postcode' | translate }}</label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text"
                                               id="postcode-billing"
                                               name="address[postcode]"
                                               v-model="billingAddress.postcode"
                                               initial="off"
                                               v-validate:customer-billing-postcode="{ required: true, regex: this.localeValidation[this.currentLocale].postcodeNumber }"
                                        />
                                        <div class="validation-error-msg" v-if="!$mydetails.customerBillingPostcode.valid">
                                            {{ invalidPostcodeFormatValidationMessage | translate }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="splitAddresses" class="shipping-address">
                                <hr>

                                <input type="hidden" name="additionalAddress[id]" v-model="shippingAddress.id">
                                <input type="hidden" name="additionalAddress[default_billing]" :value="0">
                                <input type="hidden" name="additionalAddress[default_shipping]" :value="1">
                                <input type="hidden" name="additionalAddress[firstname]" v-model="shippingAddress.firstname">
                                <input type="hidden" name="additionalAddress[lastname]" v-model="shippingAddress.lastname">
                                <input type="hidden" name="additionalAddress[telephone]" v-model="shippingAddress.telephone">

                                <div class="my-account-header">
                                    <p class="my-account-heading h-common">{{ 'Shipping Address' | translate }}</p>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label class="side-label required" for="postcode-shipping">{{ 'Postcode' | translate }}</label>
                                    </div>

                                    <div class="col-8">
                                        <input class="required-entry" type="text" id="postcode-shipping" name="additionalAddress[postcode]" v-model="shippingAddress.postcode" initial="off" v-validate:customer-shipping-postcode="['required']">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label class="side-label required" for="street1-shipping">{{ 'Street Address' | translate }}</label>
                                    </div>

                                    <div class="col-8">
                                        <input class="required-entry" id="street1-shipping" type="text" name="additionalAddress[street][0]" v-model="shippingAddress.street_1" initial="off" v-validate:customer-shipping-street="['required']">
                                        <input id="street2-shipping" type="text" name="additionalAddress[street][1]" v-model="shippingAddress.street_2">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label class="side-label required" for="city-shipping">{{ 'City' | translate }}</label>
                                    </div>

                                    <div class="col-8">
                                        <input class="required-entry" id="city-shipping" type="text" name="additionalAddress[city]" initial="off" v-model="shippingAddress.city" v-validate:customer-shipping-city="['required']">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label class="side-label required" for="region-shipping">{{ 'Region' | translate }}</label>
                                    </div>

                                    <div class="col-8">
                                        <input class="required-entry" id="region-shipping" type="text" name="additionalAddress[region]" initial="off" v-model="shippingAddress.region" v-validate:customer-shipping-region="['required']">
                                    </div>
                                </div>

                                <div class="row ">
                                    <div class="col-4">
                                        <label class="side-label required" for="country_id-shipping">{{ 'Country' | translate }}</label>
                                    </div>

                                    <div class="col-8">
                                        <div class="customer-country">
                                            <app-select
                                                    @select="changeCustomerShippingCountry"

                                                    title="-"
                                                    :init-selected="shippingAddress.country"
                                                    :valid="$mydetails.customerShippingCountry.valid"
                                                    :disabled="false"
                                                    :options="createSelect(true, true, this.availableCountries, 'label', 'value')">
                                            </app-select>

                                            <input type="hidden" v-model="shippingAddress.country" v-validate:customer-shipping-country="['required']" name="additionalAddress[country_id]" id="country_id-shipping">

                                            <div class="validation-error-msg" v-if="!$mydetails.customerShippingCountry.valid">
                                                {{ 'This field is required.' | translate }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-6">
                        <div class="row personal-details-desktop">
                            <div class="col-8 shift-4">
                                <div class="button-wrapper row-elements two-in-row form-elements">
                                    <button class="row-element button-default confirm-details" type="submit">{{ 'Confirm' | translate }}</button>
                                    <button class="row-element button-empty cancel-details" @click.prevent="restoreState(['customer', 'address', 'dob'], 'default', 'toggle')">
                                        {{ 'Cancel' | translate }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row personal-details-mobile">
                        <div class="col-12">
                            <div class="button-wrapper row-elements two-in-row form-elements">
                                <button class="button button-default" type="submit">{{ 'Confirm' | translate }}</button>
                                <button class="button button-empty" @click.prevent="restoreState(['customer', 'address', 'dob'], 'default', 'toggle')">
                                    {{ 'Cancel' | translate }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </validator>
        </div>

        <!-- Password update block -->
        <div v-show="state.password" class="password-block">
            <div class="my-account-header">
                <p class="my-account-heading h-common h-small">
                    {{ 'Update password' | translate }}
                </p>
            </div>

            <div class="col-6 col-md-12">
                <form class="row" :action="passwordForm" method="post">
                    <input type="hidden" name="form_key" :value="formKey">

                    <div class="row">
                        <div class="col-3">
                            <label class="side-label required" for="current_password">{{ 'Old password' | translate }}</label>
                        </div>

                        <div class="col-9">
                            <input class="required-entry" type="password" id="current_password" name="current_password" autocomplete="off">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-3">
                            <label class="side-label required" for="password">{{ 'New password' | translate }}</label>
                        </div>

                        <div class="col-9">
                            <input class="validate-password required-entry" type="password" id="password" name="password" autocomplete="off">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-3">
                            <label class="side-label required" for="confirmation">{{ 'Confirm New password' | translate }}</label>
                        </div>

                        <div class="col-9">
                            <input class="validate-cpassword required-entry" type="password" id="confirmation" name="confirmation" autocomplete="off">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="button-wrapper row-elements two-in-row form-elements">
                                <button class="row-element button-default confirm-password" type="submit">
                                    {{ 'Confirm' | translate }}
                                </button>

                                <button class="row-element button-empty cancel-password" @click.prevent="restoreState(['customer', 'address', 'dob'], 'default', 'toggle')">
                                    {{'Cancel' | translate }}
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import VueValidator from 'vue-validator';
    Vue.use(VueValidator);

    import appFormValidation from 'core/utils/FormValidation';
    import appSelect from 'core/components/Elements/Select';
    import appTooltip from 'core/components/Elements/Tooltip';
    import appModal from 'core/components/Elements/Modal';
    import appGdprPreferences from 'core/components/Gdpr/Preferences';
    import currentLocale from 'motorrad/components/Shared/CurrentLocale';
    import uiVariables from 'motorrad/components/Shared/UIVariables';

    export default Vue.extend({
        mixins: [
            currentLocale,
            uiVariables
        ],

        props: {
            customer: {
                required: true,
                type: Object
            },
            notifications: {
                required: true,
                type: Object
            },
            gdprSaveUrl: {
                required: true,
                type: String
            },
            gdprChannels: {
                required: true,
                type: Array
            },
            gdprOptions: {
                required: true,
                type: Array
            },
            gdprCustomerOptions: {
                required: false,
                type: Array
            },
            billingAddress: {
                required: false,
                default() {
                    return {
                        id: false,
                        firstname: '',
                        lastname: '',
                        street_1: '',
                        street_2: '',
                        city: '',
                        region: '',
                        country: '',
                        postcode: '',
                        telephone: ''
                    }
                }
            },
            shippingAddress: {
                required: false,
                default() {
                    return {
                        id: false,
                        firstname: '',
                        lastname: '',
                        street_1: '',
                        street_2: '',
                        city: '',
                        region: '',
                        country: '',
                        postcode: '',
                        telephone: ''
                    }
                }
            },
            editForm: {
                required: false,
                type: String
            },
            notificationForm: {
                required: false,
                type: String
            },
            passwordForm: {
                required: false,
                type: String
            },
            formKey: {
                required: true,
                type: String
            },
            availableCountries: {
                required: true,
                type: Array
            },
            defaultCountry: {
                required: false,
                type: String
            },
            dob: {
                required: false,
                type: Object
            },
            prefixOptions: {
                required: true,
                type: Array
            },
            brand: {
                required: false,
                type: String
            },
            splitAddresses: {
                required: false,
                type: [Boolean, Number],
                default: false
            },

            isGroupsDropdownEnabled: {
                required: false,
                type: Boolean,
                default: false
            },

            customerGroups: {
                required: false,
                type: Array,
                default: []
            },

            isAffiliateCodeEnabled: {
                required: false,
                type: Boolean,
                default: false
            },

            affiliateCodes: {
                required: false,
                type: Array,
                default: []
            },

            affiliateCodeDropdownOption: {
                required: false,
                type: String
            },

            affiliateCodeTooltipText: {
                required: false,
                type: String
            }
        },

        data() {
            return {
                showPreferences: false,
                popupPaddingTop: 30,
                state: {
                    default: true,
                    details: false,
                    password: false
                },
                dobFull: null,
                originalData: {},
                showAffiliateCodeField: false,
            }
        },

        computed: {
            bothAddressesAreEqual() {
                if (this.shippingAddress.id && this.billingAddress.id
                    && this.shippingAddress.city === this.billingAddress.city
                    && this.shippingAddress.country === this.billingAddress.country
                    && this.shippingAddress.postcode === this.billingAddress.postcode
                    && this.shippingAddress.region === this.billingAddress.region
                    && this.shippingAddress.street_1 === this.billingAddress.street_1
                    && this.shippingAddress.street_2 === this.billingAddress.street_2) {
                    return true;
                } else {
                    return false;
                }
            },

            fullName() {
                var fullName = '';
                if (this.customer.prefix !== '') {
                    fullName += `${this.customer.prefix} `;
                }
                fullName += `${this.customer.firstname} ${this.customer.lastname}`;
                return fullName;
            },

            countryName() {
                var address = this.address;

                if (address.country) {
                    for (var i = 0; i < this.availableCountries.length; i++) {
                        if (this.availableCountries[i]
                            && this.availableCountries[i].value !== undefined
                            && this.availableCountries[i].value === address.country
                        ) {
                            return this.availableCountries[i].label;
                        }
                    }
                }

                return '';
            },

            computedCustomerGroups() {
                const customerGroups = this.customerGroups;
                if (this.isAffiliateCodeEnabled) {
                    customerGroups.push({ code: this.affiliateCodeDropdownOption, id: 'affiliate_code' });
                }

                return customerGroups;
            }
        },

        watch: {
            'billingAddress.postcode'() {
                jQuery('.address-form input').trigger('change');
            },

            'shippingAddress.postcode'() {
                jQuery('.address-form input').trigger('change');
            },

            'customer.firstname'() {
                this.billingAddress.firstname = this.customer.firstname;
                this.shippingAddress.firstname = this.customer.firstname;
            },

            'customer.lastname'() {
                this.billingAddress.lastname = this.customer.lastname;
                this.shippingAddress.lastname = this.customer.lastname;
            },

            'customer.primary_number'() {
                this.billingAddress.telephone = this.customer.primary_number;
                this.shippingAddress.telephone = this.customer.primary_number;
            }
        },

        methods: {
            submitForm(e) {
                this.$validate(false, () => {
                    if (!this.$mydetails.valid) {
                        e.preventDefault();
                    }
                });
            },

            getCountryName(country) {
                for (let i = 0; i < this.availableCountries.length; i++) {
                    if (this.availableCountries[i] && this.availableCountries[i].value !== undefined && this.availableCountries[i].value === country) {
                        return this.availableCountries[i].label;
                    }
                }

                return '';
            },

            changeDobFull() {
                this.dobFull = null;

                if (this.dob.month && this.dob.day && this.dob.year) {
                    this.dobFull = this.dob.format
                        .replace(/%[mb]/i, this.dob.month)
                        .replace(/%[de]/i, this.dob.day)
                        .replace(/%y/i, this.dob.year);
                }
            },

            changeCustomerPrefix(data) {
                this.customer.prefix = data.title;
            },

            changeCustomerGroup(data) {
                this.customer.group_id = data.value;
                this.showAffiliateCodeField = (data.value === 'affiliate_code');
            },

            changeDobDay(data) {
                this.dob.day = data.value;
                this.changeDobFull();
            },

            changeDobMonth(data) {
                this.dob.month = data.value;
                this.changeDobFull();
            },

            changeDobYear(data) {
                this.dob.year = data.value;
                this.changeDobFull();
            },

            changeCustomerShippingCountry(data) {
                this.shippingAddress.country = data.value;
            },

            changeCustomerBillingCountry(data) {
                this.billingAddress.country = data.value;
            },

            updateState(id, action) {
                switch (action) {
                    case 'show':
                        this.showState(id);
                        break;
                    case 'hide':
                        this.hideState(id);
                        break;
                    case 'toggle':
                    default:
                        this.toggleState(id);
                }
            },

            restoreState(id, stateToggle, action) {
                var ids = [id];
                if (typeof id === 'object') {
                    ids = id; // multiple ids
                }

                for (var i = 0; i < ids.length; i++) {
                    id = ids[i];
                    if (this[id] !== undefined) {
                        this[id] = JSON.parse(JSON.stringify(this.originalData[id])); // hack for cloning object
                    }
                    if (id === 'address') {
                        this.billingAddress = JSON.parse(JSON.stringify(this.originalData[id]));
                    }
                }

                this.updateState(stateToggle, action);
            },

            toggleState(id) {
                this.state.default = false;
                this.state.details = false;
                this.state.password = false;
                this.state[id] = true;
            },

            hideState(id) {
                this.state[id] = false;
            },

            showState(id) {
                this.state[id] = true;
            },

            getOriginalData() {
                return {
                    customer: this.customer,
                    address: this.billingAddress,
                    notifications: this.notifications,
                    dob: this.dob
                }
            },

            createSelect(isObject, removeFirst, list, keyLabel = false, keyValue = false) {
                var options = [];

                if (isObject) {
                    Object.keys(list).forEach((item) => {
                        if (keyLabel && keyValue) {
                            options.push({
                                title: list[item][keyLabel],
                                value: list[item][keyValue]
                            });
                        } else {
                            options.push({
                                title: list[item],
                                value: item
                            });
                        }
                    });
                } else {
                    list.forEach((item) => {
                        if (!keyLabel && !keyValue) {
                            options.push({
                                title: item,
                                value: String(item)
                            });
                        } else {
                            if (item[keyLabel] && item[keyValue]) {
                                options.push({
                                    title: item[keyLabel],
                                    value: item[keyValue]
                                });
                            }
                        }
                    });
                }

                if (removeFirst) {
                    options.shift();
                }

                return options;
            }
        },

        ready() {
            this.$root.detectMobile();
            this.$set('originalData', JSON.parse(JSON.stringify(this.getOriginalData()))); // hack for cloning object
            this.changeDobFull();

            pca.magento.fields.push({ Line1: 'street1-shipping', City: 'city-shipping', Line2: 'region-shipping', Postcode: 'postcode-shipping', CountrySelect: 'country-id-shipping' });
            pca.magento.fields.push({ Line1: 'street1-billing', City: 'city-billing', Line2: 'region-billing', Postcode: 'postcode-billing', CountrySelect: 'country-id-billing' });
            pca.magento.load();

            if (this.isAffiliateCodeEnabled && !this.isGroupsDropdownEnabled) {
                this.showAffiliateCodeField = true;
            }

            // Set values from visible form fields as they are expected to be saved
            this.billingAddress.firstname = this.customer.firstname;
            this.billingAddress.lastname = this.customer.lastname;
            this.billingAddress.telephone = this.customer.primary_number;
            this.shippingAddress.firstname = this.customer.firstname;
            this.shippingAddress.lastname = this.customer.lastname;
            this.shippingAddress.telephone = this.customer.primary_number;
        },

        components: {
            appSelect,
            appTooltip,
            appModal,
            appGdprPreferences
        }
    });
</script>
