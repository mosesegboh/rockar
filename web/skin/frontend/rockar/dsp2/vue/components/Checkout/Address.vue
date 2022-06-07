<template>
    <div class="section customer-address">
        <div
            class="general-preloader"
            v-show="ajaxLoading"
        >
            <div class="show-loading"></div>
        </div>

        <div class="register-block">
            <div class="row login-wrapper">
                <div class="form-header checkout-address-disclaimer">
                    <div class="step-header">
                        {{'Your Personal Details' | translate }}
                    </div>
                    <div class="col-12">
                        {{{ gdprAddressDisclaimer }}}
                    </div>
                </div>
                <div class="col-12 content">
                    <validator
                        name="address"
                        :classes="{ invalid: 'validation-error' }"
                    >
                        <form
                            class="row"
                            @submit="submitForm"
                            method="post"
                            v-el:form-address
                        >
                            <input
                                type="hidden"
                                name="form_key"
                                :value="formKey"
                            >

                            <div class="row">
                                <p>{{'I am buying as' | translate }}*</p>
                            </div>

                            <div class="row customer-type">
                                <div class="col-12">
                                    <div class="type">
                                        <input
                                            type="radio"
                                            class="radio-checkbox small-business-check"
                                            id="individual"
                                            value="individual"
                                            v-model="businessPicker"
                                            checked
                                        >
                                        <label for="individual"><span></span>{{ 'An individual' | translate }}</label>

                                        <span class="order-cap-tooltip" :class="{ 'show' : !isBusiness && remainingRequestAmount > 0 }">
                                        <p class="coupon-label-text">({{ 'You can have up to ' | translate }} {{ remainingRequestAmount }} {{ ' active orders at a time' | translate }})</p>
                                    </span>
                                    </div>
                                    <div class="type">
                                        <input
                                            type="radio"
                                            class="radio-checkbox small-business-check"
                                            id="business"
                                            value="business"
                                            v-model="businessPicker"
                                        >
                                        <label for="business"><span></span>{{'A company' | translate }}</label>
                                    </div>
                                    <input
                                        type="hidden"
                                        name="customer_type"
                                        value="{{ businessPicker }}"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="label">
                                        <label
                                            class="required side-label checkout-name-label"
                                            for="first_name"
                                        >{{ 'First Name(s)' | translate }}</label>
                                    </div>
                                    <input
                                        type="text"
                                        initial="off"
                                        id="first_name"
                                        name="first_name"
                                        v-model="formData.firstname"
                                        v-validate:first-name="['required']"
                                    />
                                    <div
                                        class="validation-error-msg"
                                        v-if="!$address.firstName.valid"
                                    >
                                        {{ 'This field is required.' | translate }}
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="label">
                                        <label
                                            class="required side-label checkout-name-label"
                                            for="last_name"
                                        >{{ 'Surname' | translate }}</label>
                                    </div>
                                    <input
                                        type="text"
                                        initial="off"
                                        id="last_name"
                                        name="last_name"
                                        v-model="formData.lastname"
                                        v-validate:last-name="['required']"
                                    />
                                    <div
                                        class="validation-error-msg"
                                        v-if="!$address.lastName.valid"
                                    >
                                        {{ 'This field is required.' | translate }}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="label">
                                        <label
                                            class="required side-label"
                                            for="country_of_citizenship"
                                        >{{ 'Country of Citizenship' | translate }}</label>
                                    </div>
                                    <div class="input-box customer-country">
                                        <app-select
                                            @select="changeCountryCitizenship"
                                            @change="changeCountryCitizenship"
                                            title="-"
                                            :init-selected="formData.country_of_citizenship"
                                            id="country_of_citizenship_select"
                                            :disabled="false"
                                            :options="createSelect(false, allCountries, 'label', 'value')"
                                        >
                                        </app-select>
                                        <input
                                            type="hidden"
                                            initial="off"
                                            v-model="formData.country_of_citizenship"
                                            name="country_of_citizenship"
                                            id="country_of_citizenship"
                                            v-validate:country-of-citizenship="['required']"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.countryOfCitizenship.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="label">
                                        <label
                                            class="required side-label"
                                            for="south_african_document_type"
                                        >
                                            {{ 'ID Type' | translate }}
                                        </label>
                                    </div>
                                    <div class="input-box id-document-type">
                                        <app-select
                                            @select="changeIdType"
                                            @change="changeIdType"
                                            title="-"
                                            :init-selected="formData.south_african_document_type"
                                            id="south_african_document_type_select"
                                            :disabled="false"
                                            :options="createSelect(false, southAfricanDocumentTypes, 'label', 'value')"
                                        >
                                        </app-select>
                                        <input
                                            type="hidden"
                                            initial="off"
                                            v-validate:south-african-document-type="['required']"
                                            v-model="formData.south_african_document_type"
                                            name="south_african_document_type"
                                            id="south_african_document_type"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.southAfricanDocumentType.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="label">
                                        <label
                                            class="required side-label"
                                            for="south_african_id_number"
                                        >
                                            {{ isSAID ? 'ID Number' : 'Passport Number' | translate }}</label>
                                    </div>
                                    <input
                                        initial="off"
                                        v-validate:south-african-id-number="{ required: true, minlength: 1,
                                            maxlength: 100, said: [formData.south_african_document_type, saidTypeID] }"
                                        type="text"
                                        id="south_african_id_number"
                                        name="south_african_id_number"
                                        v-model="formData.south_african_id_number"
                                        @blur="checkDateOfBirth()"
                                    />
                                    <div
                                        class="validation-error-msg"
                                        v-if="!$address.southAfricanIdNumber.valid"
                                    >
                                        {{ 'This field is required and ID Number should be valid.' | translate }}
                                    </div>
                                </div>
                                <div class="col-6 date" v-show="dob">
                                    <div class="label">
                                        <label
                                            class="required side-label main-label"
                                            for="dob"
                                        >{{ 'Date of Birth' | translate }}
                                        </label>
                                    </div>
                                    <div class="customer-dob">
                                        <div class="input-box row-elements three-in-row">
                                            <div class="row-element">
                                                <label
                                                    class="side-label sub-label"
                                                    for="day"
                                                >{{ 'Day' | translate }}
                                                </label>
                                                <app-select
                                                    @select="changeDobDay"
                                                    title="dd"
                                                    id="day"
                                                    :init-selected="formData.dob.day"
                                                    :valid="$address.dobDay.valid"
                                                    :disabled="isSAID"
                                                    :options="createSelect(false, dob.days)"
                                                >
                                                </app-select>
                                                <input
                                                    type="hidden"
                                                    v-model="formData.dob.day"
                                                    id="day"
                                                    name="day"
                                                    initial="off"
                                                    v-validate:dob-day="['required', 'number']"
                                                >
                                            </div>

                                            <div class="row-element">
                                                <label
                                                    class="side-label sub-label"
                                                    for="month"
                                                >{{ 'Month' | translate }}
                                                </label>
                                                <app-select
                                                    @select="changeDobMonth"
                                                    title="mm"
                                                    id="month"
                                                    :init-selected="formData.dob.month"
                                                    :valid="$address.dobMonth.valid"
                                                    :disabled="isSAID"
                                                    :options="createSelect(false, dob.months)"
                                                >
                                                </app-select>
                                                <input
                                                    type="hidden"
                                                    v-model="formData.dob.month"
                                                    id="month"
                                                    name="month"
                                                    initial="off"
                                                    v-validate:dob-month="['required', 'number']"
                                                >
                                            </div>

                                            <div class="row-element last-child">
                                                <label
                                                    class="side-label sub-label"
                                                    for="year"
                                                >{{ 'Year' | translate }}
                                                </label>
                                                <app-select
                                                    @select="changeDobYear"
                                                    title="yyyy"
                                                    id="year"
                                                    :init-selected="formData.dob.year"
                                                    :valid="$address.dobYear.valid"
                                                    :disabled="isSAID"
                                                    :options="createSelect(false, dob.years)"
                                                >
                                                </app-select>
                                                <input
                                                    type="hidden"
                                                    v-model="formData.dob.year"
                                                    id="year"
                                                    name="year"
                                                    initial="off"
                                                    v-validate:dob-year="['required', 'number']"
                                                >
                                            </div>
                                            <input
                                                type="hidden"
                                                id="dob"
                                                name="dob"
                                                v-model="dobFull"
                                                initial="off"
                                                v-validate:dob="{ dob: [minAge, formData.dob.format], required: true }"
                                            >
                                        </div>
                                        <div
                                            class="validation-error-msg"
                                            v-if="$address.dob.required"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.dob.required && $address.dob.dob"
                                        >
                                            {{ 'Must be' | translate }} {{ minAge }} {{ 'years or over.' | translate }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="label">
                                        <label
                                            class="required side-label"
                                            for="primary_number"
                                        >
                                            {{ 'Mobile Number' | translate }}
                                        </label>
                                    </div>
                                    <input
                                        @blur="mobileInputBlurManipulator"
                                        @keyup="fireManipulateMobile"
                                        type="tel"
                                        initial="off"
                                        v-validate:primary-number="{
                                            mobile: [this.currentLocale, this.localeValidation[this.currentLocale].mobileRegEx]
                                        }"
                                        id="primary_number"
                                        name="primary_number"
                                        v-model="formData.primary_number"
                                    />
                                    <div
                                        class="validation-error-msg"
                                        v-if="$address.primaryNumber.mobile"
                                    >
                                        {{ 'Please enter a valid phone number.' | translate }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="isBusiness">
                                <div class="row">
                                    <div class="col-6">
                                        <label
                                            class="required side-label"
                                        >{{ 'Company Name' | translate }}
                                        </label>
                                        <input
                                            type="text"
                                            name="company[name]"
                                            v-validate:company-name-required="{required: true}"
                                            initial="off"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.companyNameRequired.valid"
                                        >
                                            {{ requiredFieldErrorMessage | translate}}
                                        </div>

                                    </div>
                                    <div class="col-6">
                                        <label
                                            class="required side-label"
                                            for="company-type"
                                        >{{ 'Company Type' | translate }}
                                        </label>
                                        <app-select
                                            @select="changeCompanyType"
                                            title="-"
                                            :init-selected="formData.company_type"
                                            id="company-type"
                                            :options="createSelect(false, companyType, 'label', 'value')"
                                            :valid="$address.companyTypeRequired.valid"
                                        >
                                        </app-select>
                                        <input
                                            type="hidden"
                                            v-model="validationError.companyType"
                                            id="company-type"
                                            name="company[type]"
                                            v-validate:company-type-required="['required']"
                                            initial="off"
                                        >
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.companyTypeRequired.valid"
                                        >
                                            {{ requiredFieldErrorMessage | translate}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label
                                                class="required side-label"
                                        >{{ 'Registration Number' | translate }}
                                        </label>
                                        <input
                                                type="text"
                                                name="company[registration_number]"
                                                v-validate:company-registration-number="{ required: true }"
                                                initial="off"
                                        />
                                        <div
                                                class="validation-error-msg"
                                                v-if="!$address.companyRegistrationNumber.valid"
                                        >
                                            {{ 'Registration number is required.' | translate}}
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label
                                            class="side-label"
                                        >{{ 'VAT Number' | translate }}
                                        </label>
                                        <input
                                            type="text"
                                            name="company[vat_number]"
                                            v-validate:company-vat-number="{ regexorempty: this.localeValidation[this.currentLocale].vatNumber }"
                                            initial="off"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.companyVatNumber.valid"
                                        >
                                            {{ 'Please enter a valid tax number.' | translate}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label
                                            class="required side-label"
                                            for="designation"
                                        >{{ 'Designation' | translate }}
                                        </label>
                                        <app-select
                                            @select="changeDesignation"
                                            title="-"
                                            :init-selected="formData.designation"
                                            id="designation"
                                            :options="createSelect(false, designation, 'label', 'value')"
                                        >
                                        </app-select>
                                        <input
                                            type="hidden"
                                            v-model="validationError.designation"
                                            id="designation"
                                            name="company[designation]"
                                            v-validate:company-designation="{ required: true }"
                                            initial="off"
                                        >
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.companyDesignation.valid"
                                        >
                                            {{ requiredFieldErrorMessage | translate }}
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label
                                            class="required side-label"
                                        >{{ 'Contact Number' | translate }}
                                        </label>
                                        <input
                                            type="text"
                                            name="company[contact_number]"
                                            v-validate:company-contact-number="{ mobile: [this.currentLocale, this.localeValidation[this.currentLocale].mobileOrLandLine] }"
                                            initial="off"
                                            @blur="mobileInputBlurManipulator"
                                            @keyup="fireManipulateMobile"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.companyContactNumber.valid"
                                        >
                                            {{ phoneInvalidFormatErrorMessage | translate }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="label">
                                        <label
                                            class="required side-label"
                                            for="street1"
                                        >{{ isBusiness ?  'Physical Address 1' : 'Residential Address 1' | translate }}
                                        </label>
                                    </div>
                                    <div v-if="!isBusiness">
                                        <input
                                            initial="off"
                                            v-validate:street0="['required']"
                                            id="street1"
                                            type="text"
                                            name="address[street][0]"
                                            v-model="formData.streets[0]"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.street0.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                    <div v-else>
                                        <input
                                            initial="off"
                                            v-validate:street0="['required']"
                                            id="bus-street1"
                                            type="text"
                                            name="address[street][0]"
                                            v-model="streets0FormData"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.street0.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="label">
                                        <label
                                            class="required side-label"
                                            for="street2"
                                        >{{ isBusiness ?  'Physical Address 2' : 'Residential Address 2' | translate }}
                                        </label>
                                    </div>
                                    <div v-if="!isBusiness">
                                        <input
                                            initial="off"
                                            v-validate:street1="['required']"
                                            id="street2"
                                            type="text"
                                            name="address[street][1]"
                                            v-model="formData.streets[1]"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.street1.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                    <div v-else>
                                        <input
                                            initial="off"
                                            v-validate:street1="['required']"
                                            id="bus-street2"
                                            type="text"
                                            name="address[street][1]"
                                            v-model="streets1FormData"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.street1.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="label">
                                        <label
                                            class="required side-label"
                                            for="region"
                                        >{{ 'Suburb' | translate }}</label>
                                    </div>
                                    <div v-if="!isBusiness">
                                        <input
                                            initial="off"
                                            v-validate:region="['required']"
                                            id="region"
                                            type="text"
                                            name="address[region]"
                                            v-model="formData.region"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.region.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                    <div v-else>
                                        <input
                                            initial="on"
                                            v-validate:region="['required']"
                                            id="bus-region"
                                            type="text"
                                            name="address[region]"
                                            v-model="regionFormData"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.region.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="label">
                                        <label
                                            class="required side-label"
                                            for="city"
                                        >{{ 'City' | translate }}</label>
                                    </div>
                                    <div v-if="!isBusiness">
                                        <input
                                            initial="off"
                                            v-validate:city="['required']"
                                            type="text"
                                            id="city"
                                            name="address[city]"
                                            v-model="formData.city"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.city.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                    <div v-else>
                                        <input
                                            initial="off"
                                            v-validate:city="['required']"
                                            type="text"
                                            id="bus-city"
                                            name="address[city]"
                                            v-model="cityFormData"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.city.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="label">
                                        <label
                                            class="required side-label"
                                            for="postcode"
                                        >{{ 'Postal Code' | translate }}
                                        </label>
                                    </div>
                                    <div v-if="!isBusiness">
                                        <input
                                            initial="off"
                                            v-validate:postcode="{ required: true, regex: this.localeValidation[this.currentLocale].postcodeNumber }"
                                            type="text"
                                            id="postcode"
                                            name="address[postcode]"
                                            v-model="formData.postcode"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.postcode.valid"
                                        >
                                            {{ invalidPostcodeFormatValidationMessage | translate }}
                                        </div>
                                    </div>
                                    <div v-else>
                                        <input
                                            initial="off"
                                            v-validate:postcode="{ required: true, regex: this.localeValidation[this.currentLocale].postcodeNumber }"
                                            type="text"
                                            id="bus-postcode"
                                            name="address[postcode]"
                                            v-model="postcodeFormData"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.postcode.valid"
                                        >
                                            {{ invalidPostcodeFormatValidationMessage | translate }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-show="false">
                                <div class="col-12">
                                    <div class="label">
                                        <label
                                            class="required side-label"
                                            for="country-id"
                                        >{{ 'Country' | translate }}</label>
                                    </div>
                                    <div class="input-box customer-country" v-if="!isBusiness">
                                        <app-select
                                            @select="changeCountry"
                                            title="-"
                                            id="address[country_id]"
                                            name="address[country_id]"
                                            :init-selected="formData.country_id"
                                            :valid="$address.country.valid"
                                            :disabled="false"
                                            :options="createSelect(false, this.availableCountries, 'label', 'value')"
                                        >
                                        </app-select>
                                        <input
                                            type="hidden"
                                            v-model="formData.country_id"
                                            initial="off"
                                            v-validate:country="['required']"
                                            name="address[country_id]"
                                            value="ZA"
                                            id="country-id"
                                        >
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.country.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                    <div class="input-box customer-country" v-else>
                                        <app-select
                                            @select="changeCountry"
                                            title=" "
                                            id="address-country-id"
                                            :init-selected=""
                                            name="address[country_id]"
                                            :valid="$address.country.valid"
                                            :disabled="false"
                                            :options="createSelect(false, this.availableCountries, 'label', 'value')"
                                        >
                                        </app-select>
                                        <input
                                            type="hidden"
                                            initial="off"
                                            v-validate:country="['required']"
                                            name="address[country_id]"
                                            id="bus-country-id"
                                            v-model="formData.country_id"
                                        >
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.country.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="row additional-delivery-address"
                                v-if="multipleAddressesEnabled"
                            >
                                <div class="col-12">
                                    <input
                                        type="checkbox"
                                        id="additional_address"
                                        v-model="additionalAddress"
                                        name="set_additional_address"
                                    />
                                    <label for="additional_address"><span></span>
                                        {{ 'Would you like your car delivered to your work address?' | translate }}
                                    </label>
                                </div>
                            </div>
                            <!--Additional address-->
                            <div
                                class="section customer-address"
                                v-if="multipleAddressesEnabled && additionalAddress"
                                transition="pxVrm"
                            >
                                <div
                                    class="general-preloader"
                                    v-show="ajaxLoading"
                                >
                                    <div class="show-loading"></div>
                                </div>
                                <!-- Checkout Your Address block -->
                                <div class="register-block">
                                    <div class="row login-wrapper">
                                        <!-- Address form -->
                                        <div class="col-12">
                                            <div class="row">
                                                <div class="col-12">
                                                    <label
                                                        class="required side-label"
                                                        for="additional_postcode"
                                                    >
                                                        {{ 'Postcode' | translate }}</label>
                                                    <input
                                                        initial="off"
                                                        v-validate:postcode-additional="['required']"
                                                        type="text"
                                                        id="additional_postcode"
                                                        name="address[additional][postcode]"
                                                        v-model="formData.additional_address.postcode"
                                                    />
                                                    <div
                                                        class="validation-error-msg"
                                                        v-if="!$address.postcodeAdditional.valid"
                                                    >
                                                        {{ 'This field is required.' | translate }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <label
                                                        class="required side-label"
                                                        for="additional_street1"
                                                    >
                                                        {{ 'Street Address' | translate }}</label>

                                                    <input
                                                        initial="off"
                                                        v-validate:street-additional="['required']"
                                                        id="additional_street1"
                                                        type="text"
                                                        name="address[additional][street][0]"
                                                        v-model="formData.additional_address.streets[0]"
                                                    />
                                                    <div
                                                        class="validation-error-msg"
                                                        v-if="!$address.streetAdditional.valid"
                                                    >
                                                        {{ 'This field is required.' | translate }}
                                                    </div>
                                                    <input
                                                        id="additional_street2"
                                                        type="text"
                                                        name="address[additional][street][1]"
                                                        v-model="formData.additional_address.streets[1]"
                                                    />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <label
                                                        class="required side-label"
                                                        for="additional_city"
                                                    >
                                                        {{ 'City' | translate }}
                                                    </label>
                                                    <input
                                                        initial="off"
                                                        v-validate:city-additional="['required']"
                                                        id="additional_city"
                                                        type="text"
                                                        name="address[additional][city]"
                                                        v-model="formData.additional_address.city"
                                                    />
                                                    <div
                                                        class="validation-error-msg"
                                                        v-if="!$address.cityAdditional.valid"
                                                    >
                                                        {{ 'This field is required.' | translate }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <label
                                                        class="required side-label"
                                                        for="additional_region"
                                                    >
                                                        {{ 'Region' | translate }}
                                                    </label>
                                                    <input
                                                        initial="off"
                                                        v-validate:region-additional="['required']"
                                                        type="text"
                                                        id="additional_region"
                                                        name="address[additional][region]"
                                                        v-model="formData.additional_address.region"
                                                    />
                                                    <div
                                                        class="validation-error-msg"
                                                        v-if="!$address.regionAdditional.valid"
                                                    >
                                                        {{ 'This field is required.' | translate }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <label
                                                        class="required side-label"
                                                        for="additional_country_id"
                                                    >
                                                        {{ 'Country' | translate }}
                                                    </label>
                                                    <div class="input-box customer-country">
                                                        <app-select
                                                            @select="changeCountryAdditional"
                                                            title="-"
                                                            id="additional_address[country_id]"
                                                            :init-selected="formData.additional_address.country_id"
                                                            :valid="$address.countryAdditional.valid"
                                                            :disabled="false"
                                                            :options="createSelect(false, availableCountries,'label', 'value')"
                                                        >
                                                        </app-select>
                                                        <input
                                                            type="hidden"
                                                            v-model="formData.additional_address.country_id"
                                                            initial="off"
                                                            v-validate:country-additional="['required']"
                                                            name="address[additional][country_id]"
                                                            id="additional_country_id"
                                                        >
                                                        <div
                                                            class="validation-error-msg"
                                                            v-if="!$address.countryAdditional.valid"
                                                        >
                                                            {{ 'This field is required.' | translate }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--Additional address end-->
                            <h3 v-if="isBusiness">{{ 'Postal Address' | translate }}</h3>
                            <div class="row" v-if="isBusiness">
                                <div class="col-12 same-address">
                                    <input
                                        type="checkbox"
                                        id="postal-same-as-residential"
                                        @change="setPostalValues"
                                        name="postal_same_as_residential"
                                    />
                                    <label for="postal-same-as-residential"><span></span>
                                        {{ 'My postal address is the same as my physical address' | translate }}
                                    </label>
                                </div>
                            </div>
                            <div class="row" v-if="isBusiness">
                                <div class="col-12">
                                    <label :class="cssClassForRequiredLabel">
                                        {{ 'Address Line 1' | translate }}
                                    </label>
                                    <div v-if="postalSameAsChecked">
                                        <input
                                            initial="off"
                                            v-validate:postal-street="['required']"
                                            type="text"
                                            v-model="streets0FormData"
                                            name="postal[street][0]"
                                            disabled
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.postalStreet.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                    <div v-else>
                                        <input
                                            initial="off"
                                            v-validate:postal-street="['required']"
                                            type="text"
                                            name="postal[street][0]"
                                            id="bus-post-street1"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.postalStreet.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="row" v-if="isBusiness">
                                <div class="col-12">
                                    <label class="side-label">
                                        {{ 'Address Line 2' | translate }}
                                    </label>
                                    <div v-if="postalSameAsChecked">
                                        <input
                                            type="text"
                                            v-model="streets1FormData"
                                            name="postal[street][1]"
                                            disabled
                                        />
                                    </div>
                                    <div v-else>
                                        <input
                                            type="text"
                                            name="postal[street][1]"
                                            id="bus-post-street2"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-if="isBusiness">
                                <div class="col-12">
                                    <label :class="cssClassForRequiredLabel">
                                        {{ 'Suburb' | translate }}
                                    </label>
                                    <div v-if="postalSameAsChecked">
                                        <input
                                            initial="off"
                                            v-validate:postal-suburb="['required']"
                                            type="text"
                                            v-model="regionFormData"
                                            name="postal[region]"
                                            disabled
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.postalSuburb.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                    <div v-else>
                                        <input
                                            initial="off"
                                            v-validate:postal-suburb="['required']"
                                            type="text"
                                            name="postal[region]"
                                            id="bus-post-region"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.postalSuburb.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-if="isBusiness">
                                <div class="col-6">
                                    <label :class="cssClassForRequiredLabel">
                                        {{ 'City' | translate }}
                                    </label>
                                    <div v-if="postalSameAsChecked">
                                        <input
                                            initial="off"
                                            v-validate:postal-city="['required']"
                                            type="text"
                                            v-model="cityFormData"
                                            name="postal[city]"
                                            disabled
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.postalCity.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                    <div v-else>
                                        <input
                                            initial="off"
                                            v-validate:postal-city="['required']"
                                            type="text"
                                            name="postal[city]"
                                            id="bus-post-city"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.postalCity.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <label :class="cssClassForRequiredLabel">
                                        {{ 'Postal Code' | translate }}
                                    </label>
                                    <div v-if="postalSameAsChecked">
                                        <input
                                            initial="off"
                                            v-validate:postal-postcode="['required']"
                                            type="text"
                                            v-model="postcodeFormData"
                                            name="postal[postcode]"
                                            disabled
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.postalPostcode.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                    <div v-else>
                                        <input
                                            initial="off"
                                            v-validate:postal-postcode="['required']"
                                            type="text"
                                            name="postal[postcode]"
                                            id="bus-post-code"
                                        />
                                        <div
                                            class="validation-error-msg"
                                            v-if="!$address.postalPostcode.valid"
                                        >
                                            {{ 'This field is required.' | translate }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="buttons-set">
                                        <div class="button-section">
                                            <button
                                                type="submit"
                                                class="button dsp2-money"
                                            >
                                                {{ 'Save and continue' | translate }}
                                            </button>
                                        </div>
                                        <div class="label">
                                            <p
                                                class="required"
                                                aria-required="true"
                                            >* {{ 'indicates a required field' | translate }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </validator>
                </div>
            </div>
        </div>
    </div>
    <app-modal class="order-cap-popup" :show.sync="showOrderCapPopup" :show-close="true" >
        <div slot="content">
            <div v-html="htmlEntityDecode(customerOrderCapBlock)"></div>
            <div class="row align-right order-cap-popup-buttons">
                <button class="button button-narrow button-grey" @click="getBackToPDP()">{{ 'Back' | translate }}</button>
                <button class="button button-narrow popup-continue" @click="redirectToMA()">{{ 'My Account' | translate }}</button>
            </div>
        </div>
    </app-modal>
</template>

<script>
    import appCheckoutAddress from 'core/components/Checkout/Address';
    import yourDetailsHelpers from 'dsp2/components/Checkout/YourDetails/helpers';
    import currentLocale from 'dsp2/components/Shared/CurrentLocale';
    import uiVariables from 'dsp2/components/Shared/UIVariables';
    import appTooltip from 'core/components/Elements/Tooltip';
    import appModal from 'core/components/Elements/Modal';
    import EventTracker from 'dsp2/mixins/EventTracker';

    export default appCheckoutAddress.extend({
        mixins: [
            yourDetailsHelpers,
            currentLocale,
            uiVariables,
            EventTracker
        ],

        props: {
            southAfricanDocumentTypes: {
                required: true,
                type: Array
            },
            allCountries: {
                required: true,
                type: Array
            },
            companyType: {
                required: true,
                type: Array
            },
            designation: {
                required: true,
                type: Array
            },
            customerType: {
                type: String,
                required: false,
                default: 'individual'
            },
            customerOrderCapBlock: {
                required: true,
                type: String
            },
            myAccountUrl: {
                required: true,
                type: String
            },
            orderCaps: {
                required: true,
                type: Object
            },
            activeOrders: {
                required: true,
                type: String
            },
            productUrl: {
                required: true,
                type: String
            }
        },

        data() {
            return {
                postalSameAsChecked: false,
                saidNumberIsValid: true,
                minAge: 17,
                primaryPhoneNumberIsValid: true,
                secondaryPhoneNumberIsValid: true,
                businessPicker: this.customerType,
                isSAID: true,
                postal: {
                    address1: null,
                    address2: null,
                    region: null,
                    city: null,
                    postalCode: null
                },
                validationError: {
                    designation: null,
                    companyType: null
                },
                showOrderCapPopup: false
            }
        },

        watch: {
            'businessPicker': {
                handler(newV) {
                    this.$store.commit('setBusinessPicker', newV);
                    this.customerType = newV;
                    pca.magento.load();
                }
            },

            saidNumber: {
                immediate: true,
                handler(newValue) {
                    this.emitIdNumberIfValid(newValue);
                }
            },

            'checkoutAddressAccordionShow'(newValue) {
                if (newValue) {
                    /**
                     * Fire event for tracking purposes on initial load of personal details
                     */
                    this.fireEventForTracking(
                        this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                        this.getEventConstants().EVENTRACKERVALUES.CHECKOUTDETAILS
                    );
                }
            }
        },

        methods: {
            submitForm(e) {
                e.preventDefault();
                this.showOrderCapPopup = this.remainingRequestAmount <= 0;

                this.$validate(false, () => {
                    if (this.$address.valid && !this.showOrderCapPopup) {
                        const data = jQuery.extend(this.collectFormData(jQuery(this.$els.formAddress)),
                            { step: this.$parent.getNextStepIndex() });
                        this.ajaxLoading = true;
                        this.$http({
                            url: this.formUrl,
                            method: 'POST',
                            emulateJSON: true,
                            data
                        }).then(this.submitSuccess, this.submitFail, this.$dispatch('Main::progressUpdateFinanceQuote'));
                    }
                });
            },

            /**
             * Manages formData.south_african_document_type select
             * @param {Object} data instance of select data
             */
            changeIdType(data) {
                this.formData.south_african_document_type = data.value;
                this.isSAID = data.value === this.saidTypeID;
            },

            /**
             * Checks for DOB from ID number and whether person is older than 100 years
             */
            checkDateOfBirth() {
                const currentYear = new Date().getFullYear(),
                    year = (currentYear.toString()).substring(2, 4);

                let century = '19';
                let dobYear = this.formData.south_african_id_number.substring(0, 2);

                const dobMonth = this.formData.south_african_id_number.substring(2, 4),
                    dobDay = this.formData.south_african_id_number.substring(4, 6);

                if (this.isSAID) {
                    if (this.formData.south_african_id_number.substring(0, 2) < year) {
                        century = '20';
                    }

                    dobYear = century + this.formData.south_african_id_number.substring(0, 2);
                    this.formData.dob.year = dobYear;
                    this.formData.dob.month = dobMonth;
                    this.formData.dob.day = dobDay;
                    this.changeDobFull();
                }
            },

            /**
             * Manages formData.country_of_citizenship select
             * @param {Object} data instance of select data
             */
            changeCountryCitizenship(data) {
                this.formData.country_of_citizenship = data.value;
            },

            /**
             * Manages formData.company_type select
             * @param {Object} data instance of select data
             */
            changeCompanyType(data) {
                this.formData.company_type = data.value;
                this.validationError.companyType = data.value;
            },

            /**
             * Manages formData.designation select
             * @param {Object} data instance of select data
             */
            changeDesignation(data) {
                this.$nextTick(() => {
                    this.formData.designation = data.value;
                    this.validationError.designation = data.value;
                });
            },
            /**
             *  sets or unsets the postal address values based on the selection made by the user
             * @param {event} the event object passed by the change event
             */
            setPostalValues(eve) {
                this.postalSameAsChecked = eve.target.checked;
            },

            /**
             * centralised method to emit the id number as captured by the user.
             * Done in this way because the user can close the page at any point
             * items down the chain still need to know the users identity number
             * now we can fire this on the watch and onready.
             * Used nextTick so we can access the validation object.
             * @param {String} valToSend, the id number that is sent down the chain.
             */
            emitIdNumberIfValid(valToSend) {
                if (valToSend) {
                    this.$nextTick((isIdentity = this.isSAID, valid = this.$address.southAfricanIdNumber.valid) => {
                        if (isIdentity && valid) {
                            this.$root.$emit('Address::SA_ID_NUMBER_SET', valToSend);
                            this.checkDateOfBirth();
                        }
                    });
                }
            },

            htmlEntityDecode(encodedHtml) {
                return jQuery('<textarea />').html(encodedHtml).text();
            },

            getBackToPDP() {
                if (this.productUrl) {
                    window.location.href = this.productUrl;
                }
            },

            redirectToMA() {
                window.location.href = this.myAccountUrl;
            },
        },

        computed: {
            /**
             * South African ID document type key, required for validation because SAID numbers are to be validated
             * while Passport numbers is not validated.
             * @type {number}
             */
            saidTypeID() {
                const southAfricanType = Object.values(this.southAfricanDocumentTypes).find(item =>
                    item.label.indexOf('SAID') > -1 || item.label.indexOf('SA ID') > -1);

                return southAfricanType ? southAfricanType.value : 0;
            },

            isBusiness() {
                return this.businessPicker === 'business';
            },

            saidNumber() {
                return this.isSAID ? this.formData.south_african_id_number : '';
            },

            remainingRequestAmount() {
                let orderCapToCompare = 0;

                if (this.orderCaps) {
                    orderCapToCompare = this.isBusiness ? this.orderCaps.corporate_cap : this.orderCaps.individual_cap;
                }

                return orderCapToCompare - this.activeOrders;
            },

            checkoutAddressAccordionShow() {
                return this.$root.$refs.checkoutAddressAccordion.show;
            }
        },

        ready() {
            // Initial check on pre-populated ID type
            if (this.formData.south_african_document_type !== null) {
                this.isSAID = this.formData.south_african_document_type === this.saidTypeID;
            }
            pca.magento.fields.push({ Line1: 'bus-street1', City: 'bus-city', Line2: 'bus-region', Postcode: 'bus-postcode', CountrySelect: 'bus-country-id' });
            pca.magento.fields.push({ Line1: 'bus-post-street1', City: 'bus-post-city', Line2: 'bus-post-region', Postcode: 'bus-post-code', CountrySelect: 'bus-country-id' });
            pca.magento.fields.push({ Line1: 'street1', City: 'city', Line2: 'region', Postcode: 'postcode', CountrySelect: 'country-id' });
            pca.magento.load();
            this.emitIdNumberIfValid(this.formData.south_african_id_number);
        },

        components: {
            appModal,
            appTooltip
        }
    });
</script>
