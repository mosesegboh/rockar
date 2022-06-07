<template>
    <div id="you-drive" class="you-drive" :class="currentState == state.MODEL ? 'first-step' : ''">
        <div class="top-navigation-wrapper">
            <app-navigation-steps
                    :requested-step="getActiveStep"
                    :is-rebooking="options.isRebooking"
            ></app-navigation-steps>
        </div>

        <div class="outer-container" id="youdrive-navi">
            <div class="row inner-container">
                <div class="general-preloader" v-show="ajaxLoading || ajaxLoadingNotAvailableDates">
                    <div class="show-loading"></div>
                </div>
                <div class="col-md-12 col-12">

                    <slot name="global-messages"></slot>
                    <slot name="messages"></slot>

                    <div class="step-one" v-show="currentState == state.MODEL">
                        <div class="row" v-show="youdriveCars.length > 0">
                            <h1 class="you-drive-step-title">
                                {{ options.youdrivePageTitle }}
                            </h1>
                        </div>

                        <div class="row" v-if="youdriveCars.length > 0">
                            <div id="model-filter">
                                <div class="model-filter-carousel">
                                    <app-carousel-model
                                            :slides="youdriveCars"
                                            general-class="car"
                                            :checkboxes="true"
                                            :options="generateOptions()"
                                            :select-limit="options.maxCarsToBook"
                                            :pre-selected-slide="preSelectedModel"
                                            v-ref:car-carousel
                                    ></app-carousel-model>
                                </div>
                            </div>
                        </div>
                        <div class="row" v-else>
                            <p class="you-drive-section-heading">
                                {{ options.youdrivePageMessage }}
                            </p>
                        </div>
                    </div>

                    <div class="step-two" v-show="currentState == state.DEALER && !ajaxLoadingNotAvailableDates">
                        <input
                            type="hidden"
                            id="country_id_td"
                            value="ZA"
                        >
                        <input
                            type="hidden"
                            id="postcode_td"
                            value=""
                        >
                        <div class="location-wrapper" v-show="dealerStateStep === 1">
                            <div class="row">
                                <div>
                                    <h3>{{ 'Find your closest retailer' | translate }}</h3>
                                </div>
                                <div class="postcode-input">
                                    <div class="inline-submit-button">
                                        <input
                                            type="search"
                                            id="street_td"
                                            placeholder="{{ 'Enter your address' | translate }}"
                                            @keyup.enter="submitPostcodeAndContinue()"
                                        >
                                        <button class="submit-button button-default" @click="submitPostcodeAndContinue()">
                                            {{ 'Search' | translate }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="map-ui-wrapper" v-show="dealerStateStep === 2 && showMapWrapper">
                            <div class="dealer-list" v-el:dealer-list>
                                <template v-for="(index, store) in storesSorted" track-by="$index">
                                    <div
                                            class="dealer-preview-wrapper"
                                            :class="{ active: localStore.id === store.id, recommended: store.recommended }"
                                            @click="selectDealer(index)"
                                    >
                                        <div class="dealer-preview">
                                            <div class="number">
                                                <span>{{ index + 1 }}</span>
                                            </div>

                                            <div class="details">
                                                <div class="name">{{ store.name }}</div>
                                                <div class="address" v-html="storeFormattedAddress(store)"></div>
                                                <div class="rating-distance-wrapper">
                                                    <div class="distance">{{ store.distanceFormatted ? store.distanceFormatted : '' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="dealers row">
                                <div class="dealer-info">
                                    <template v-for="store in storesSorted">
                                        <div class="dealer-box" v-if="localStore.id === store.id">
                                            <app-models
                                                    :models.sync="store.vehicles"
                                                    :store-id="store.id"
                                                    :selected-models-titles="selectedModelTitles"
                                                    :selected-vehicles="selectedVehicles"
                                                    :select-models-limit="parseInt(selectModelsLimit)"
                                                    :available-ids="availableModelIds(store)"
                                                    :local-store-code="localStore.code"
                                                    :local-store.sync="localStore"
                                            ></app-models>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <app-maps
                                    :js-api="true"
                                    :locations="storesSorted"
                                    :api-key="options.mapApiKey"
                                    :home="getHomeLocation()"
                                    @update-stores-data="updateStoresData"
                                    :centred-on-user="true"
                                    v-ref:stores-map
                            ></app-maps>
                        </div>
                    </div>

                    <div class="step-three-no-inherit" v-show="currentState == state.DATE">
                        <div class="you-drive-date">
                            <div class="row">
                                <h1 class="you-drive-step-title">{{ 'Choose your Date and Time' | translate }}</h1>
                            </div>
                            <div class="row">
                                <ul class="messages" v-if="showCalendarErrorMessage">
                                    <li class="error-msg">
                                        <ul>
                                            <li>
                                                <span>{{ calendarErrorMessage }}</span>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                                <div class="col-8 col-md-6 drive-time">
                                    <app-timepicker
                                            :available-times-url="options.availableTimesUrl"
                                            :ajax-loading.sync="ajaxLoading"
                                            :local-store="localStore"
                                            :selected-model-data="selectedModelData"
                                            :request-data="getAvailableTimesAdditionalRequestData"
                                            identifier="you_drive_calendar"
                                            :vehicle-ids="selectedVehicles"
                                            :first-available-date-url="options.firstAvailableDateUrl"
                                    >
                                    </app-timepicker>
                                </div>

                                <div class="col-4 col-md-6">
                                    <app-summary
                                            :current-state="currentState"
                                            :selected-model-data='selectedModelData'
                                            :ajax-loading.sync='ajaxLoading'
                                            :local-store='localStore'
                                            :cancel-booking-url='options.cancelBookingUrl'
                                            :test-drive-title='options.testDriveTitle'
                                            v-ref:you-drive-summary
                                    ></app-summary>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="step-three-no-inherit" v-show="currentState == state.CONFIRMATION">
                        <div class="row">
                            <ul class="messages" v-show="validationError != false">
                                <li class="error-msg"><span>{{ validationError }}</span></li>
                            </ul>
                            <div class="form-header you-drive-disclaimer">
                                <div class="col-12">
                                    <h1 class="you-drive-step-title">
                                        {{ `${options.testDriveTitle} Booking` | translate }}
                                    </h1>
                                </div>
                                <div class="col-12" v-if="this.isTDRequest()" v-html="htmlEntityDecode(getintouchBlock)"></div>
                                <div class="col-12" v-else v-html="htmlEntityDecode(gdprDisclaimer)"></div>
                            </div>
                        </div>
                        <div class="you-drive-signup">
                            <div class="col-8 col-md-12 drive-signup-body">
                                <div class="row">
                                    <div class="col-12">
                                        <p class="h2 section-title">{{ 'Personal Details' | translate }}</p>
                                    </div>
                                    <div class="col-12">
                                        <validator name="testdrive" :classes="{ invalid: 'validation-error' }">
                                            <form action="#" method="post" id="book-test-drive" v-el:booking-form>
                                                <input type="hidden" name="form_key" :value="options.formKey">

                                                <div class="fieldset">
                                                    <div class="col-10 col-md-12">
                                                        <div class="row">
                                                            <label class="required side-label">{{ 'Title' | translate }}</label>
                                                        </div>
                                                        <div class="row">
                                                            <div class="input-box customer-title">
                                                                <app-select
                                                                        @select="changeCustomerPrefix"
                                                                        title="-"
                                                                        id="prefix"
                                                                        :init-selected="customer.prefix"
                                                                        :valid="$testdrive.prefix.valid"
                                                                        :disabled="false"
                                                                        :options="createSelect(false, false, this.prefixes)">
                                                                </app-select>
                                                                <input type="hidden" v-model="customer.prefix"
                                                                       initial="off" v-validate:prefix="['required']"
                                                                       name="prefix">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-5 col-md-6 col-sm-12">
                                                            <div class="row">
                                                                <label class="required side-label">{{ 'First Name' | translate }}</label>
                                                            </div>
                                                            <div class="row">
                                                                <div class="input-box">
                                                                    <input type="text" name="firstname"
                                                                        class="required-entry"
                                                                        :value="customer.firstname"
                                                                        v-model="customer.firstname"
                                                                        :title="'First Name' | translate" initial="off"
                                                                        v-validate:firstname="['required']">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-5 col-md-6 col-sm-12">
                                                            <div class="row">
                                                                <label class="required side-label">{{ 'Surname' | translate }}</label>
                                                            </div>
                                                            <div class="row">
                                                                <div class="input-box">
                                                                    <input type="text" name="lastname"
                                                                        class="required-entry"
                                                                        :value="customer.lastname"
                                                                        v-model="customer.lastname"
                                                                        :title="'Surname' | translate" initial="off"
                                                                        v-validate:lastname="['required']">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-5 col-md-6 col-sm-12">
                                                            <div class="row">
                                                                <label for="primary_number" class="side-label required">
                                                                    {{'Mobile Number' | translate }}
                                                                </label>
                                                            </div>
                                                            <div class="row">
                                                                <input type="tel"
                                                                    @blur="mobileInputBlurManipulator"
                                                                    @keyup="fireManipulateMobile"
                                                                    minlength="8"
                                                                    maxlength="15"
                                                                    name="primary_number"
                                                                    id="primary_number"
                                                                    :value="customer.primary_phone_number"
                                                                    :title="'Mobile Number' | translate"
                                                                    initial="off"
                                                                    v-validate:primary-phone="{ required: true, mobile: [this.currentLocale, this.localeValidation[this.currentLocale].mobileRegEx] }"
                                                                    v-model="customer.primary_phone_number"/>
                                                                <div class="validation-error-msg"
                                                                    v-if="!$testdrive.primaryPhone.valid">
                                                                    {{ 'Please enter a valid phone number.' | translate }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-5 col-md-6 col-sm-12">
                                                            <div class="row details-email">
                                                                <div class="info-label-wrap edit-profile">
                                                                    <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                                                                        <span class="action-badge info-small" slot="tooltipElement"></span>
                                                                        <div slot="tooltipContent">
                                                                            <p>{{ options.emailInfoText | translate }}</p>
                                                                        </div>
                                                                    </app-tooltip>
                                                                </div>
                                                                <label class="side-label required" for="details-email">
                                                                    {{ 'Email Address' | translate }}
                                                                </label>
                                                            </div>
                                                            <div class="row">
                                                                <input
                                                                    class="required-entry keyboard-email"
                                                                    type="email"
                                                                    autocapitalize="off"
                                                                    autocorrect="off"
                                                                    id="details-email"
                                                                    name="email"
                                                                    :value="customer.email"
                                                                    initial="off"
                                                                    v-validate:customer-email="['required', 'email']"
                                                                    disabled
                                                                >
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="row">
                                                            <label class="required side-label" for="dob">{{ 'Date of Birth' | translate }}</label>
                                                        </div>
                                                        <div class="row customer-dob">
                                                            <div class="input-box row-elements three-in-row">
                                                                <div class="dob-full" style="display:none;">
                                                                    <input type="hidden" id="dob" name="dob"
                                                                           initial="off"
                                                                           v-validate:dob="{ dob: [minAge, this.customer.dob.format], required: true }"
                                                                           v-model="dobFull">
                                                                </div>

                                                                <div class="row-element">
                                                                    <app-select
                                                                            @select="changeDobDay"
                                                                            title="dd"
                                                                            id="day"
                                                                            :init-selected="customer.dob.day"
                                                                            :valid="$testdrive.dobDay.valid && $testdrive.dob.valid"
                                                                            :disabled="false"
                                                                            :options="createSelect(false, true, this.customer.dob.days)">
                                                                    </app-select>
                                                                    <input type="hidden" v-model="customer.dob.day"
                                                                           id="day" name="day" initial="off"
                                                                           v-validate:dob-day="['required', 'number']">
                                                                </div>

                                                                <div class="row-element">
                                                                    <app-select
                                                                            @select="changeDobMonth"
                                                                            title="mm"
                                                                            id="month"
                                                                            :init-selected="customer.dob.month"
                                                                            :valid="$testdrive.dobMonth.valid && $testdrive.dob.valid"
                                                                            :disabled="false"
                                                                            :options="createSelect(false, true, this.customer.dob.months)">
                                                                    </app-select>
                                                                    <input type="hidden" v-model="customer.dob.month"
                                                                           id="month" name="month" initial="off"
                                                                           v-validate:dob-month="['required', 'number']">
                                                                </div>

                                                                <div class="row-element">
                                                                    <app-select
                                                                            @select="changeDobYear"
                                                                            title="yyyy"
                                                                            id="year"
                                                                            :init-selected="customer.dob.year"
                                                                            :valid="$testdrive.dobYear.valid && $testdrive.dob.valid"
                                                                            :disabled="false"
                                                                            :options="createSelect(false, true, this.customer.dob.years)">
                                                                    </app-select>
                                                                    <input type="hidden" v-model="customer.dob.year"
                                                                           id="year" name="year" initial="off"
                                                                           v-validate:dob-year="['required', 'number']">
                                                                </div>
                                                            </div>

                                                            <div class="validation-error-msg"
                                                                 v-if="!$testdrive.dob.valid">
                                                                {{ 'Must be' | translate }} {{ minAge }} {{ 'years or over.' | translate }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </validator>
                                    </div>
                                    <div class="description">
                                        <div class="col-12" v-html="personalDetailsBlock"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 col-md-12">
                                <app-summary  v-show="!this.isTDRequest()"
                                    :current-state="currentState"
                                    :selected-model-data='selectedModelData'
                                    :ajax-loading.sync='ajaxLoading'
                                    :local-store='localStore'
                                    :cancel-booking-url='options.cancelBookingUrl'
                                    :test-drive-title='options.testDriveTitle'
                                    v-ref:you-drive-summary
                                ></app-summary>
                            </div>
                        </div>
                    </div>

                    <div class="step-four" v-show="currentState == state.SUMMARY">
                        <div class="you-drive-confirmation">
                            <div class="you-drive-confirmation-header" v-if="this.isTDRequest()">
                                <div class="row">
                                    <div class="col-12 col-md-12">
                                        <h1 class="drive-confirmation-heading">{{'Your enquiry has been sent!' | translate }}</h1>
                                        <p>{{ 'Your selected Retailer will contact you and confirm your request.' | translate }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="you-drive-confirmation-header" v-else>
                                <div class="row">
                                    <div class="col-12 col-md-12">
                                        <h1 class="drive-confirmation-heading">{{'Your booking is confirmed!' | translate }}</h1>
                                        <p class="confirmed-text">{{ 'A confirmation email has been sent to your e-mail address.' | translate }}</p>
                                        <p>{{ 'You can view and amend your booking in your account.' | translate }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row confirmation-block">
                                <h4 class="summary-title">
                                    <span class="youdrive-title" v-if="this.isTDRequest()">{{ 'Retailer Details' | translate }}</span>
                                    <span class="youdrive-title" v-else>{{ 'Your Appointment' | translate }}</span>
                                </h4>
                                <div class="drive-car local-store">
                                    <p class="store-name">
                                        <span class="summary-title location">{{ 'Location' | translate }}:</span>
                                        {{ localStore.name }}
                                    </p>
                                    <p class="store-name" v-if="localStore.street">
                                        <span class="summary-title location">{{ 'Address' | translate }}:</span>
                                        {{ localStore.street }}
                                    </p>
                                    <p class="date-time" v-show="!this.isTDRequest()">
                                        <span class="summary-title location">{{ 'Date' | translate }}:</span>
                                        {{ selectedModelData.bookingDatetime.format('dddd Do MMMM') }}
                                    </p>
                                    <p class="date-time" v-show="!this.isTDRequest()">
                                        <span class="summary-title location">{{ 'Time' | translate }}:</span>
                                        {{ selectedModelData.bookingDatetime.format('h:mm a') }}
                                    </p>
                                </div>

                                <div class="your-test-drive" v-if="selectedModelData.carId">
                                    <div class="selected-car-wrapper">
                                        <div class="selected-car-data" v-if="selectedModelData.id">
                                            <h4 class="selected-car-title">{{selectedModelData.title}}</h4>
                                            <p class="selected-car-subtitle">{{selectedModelData.subtitle}}</p>
                                            <div class="selected-car-image">
                                                <img :src="selectedModelData.image" :alt="selectedModelData.title">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="your-test-drive-disclaimer">
                                <slot name="youdrive-online-booking-disclaimer"></slot>
                            </div>

                            <div class="row your-test-drive-next-step">
                                <div>
                                    <h4 class="next-step-title" v-html="htmlEntityDecode(bookanotherBlock)"></h4>
                                    <div class="next-step-statement" v-html="htmlEntityDecode(nextStepStatementBlock)"></div>
                                </div>
                                <div class="row your-test-drive-next-buttons">
                                    <div class="col-6 col-sm-12">
                                        <a class="button button-dark" :href="options.homeUrl">{{ 'Back to home' | translate }}</a>
                                    </div>
                                    <div class="col-6 col-sm-12">
                                        <a class="button button-dark" :href="options.testDriveUrl">{{ 'Start a new booking' | translate }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="youdrive-bottom-disclaimer" v-show="currentState == state.MODEL">
            <div v-html="htmlEntityDecode(howitworksBlock)"></div>
            <div v-html="htmlEntityDecode(tncsBlock)"></div>
        </div>

        <div class="bottom-navigation-wrapper" v-if="currentState < state.SUMMARY">
            <app-bottom-navigation
                :current-step="currentState"
                :disabled-next-step-button="disabledNextStepButton"
                :is-rebooking="options.isRebooking"
                :next-step-button-text=" 'Next' | translate "
            ></app-bottom-navigation>
        </div>

        <app-modal :show.sync="updateError">
            <div slot="content" class="valuation-result">
                <h2>{{ 'Something went wrong!' | translate }}</h2>
                <p v-if="defaultErrorMessageShow">{{ 'Please try again later or contact administrator' | translate }}</p>
                <p v-else>{{ errorMessage }}</p>
                <div class="valuation-result-continue row">
                    <div class="valuation-result-continue-block col-12 align-right">
                        <button type="button" name="button" class="button button-gray-light"
                                @click.prevent="processFailure()">{{ 'BACK' | translate }}
                        </button>
                    </div>
                </div>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import moment from 'moment';
    import appCarouselModel from 'bmw/components/YouDrive/CarouselModel';
    import appModal from 'core/components/Elements/Modal';
    import appTimepicker from 'bmw/components/Elements/TimePicker';
    import appMaps from 'bmw/components/YouDrive/Maps';
    import appModels from 'bmw/components/YouDrive/Models';
    import appSummary from 'bmw/components/YouDrive/Summary';
    import appCarBookedHeader from 'core/components/YouDrive/CarBookedHeader';
    import appSelect from 'core/components/Elements/Select';
    import appNavigationSteps from 'bmw/components/YouDrive/NavigationSteps';
    import appBottomNavigation from 'bmw/components/YouDrive/BottomNavigation';
    import appMoreInfo from 'core/components/Elements/MoreInfo';
    import appAccordionGroup from 'bmw/components/AccordionGroup';
    import appAccordion from 'core/components/Accordion';
    import appTooltip from 'core/components/Elements/Tooltip';
    import currentLocale from 'bmw/components/Shared/CurrentLocale';

    import stickybits from 'stickybits';

    export default Vue.extend({
        mixins: [
            currentLocale
        ],

        props: {
            youdriveCars: {
                required: true,
                type: Array
            },
            drivingLicenseTypes: {
                required: true,
                type: Array
            },
            customer: {
                required: false,
                type: [Object, Boolean],
                default: false
            },
            prefixes: {
                required: true,
                type: Array
            },
            initialSlide: {
                required: false,
                type: Number
            },
            selectedModelData: {
                required: true,
                type: Object
            },
            models: {
                required: false,
                type: Array
            },
            preselectStep: {
                required: true,
                type: String
            },
            options: {
                required: true,
                type: Object
            },
            descriptionBlock: {
                required: true,
                type: String
            },
            gdprDisclaimer: {
                required: false,
                type: String,
                default: ''
            },
            personalDetailsBlock: {
                required: false,
                type: String,
                default: ''
            },
            howitworksBlock: {
                required: true,
                type: String
            },
            tncsBlock: {
                required: true,
                type: String
            },
            getintouchBlock: {
                required: true,
                type: String
            },
            bookanotherBlock: {
                required: true,
                type: String
            },
            nextStepStatementBlock: {
                required: true,
                type: String
            }
        },

        data() {
            return {
                currentState: 0,
                updateError: false,
                ajaxLoading: false,
                // If ajaxLoading is used for all different requests, it can happen, that some
                // request is not yet finished, but other disables loading state and we may
                // face unpredictable behaviour
                ajaxLoadingNotAvailableDates: false,
                selectedDatetime: false,
                // Last Selected Date Time saves a copy of selected Date Time to repair it should selected Date Time become invalidated
                lastSelectedDatetime: false,
                validationError: false,
                defaultErrorMessageShow: false,
                errorMessage: '',
                calendarErrorMessage: '',
                showCalendarErrorMessage: false,
                minAge: 18,
                dobFull: null,
                navigationSteps: ['model', 'vehicle', 'datetime', 'confirmation', 'summary'],
                bottomMoreInfo: {
                    processing: 'Online booking process',
                    whereToFind: 'Where to find us',
                    terms: 'Terms & conditions'
                },
                screenWidth: jQuery(window).width(),
                reInitMap: true,
                dealerStateStep: 1,
                showMapWrapper: false,
                stores: [],
                storesWithNoCoords: [],
                localStore: {},
                selectModelsLimit: this.options.maxCarsToBook,
                state: {
                    MODEL: 0,
                    DEALER: 1,
                    DATE: 2,
                    CONFIRMATION: 3,
                    SUMMARY: 4
                },
                originalAddress: {},
                preSelectedModel: 0,
                modelPreloadState: false
            }
        },

        computed: {
            getAvailableTimesAdditionalRequestData() {
                return {
                    localStoreId: this.localStore.id,
                    vehicleId: this.selectedModelData.id
                };
            },

            selectedVehicles() {
                const vehicles = [];

                if (this.localStore && this.localStore.vehicles) {
                    this.localStore.vehicles.forEach((item) => {
                        if (this.isSelected(item)) {
                            vehicles.push(item.youdriveId);
                        }
                    });
                }

                return vehicles;
            },

            getActiveStep() {
                return this.navigationSteps[this.currentState];
            },

            selectedModels() {
                return this.$store.state.youdrive.selectedModels;
            },

            /**
             * Add validation for next step button state based on step
             */
            disabledNextStepButton() {
                return !(this.currentState === this.state.MODEL && this.selectedModels.length > 0) &&
                    !(this.currentState === this.state.DEALER && this.selectedVehicles.length > 0) &&
                    !(this.currentState === this.state.DATE && this.selectedDatetime) &&
                    !(this.currentState === this.state.CONFIRMATION && this.$testdrive.valid && !this.ajaxLoading)
            },

            storesSorted() {
                // Slice is used to clone the original array
                const stores = this.stores.slice(0);

                stores.forEach((store, index) => {
                    if (store.recommended) {
                        stores.splice(index, 1);
                        stores.unshift(store);
                    }
                });

                this.storesWithNoCoords.forEach((store) => {
                    stores.push(store);
                });

                return stores.sort(this.sortStoresByMultipleValues(['distance']));
            },

            selectedModelTitles() {
                const selectedModels = {};

                this.youdriveCars.forEach(car => {
                    if (this.selectedModels.includes(car.id)) {
                        selectedModels[car.id] = car.title;
                    }
                });

                return selectedModels;
            }
        },

        methods: {
            goBack(state) {
                if (typeof state === 'undefined') {
                    state = this.currentState - 1;
                }

                if ((state >= this.currentState) || (this.currentState === this.state.CONFIRMATION)) {
                    return;
                }

                // Skip date selection if Test Drive Request and going back
                if (this.isTDRequest && state === this.state.DATE) {
                    state -= 1;
                }

                this.clearBookingProgress({});
                this.currentState = state;
            },

            goToNextSubstep() {
                if (this.currentState === this.state.DEALER) {
                    if (this.dealerStateStep < 2) {
                        this.dealerStateStep = 2;
                    } else {
                        this.updateBookingProgress({
                            localStore: this.localStore,
                        });
                    }
                }
            },

            removeStoresWithNoCoords() {
                if (this.stores.length > 0) {
                    this.stores.forEach((store, index) => {
                        if (store.location.lat === 0 || store.location.lng === 0) {
                            this.stores.splice(index, 1);
                            this.storesWithNoCoords.push(store);
                        }
                    });
                }
            },

            removeStoresWithoutDistance() {
                if (this.stores.length) {
                    const result = this.stores.slice(0);
                    this.stores.forEach((store, index) => {
                        if (store.distance !== 0 && !store.distance) {
                            result.splice(index, 1);
                        }
                    });

                    this.stores = [];
                    if (result.length) {
                        result.forEach(store => {
                            this.stores.push(store);
                        });
                    }
                }
            },

            sortStoresByMultipleValues(props, desc = false) {
                return (obj1, obj2) => {
                    let i = 0;
                    let result = 0;

                    while (result === 0 && i < props.length) {
                        const sort = () => {
                            let res;
                            if (obj1[props[i]] > obj2[props[i]]) {
                                res = 1;
                            } else {
                                if (obj1[props[i]] < obj2[props[i]]) {
                                    res = -1;
                                } else {
                                    res = 0;
                                }
                            }

                            if (desc !== false && desc.indexOf(props[i]) !== -1) {
                                res = res * -1;
                            }
                            return res;
                        };
                        result = sort();
                        i++;
                    }
                    return result;
                }
            },

            selectDealer(index) {
                this.carSelected();

                if (this.storesSorted[index] !== undefined) {
                    this.localStore = this.storesSorted[index];

                    this.$broadcast('Map::selectStoreMarker', this.localStore.id);
                    this.updateBookingProgress({ localStore: this.localStore });

                    this.$nextTick(() => {
                        const scrollHeight = document.getElementsByClassName('dealer-list')[0].scrollHeight;
                        const clientHeight = document.getElementsByClassName('dealer-list')[0].clientHeight;
                        const $scrollTo = jQuery('.dealer-preview-wrapper.active');

                        if (this.$refs.storesMap.distancesCalculated && $scrollTo.length && scrollHeight > clientHeight) {
                            const $container = jQuery('.dealer-list');

                            $container.animate({ scrollTop: $scrollTo.offset().top - $container.offset().top + $container.scrollTop(), scrollLeft: 0 }, 300);
                        }
                    });
                }
            },

            selectDealerById(storeId) {
                storeId = parseInt(storeId) || 0;

                if (parseInt(this.localStore.id) === storeId) {
                    // Prevent infinite loops
                    return false;
                }

                this.storesSorted.forEach((store, index) => {
                    if (parseInt(store.id) === storeId) {
                        this.selectDealer(index);
                    }
                });
            },

            carSelected() {
                // Unchecks selected model when user selects another dealer
                for (const store of this.storesSorted) {
                    for (const model of store.vehicles) {
                        if (model.selected) {
                            model.selected = false;
                            this.selectVariant(this.localStore.id, model, false);
                        }
                    }
                }
            },

            availableModelIds(store) {
                // Lists models that are available for test drive
                const availableIds = [];

                for (const vehicle of store.vehicles) {
                    availableIds.push(vehicle.youdriveId);
                }

                return availableIds;
            },

            getHomeLocation() {
                if (!this.isAddress(this.customer.address)) {
                    return 'South Africa';
                }

                /**
                 * Format the address in the following order, as suggested by Google
                 * House Number, Street Direction, Street Name, Street Suffix, City, State, Zip, Country
                 */
                return [
                    this.customer.address.street,
                    this.customer.address.city,
                    this.customer.address.region,
                    this.customer.address.postcode,
                    this.customer.address.country,
                ].join(', ');
            },

            isAddress(address, strict = false) {
                if (!address) {
                    return false;
                }

                const keys = [
                    'postcode',
                    'street',
                    'city',
                    'region',
                    'country',
                ];

                for (const key of keys) {
                    if (!address[key] || (strict && address[key] === ' ')) {
                        return false;
                    }
                }
                return true;
            },

            storeFormattedAddress(store) {
                return [
                    store.street,
                    store.city,
                    store.state,
                    store.postal_code
                ]
                    .filter((entry) => entry ? entry.trim() !== '' : false)
                    .join(', ');
            },

            updateStoresData() {
                this.removeStoresWithoutDistance();
                if (!this.localStore.id) {
                    this.selectDealer(0);
                } else {
                    this.$broadcast('Map::selectStoreMarker', this.localStore.id);
                }
            },

            submitPostcodeAndContinue() {
                const $postcode = jQuery('#postcode_td');
                const $street = jQuery('#street_td');

                const postcode = $postcode.val();
                const street = $street.val();

                if (postcode.length) {
                    this.customer.address = {
                        postcode,
                        street: '',
                        city: '',
                        region: '',
                        country: 'South Africa',
                    };
                } else {
                    this.restoreOriginalAddress();
                }

                for (const [key, value] of Object.entries(this.customer.address)) {
                    this.customer.address[key] = value || ' ';
                }

                this.goToNextSubstep();

                $postcode.val('');
                $street.val('');
            },

            restoreOriginalAddress() {
                if (this.isAddress(this.originalAddress, true)) {
                    for (const [key, value] of Object.entries(this.originalAddress)) {
                        this.customer.address[key] = value || ' ';
                    }
                } else {
                    this.customer.address = false;
                }
            },

            saveOriginalAddress() {
                if (this.isAddress(this.customer.address, true)) {
                    for (const [key, value] of Object.entries(this.customer.address)) {
                        this.originalAddress[key] = value || ' ';
                    }
                }
            },

            selectDate() {
                if (this.selectedDatetime) {
                    this.selectedModelData.bookingDatetime = this.selectedDatetime;
                    this.ajaxLoading = true;
                    this.updateBookingProgress({
                        carId: this.selectedModelData.carId,
                        bookingDatetime: this.selectedModelData.bookingDatetime.format('Y-MM-DD HH:mm:ss A'),
                        localStore: this.localStore,
                        vehicle_ids: [this.selectedModelData.id]
                    }, () => {
                        this.ajaxLoading = false;
                    });

                    this.showCalendarErrorMessage = false;
                } else {
                    this.selectedModelData.bookingDatetime = moment('');
                }
            },

            selectRequestedVehicle() {
                this.ajaxLoading = true;
                this.updateBookingProgress({
                    carId: this.selectedModelData.carId,
                    localStore: this.localStore,
                    vehicle_ids: [this.selectedModelData.id]
                }, () => {
                    this.ajaxLoading = false;
                });
            },

            confirmBooking() {
                this.ajaxLoading = true;
                this.$http({
                    url: this.options.youdriveSaveBookingUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        modelIds: this.selectedVehicles,
                        bookingDatetime: this.selectedModelData.bookingDatetime.format('Y-MM-DD HH:mm:ss'),
                        localStoreCode: this.localStore.code,
                        bookingId: this.selectedModelData.bookingId,
                        model: this.selectedModelData.subtitle,
                        manufacturedModel: this.selectedModelData.model,
                        title: this.selectedModelData.title,
                        isTDRequest: this.isTDRequest() ? 1 : 0
                    }
                }).then(this.saveBookingSuccess, this.requestFail);
            },

            saveBookingSuccess(resp) {
                this.ajaxLoading = false;
                if (resp.data.success) {
                    this.selectedModelData.bookingId = resp.data.bookingId;
                    this.currentState = this.state.SUMMARY;
                }
            },

            updateModelsSuccess(resp) {
                this.ajaxLoadingNotAvailableDates = false;
                try {
                    this.$broadcast('YouDrive::updateModels');
                    if (!this.modelPreloadState) {
                        this.currentState = this.state.DEALER;
                    }
                    this.stores = resp.data.stores;

                    if (this.localStore.id) {
                        let localIsInStores = false;
                        this.stores.forEach((store) => {
                            if (store.id === this.localStore.id) {
                                localIsInStores = true;
                            }
                        });

                        if (!localIsInStores) {
                            this.localStore = {};
                        }
                    }

                    this.$broadcast('Map::reload', this.localStore.id || 0, true);
                } catch (e) {
                    this.requestFail(e)
                }

                this.modelPreloadState = false;
            },

            requestFail(error) {
                this.ajaxLoading = false;
                if (error.status === 401) {
                    this.$root.loggedOutPopup();
                } else {
                    if (!error.data.slots_taken) {
                        this.updateError = true;
                        if (error.data.message) {
                            this.errorMessage = error.data.message;
                        } else {
                            this.defaultErrorMessageShow = true;
                        }
                    } else {
                        this.showCalendarErrorMessage = true;
                        this.calendarErrorMessage = error.data.message;
                        this.processFailure();
                    }
                }
            },

            /**
             * Model slider slick options
             */
            generateOptions() {
                return {
                    centerMode: false,
                    contentClick: true,
                    swipeToSlide: false,
                    focusOnSelect: false,
                    initialSlide: this.initialSlide,
                    responsive: [{
                        breakpoint: 736,
                        settings: {
                            centerMode: true,
                            slidesToShow: 1,
                            contentClick: false,
                            swipeToSlide: true,
                            focusOnSelect: true,
                            variableWidth: true
                        }
                    }]
                };
            },

            bookTestDrive() {
                this.validationError = false;
                this.$validate();

                if (this.$testdrive.valid) {
                    this.ajaxLoading = true;
                    const customerData = this.customer;
                    customerData.primary_number = this.customer.primary_phone_number;
                    customerData.dob = this.dobFull;
                    this.$http({
                        url: this.options.youdriveSaveCustomerUrl,
                        method: 'POST',
                        emulateJSON: true,
                        data: {
                            customer: customerData
                        }
                    }).then(this.bookTestDriveSuccess, this.requestFail);
                }
            },

            bookTestDriveSuccess(resp) {
                this.ajaxLoading = false;
                if (resp.data.success) {
                    this.customer = JSON.parse(resp.data.customer);
                    this.$dispatch('YouDrive::confirmBooking');
                    this.$broadcast('TimePicker::resetTimeSlots');
                } else {
                    this.showError(resp.data.error);
                }
            },

            showError(error) {
                this.validationError = error;
            },

            collectFormData(form) {
                const formData = {};

                form.find('input, select').each((i, el) => {
                    const key = jQuery(el).attr('name');

                    if (key) {
                        if (jQuery(el).is(':checkbox')) {
                            if (jQuery(el).is(':checked')) {
                                formData[key] = 1;
                            } else {
                                formData[key] = 0
                            }
                        } else {
                            formData[key] = jQuery(el).val();
                        }
                    }
                });

                return formData;
            },

            updateBookingProgress(data, callback = false) {
                this.$http({
                    url: this.options.updateBookingProgressUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data
                }).then(
                    () => {
                        if (callback) {
                            callback.call();
                        }
                    },
                    () => {
                        this.requestFail();
                    }
                );
            },

            clearBookingProgress() {
                this.$http({
                    url: this.options.clearBookingProgressUrl,
                    method: 'POST',
                    emulateJSON: true
                });
            },

            getNotAvailableDates() {
                this.ajaxLoadingNotAvailableDates = true;

                this.$http({
                    url: this.options.notAvailableDatesUrl,
                    method: 'GET',
                    emulateJSON: true,
                    data: {
                        localStoreId: this.localStore.id,
                        vehicleId: this.selectedModelData.id
                    }
                }).then(this.getNotAvailableDatesSuccess, this.getNotAvailableDatesFail);
            },

            getNotAvailableDatesSuccess(resp) {
                this.ajaxLoadingNotAvailableDates = false;
                this.$broadcast('TimePicker::setAvailableDates', {
                    id: 'you_drive_calendar', holidays: resp.data
                });
                this.$broadcast('TimePicker::chooseAvailableDate');
                if (this.showCalendarErrorMessage) {
                    setTimeout(() => {
                        jQuery('.messages').slideUp(250);
                    }, 10000);
                }
            },

            getNotAvailableDatesFail() {
                this.ajaxLoadingNotAvailableDates = false;
            },

            removeParamsFromUrl() {
                const query = window.location.search.substring(1);
                if (query.length) {
                    if (window.history !== undefined && window.history.pushState !== undefined) {
                        window.history.pushState({}, document.title, window.location.pathname);
                    }
                }
            },

            scrollToNavigation() {
                jQuery('html, body').animate({
                    scrollTop: jQuery('#navigation-steps').offset().top
                }, 400);
            },

            /**
             * Scroll view to messages block
             */
            scrollToMessagesBlock() {
                jQuery('html, body').animate({
                    scrollTop: jQuery('#you-drive .global-messages').offset().top
                }, 400);
            },

            processFailure() {
                this.updateError = false;
                clearTimeout(this.$root.messagesTimeout);
                this.scrollToMessagesBlock();
                this.getNotAvailableDates();
            },

            createSelect(isObject, removeFirst, list, keyLabel = false, keyValue = false) {
                const options = [];

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
            },

            changeCustomerPrefix(data) {
                this.customer.prefix = data.title;
            },

            changeDrivingLicense(data) {
                this.customer.driving_license_type = data.value;
            },

            changeDobDay(data) {
                this.customer.dob.day = data.value;
                this.changeDobFull();
            },

            changeDobMonth(data) {
                this.customer.dob.month = data.value;
                this.changeDobFull();
            },

            changeDobYear(data) {
                this.customer.dob.year = data.value;
                this.changeDobFull();
            },

            changeDobFull() {
                this.dobFull = null;

                if (this.customer.dob.month && this.customer.dob.day && this.customer.dob.year) {
                    this.dobFull = this.customer.dob.format
                        .replace(/%[mb]/i, this.customer.dob.month)
                        .replace(/%[de]/i, this.customer.dob.day)
                        .replace(/%y/i, this.customer.dob.year);
                }
            },

            scrollDown() {
                jQuery('html, body').animate({
                    scrollTop: jQuery('.hero-wrapper').offset().top + jQuery('.hero-wrapper').height()
                }, 1000);
            },

            cancelDrive(bookingId) {
                this.ajaxLoading = true;
                if (this.options.cancelBookingUrl) {
                    this.$http({
                        url: this.options.cancelBookingUrl,
                        data: {
                            bookingId
                        }
                    }).then(this.cancelDriveSuccess, this.cancelDriveFail);
                }
            },

            cancelDriveSuccess(resp) {
                this.$broadcast('Summary::clearData', resp.data.bookingId);
            },

            cancelDriveFail(error) {
                this.ajaxLoading = false;
                this.defaultErrorMessageShow = true;
                this.updateError = true;
                this.defaultErrorMessageShow = false;
            },

            takeBlankBookingData(data) {
                return Object.assign({}, data);
            },

            resetSessionStorage() {
                if (sessionStorage.getItem('comingFromMyAccount') === 'true') {
                    sessionStorage.setItem('comingFromMyAccount', 'false');
                    this.$refs.youDriveSummary.isRebook = 'false';
                }
            },

            isSelected(obj) {
                return this.selectedModels.includes(obj.model) && (obj.selected === true || obj.selected === 'true');
            },

            selectVariant(storeId, model, status) {
                let selectedVehicle;
                this.stores.forEach((store) => {
                    if (parseInt(store.id) === parseInt(storeId)) {
                        store.vehicles.forEach((vehicle) => {
                            if (parseInt(vehicle.youdriveId) === parseInt(model.youdriveId)) {
                                vehicle.selected = status;
                                selectedVehicle = vehicle;
                            }
                        });

                        this.localStore = store;
                    }
                });

                if (status) {
                    this.updateBookingProgress({ localStore: this.localStore });
                    this.selectedModelData.id = selectedVehicle.id;
                    this.selectedModelData.image = selectedVehicle.image;
                    this.selectedModelData.title = selectedVehicle.title;
                    this.selectedModelData.subtitle = selectedVehicle.subtitle;
                    this.selectedModelData.assignedTo = selectedVehicle.assignedTo;
                }

                this.goToNextSubstep();
            },

            preSelectVariantsOnEdit() {
                if (this.selectedModelData.vehicle_ids && this.localStore) {
                    this.selectedModelData.vehicle_ids.forEach((vehicleId) => {
                        if (this.localStore.vehicles) {
                            this.localStore.vehicles.forEach((vehicle) => {
                                if (vehicleId === vehicle.youdriveId) {
                                    this.selectedModelData.id = vehicle.id;
                                    this.selectedModelData.image = vehicle.image;
                                    this.selectedModelData.title = vehicle.title;
                                    this.selectedModelData.subtitle = vehicle.subtitle;
                                    this.selectedModelData.assignedTo = vehicle.assignedTo;
                                    if (this.currentState === this.state.DEALER && this.dealerStateStep !== 1) {
                                        this.selectVariant(this.localStore.id, vehicle, true);
                                    }
                                }
                            });
                        }
                    });
                }
            },

            htmlEntityDecode(encodedHtml) {
                return jQuery('<textarea />').html(encodedHtml).text();
            },

            isTDRequest() {
                const assignedTo = this.selectedModelData.assignedTo;

                return assignedTo ?
                    assignedTo === this.localStore.associated_compound_dealer :
                    false;
            }
        },

        events: {
            'YouDrive::selectCar'(data) {
                this.ajaxLoadingNotAvailableDates = true;
                this.selectedModelData.carId = data;
                this.$http({
                    url: this.options.youdriveGetModelsUrl,
                    method: 'GET',
                    emulateJSON: true,
                    data: {
                        modelIds: data
                    }
                }).then(this.updateModelsSuccess, this.requestFail);
            },

            'YouDrive::selectModel'(model) {
                this.selectedModelData = jQuery.extend(this.selectedModelData, model);
            },

            'TimePicker::updated'(datetime) {
                // Check to confirm the user is on the right step before updating with a new datetime
                if (
                    !this.lastSelectedDatetime
                    || this.currentState <= this.state.DATE
                ) {
                    this.selectedDatetime = datetime;
                    // save a copy of the last selected datetime separately in case selectedDatetime becomes invalid
                    this.lastSelectedDatetime = this.selectedDatetime;
                    this.selectDate();
                // Otherwise update with the last selected datetime
                } else {
                    this.selectedDatetime = this.lastSelectedDatetime ? this.lastSelectedDatetime : datetime;
                    this.selectDate();
                }
            },

            'YouDrive::setCurrentState'(state) {
                // Skip DATE step if Test Drive Request
                if (this.isTDRequest() && state === this.state.DATE) {
                    if (this.currentState < state) {
                        state += 1;
                        this.selectRequestedVehicle();
                    } else if (this.currentState > state) {
                        state -= 1;
                    }
                }

                if (state === this.state.DEALER) {
                    if (this.currentState === this.state.MODEL) {
                        // clear selected vehicles if coming from step MODEL
                        this.carSelected();
                        this.localStore = {};
                        this.dealerStateStep = 1;
                    } else if (this.currentState > state) {
                        // show last selected if going back
                        this.dealerStateStep = 2;
                    }

                    this.$emit('YouDrive::selectCar', this.selectedModels);
                }

                if (state === this.state.CONFIRMATION && (!this.customer || !this.customer.id)) {
                    window.location.href = this.options.loginUrl;

                    return;
                }

                if (state === this.state.SUMMARY) {
                    this.resetSessionStorage();
                    this.bookTestDrive();
                } else {
                    this.currentState = state;
                    this.ajaxLoading = false;
                }
            },

            'YouDrive::confirmBooking'() {
                this.confirmBooking();
            },

            'YouDrive::requestFail'(error) {
                this.requestFail(error);
            },

            'YouDrive::getNotAvailableDates'() {
                this.getNotAvailableDates();
            },

            'CarouselModel::selectedSlidesUpdated'(data) {
                this.$store.commit('setSelectedModels', data.map(item => item.id));
                this.selectedVehicle = false;
            },

            'YouDrive::cancelDrive'(bookingId) {
                this.cancelDrive(bookingId);
            },

            'YouDrive::postBookingClearAction'() {
                const youDriveSummary = this.$refs.youDriveSummary;
                if (youDriveSummary.selectedModelData.bookingId === 0 && this.currentState === this.state.CONFIRMATION) {
                    this.currentState = 0;
                    this.ajaxLoading = false;
                } else {
                    this.ajaxLoading = false;
                }
            },

            'YouDrive::selectVariant'(storeId, model, status) {
                this.selectVariant(storeId, model, status);
            }
        },

        created() {
            /* Update data from booking progress saved in customer session */
            this.selectedModelData.bookingDatetime = moment(this.selectedModelData.bookingDatetime || '');
            this.selectedDatetime = this.selectedModelData.bookingDatetime;
            this.lastSelectedDatetime = this.selectedModelData.bookingDatetime;

            if (this.options.isRebooking) {
                setTimeout(this.removeParamsFromUrl, 1000);
                this.localStore = this.selectedModelData.localStore;
                this.preSelectVariantsOnEdit();
                this.currentState = this.state.DATE;
            } else if (this.preselectStep === 'confirm') {
                this.localStore = this.selectedModelData.localStore;
                setTimeout(this.removeParamsFromUrl, 1000);
                this.currentState = this.state.CONFIRMATION;
            } else if (this.preselectStep === 'models') {
                this.currentState = this.state.DEALER;
            } else {
                this.currentState = this.state.MODEL;
                this.selectedModelData.id = 0;
                this.selectedModelData.bookingId = 0;
            }

            if (this.selectedModelData.modelIds.length > 0) {
                this.preSelectedModel = this.selectedModelData.modelIds[0];
            }
        },

        ready() {
            this.changeDobFull();
            this.saveOriginalAddress();
            this.$broadcast('Map::init');

            jQuery('.scroll-down-button').on('click', () => {
                this.scrollDown();
            });

            jQuery('.hero-scroll-down').on('click', () => {
                this.scrollDown();
            });

            stickybits('.bottom-navigation-wrapper', {
                verticalPosition: 'bottom'
            });

            pca.magento.fields.push({ Line1: 'street_td', Postcode: 'postcode_td', CountrySelect: 'country_id_td' });
            pca.magento.load();
        },

        watch: {
            'currentState'(state) {
                this.scrollToNavigation();

                switch (state) {
                    case this.state.MODEL:
                        this.$broadcast('Carousel::reInit', this.generateOptions());
                        break;
                    case this.state.DEALER:
                        pushTags('VirtualPageview', '/virtual/test-drives/2-choose-model', 'Test Drive Step 2');
                        break;
                    case this.state.DATE:
                        this.$broadcast('TimePicker::getFirstAvailableDate');
                        this.getNotAvailableDates();
                        pushTags('VirtualPageview', '/virtual/test-drives/3-choose-date', 'Test Drive Step 3');
                        break;
                    case this.state.CONFIRMATION:
                        pushTags('VirtualPageview', '/virtual/test-drives/4-confirmation', 'Test Drive Step 4');
                        break;
                    default:
                        break;
                }
            },

            'dealerStateStep'(step) {
                // In case a reset/trigger was used
                if (step < 1) {
                    this.dealerStateStep = 1;
                    return;
                }

                this.scrollToNavigation();

                switch (step) {
                    case 1:
                        if (this.options.isRebooking) {
                            this.preSelectVariantsOnEdit();
                        }
                        break;

                    case 2:
                        this.$broadcast('Map::reload', this.localStore.id || 0, true);
                        this.$broadcast('Map::selectStoreMarker', this.localStore.id);
                        this.showMapWrapper = true;
                        break;

                    default:
                        break;
                }
            }
        },

        components: {
            appModal,
            appMoreInfo,
            appTimepicker,
            appMaps,
            appModels,
            appSummary,
            appCarBookedHeader,
            appSelect,
            appNavigationSteps,
            appBottomNavigation,
            appCarouselModel,
            appAccordionGroup,
            appAccordion,
            appTooltip
        }
    });
</script>
