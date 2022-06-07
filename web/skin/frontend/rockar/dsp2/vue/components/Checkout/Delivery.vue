<template>
    <div>
        <div class="checkout-delivery" v-if="this.stores.length > 0">
            <div class="general-preloader" v-show="ajaxLoading || !allLoaded"><div class="show-loading"></div></div>
            <div class="step-header delivery">
                {{ this.getHeadingText().stepHeading | translate }}
            </div>
            <ul class="messages" v-if="slotsError">
                <li class="error-msg">
                    <ul>
                        <li>
                            <span>{{ errorMessage }}</span>
                        </li>
                    </ul>
                </li>
            </ul>

            <div id="page-header" class="checkout-delivery" v-show="actionsVisible">
                <div class="header-desktop">
                    <div class="previous" @click="previousStep()">
                        <span>{{ 'Previous' | translate }}</span>
                    </div>
                </div>
            </div>

            <div class="delivery-info">
                <p>{{ `For your convenience, you have the choice to either collect
                    your new vehicle from your preferred BMW Retailer,
                    or have them deliver it to your location of choice.` | translate }}</p>
            </div>
            <div class="row retailers-heading">
                <p>{{ this.getHeadingText().retailersHeading | translate }}</p>
            </div>

            <div class="main-content-wrapper" v-if="stores.length">
                <div v-if="isCollectionAvailable || isDeliveryAvailable" class="row main-content dealers" :class="{ actions: actionsVisible }">
                    <div class="col-5 dealer-list" :class="{ 'dealer-list-border': !hasDistance }">
                        <div v-show="hasDistance"
                             class="dealer-preview-wrapper"
                             :class="{ actions: actionsVisible, active: currentStore.id === store.id, recommended: store.recommended }"
                             v-for="(index, store) in localStores">
                            <div class="dealer-preview" :class="{ active: currentStore.id === store.id, recommended: store.recommended }" @click="selectDealer(index)">
                                <div class="dealer-details">
                                    <div class="details">
                                        <div class="details-top-row">
                                            <div class="store-details">
                                                <div class="name">{{ store.name }}</div>
                                                <div class="state">{{ store.state }}</div>
                                            </div>
                                            <span class="distance" :class="[currentStore.id === store.id ? 'active' : '']">
                                                {{ store.distanceFormatted ? store.distanceFormatted : '' }}
                                            </span>
                                        </div>
                                        <div class="address" v-if="storeFormattedAddress(store)">
                                            {{ storeFormattedAddress(store) }}
                                        </div>
                                        <div class="title" v-if="store.recommendedType === 'order'">
                                            {{ 'You placed an order at this retailer' | translate }}
                                        </div>
                                        <div class="title" v-if="store.recommendedType === 'youdrive'">
                                            {{ 'You took a test drive at this retailer' | translate }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-7" v-if="currentStore.id === store.id">
                                    <div class="dealer-box" v-show="!actionsVisible">
                                        <div class="details">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="main-phone" v-if="currentStore.main_phone">
                                                        <p>{{ 'Tel:' | translate }}</p>
                                                        <span class="phone">{{ currentStore.main_phone }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div v-if="currentStore.openingHours">
                                                        <div class="heading-hours">
                                                            {{ 'Trading Hours' | translate }}:
                                                        </div>
                                                        <div class="hours">
                                                            <p v-for="(day, hours) in currentStore.openingHours">
                                                                <span class="day">{{ day }}</span>
                                                                <span class="hour">{{ hours }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="delivery-actions" v-show="!actionsVisible">
                                        <button class="button dsp2-outline"
                                                v-if="checkDeliveryAvailability(carriers.home_delivery)"
                                                :class="[!isDeliveryAvailable ? 'button-disabled' : '']"
                                                @click="changeDeliveryType('home_delivery')"
                                                :id=" 'delivery-type-' + carriers.home_delivery"
                                                :data-title=" 'Schedule Delivery' | translate"
                                        >{{ 'Schedule Delivery' | translate }}
                                            <span v-if="deliveryPrice > 0"> - {{ currencySymbol }}{{ deliveryPrice }}</span>
                                        </button>
                                        <button class="button dsp2-outline"
                                                v-if="isCollectionAvailable && checkDeliveryAvailability(carriers.collection_free)"
                                                @click="changeDeliveryType('collection_free')"
                                                :id=" 'delivery-type-' + carriers.collection_free"
                                                :data-title=" 'Schedule Collection' | translate"
                                        >{{ 'Schedule Collection' | translate }}
                                        </button>
                                    </div>

                                    <!-- FREE COLLECTION START -->
                                    <div class="delivery-method collection_free" v-show="collectionVisible">
                                        <div class="delivery-opening">
                                            <p class="delivery-heading">
                                                {{ 'Select your preferred collection date' | translate }}
                                            </p>
                                            <div class="row inner-content">
                                                <app-datepicker
                                                    :identifier="carriers.collection_free"
                                                    :next-available-date="nextAvailableDate"
                                                >
                                                </app-datepicker>

                                                <div class="right-col">
                                                    <p class="delivery-heading">
                                                        {{ 'Select your time for collection' | translate }}
                                                    </p>

                                                    <div v-if="isTimesArrayNotEmpty">
                                                        <div id="timepicker" class="timepicker">
                                                            <div class="timepicker-time">
                                                                <div class="timepicker-body">
                                                                    <div
                                                                        v-for="time in times"
                                                                        track-by="$index"
                                                                        @click="selectTime(time)"
                                                                        class="timepicker-hour" :class="{ 'selected' : activeTime == time }"
                                                                    >
                                                                        {{ time }}
                                                                    </div>
                                                                </div>
                                                                <h3 class="timepicker-title">{{ collectionDateLabel }}</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p v-else>{{ 'Retailer is not open on this day. Please, choose another day.' | translate }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- FREE COLLECTION END -->

                                    <!-- HOME DELIVERY START -->
                                    <div v-show="homeDeliveryVisible" class="delivery-method home-delivery">
                                        <div class="row">
                                            <p class="delivery-heading">
                                                {{ 'Select your preferred delivery date' | translate }}
                                            </p>

                                            <div class="date-picker-wrapper">
                                                <app-datepicker :identifier="carriers.home_delivery"></app-datepicker>
                                            </div>

                                            <p class="delivery-heading">{{ 'Your delivery details' | translate }}</p>
                                            <div class="info">
                                                <div class="delivery-row">
                                                    <p class="bold">{{ 'Address' | translate }}</p>
                                                    <div v-if="deliveryAddress.streets.length > 0">
                                                        <span class="address">{{ this.deliveryAddressFormatted(deliveryAddress) }}</span>
                                                    </div>
                                                    <div v-else><strong>{{ 'Address is not set (home delivery is not available).' | translate }}</strong></div>
                                                </div>

                                                <div class="delivery-row">
                                                    <p class="bold">{{ 'Delivery date' | translate }}</p>
                                                    <span>{{ deliveryDate }}</span>
                                                </div>

                                                <div class="delivery-row">
                                                    <p class="bold">{{ 'Delivery Cost' | translate }}</p>
                                                    <span>{{ deliveryPrice | numberFormat '0,0.00' true false }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <p v-if="!isTimesArrayNotEmpty">
                                                {{ 'Retailer is not open on this day. Please, choose another day.' | translate }}
                                            </p>
                                        </div>
                                    </div>
                                    <!-- HOME DELIVERY END -->

                                    <div class="row button-set button-set-mobile" v-show="actionsVisible">
                                        <div class="col-6">
                                            <button
                                                class="button f-right"
                                                :class="{ 'button-disabled': !this.canSave() }"
                                                @click="submit()"
                                            >
                                                {{ 'Save and continue' | translate }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="right-content-wrapper">
                    <div class="delivery-map-wrapper">
                        <div class="delivery-map">
                            <app-maps
                                :js-api="true"
                                :ajax-loading.sync="ajaxLoading"
                                :api-key="mapApiKey"
                                :locations="stores"
                                :type-of-travel="'WALKING'"
                                :home="getHomeLocation()"
                                @update-stores-data="updateStoresData"
                                :toggle-map="toggleMap"
                                :zoom="16"
                            ></app-maps>
                        </div>
                        <div class="row retailers-heading-mobile">
                            <p>{{ actionsVisible ? this.getHeadingText().retailersHeading : 'BMW retailers near your address' | translate }}</p>
                        </div>
                    </div>
                    <div class="row button-set-desktop" v-show="actionsVisible">
                        <button
                            class="button f-right"
                            :class="{ 'button-disabled': !this.canSave() }"
                            @click="submit()"
                        >
                            {{ 'Save and continue' | translate }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="row main-content">
                <p>{{ 'Delivery methods are not available' | translate }}</p>
            </div>

            <app-modal :show.sync="outOfRange" v-if="outOfRange" width="80%" class-name="simple-popup">
                <div slot="content">
                    {{{ deliveryOutOfRangeContent | cmsBlock }}}
                    <div class="valuation-result-continue row">
                        <div class="valuation-result-continue-block col-12">
                            <button type="button" name="button" class="button button-blue-lagoon" @click="closeOverlay()">{{ 'Ok' | translate }}</button>
                        </div>
                    </div>
                </div>
            </app-modal>
        </div>
        <app-modal :show.sync="outOfRange" v-if="outOfRange" width="80%">
            <div slot="content">
                {{{ deliveryOutOfRangeContent | cmsBlock }}}
                <div class="valuation-result-continue row">
                    <div class="valuation-result-continue-block col-12">
                        <button type="button" name="button" class="button" @click="closeOverlay()">{{ 'Ok' | translate }}</button>
                    </div>
                </div>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import moment from 'moment';
    import appMaps from 'dsp2/components/Elements/Maps';
    import perfectScrollbar from 'perfect-scrollbar';
    import coreDelivery from 'core/components/Checkout/Delivery';
    import appPageHeader from 'dsp2/components/PageHeader';
    import Constants from 'dsp2/components/Shared/Constants';
    import EventTracker from 'dsp2/mixins/EventTracker';

    export default coreDelivery.extend({
        mixins: [Constants, EventTracker],

        props: {
            vehicleBrandName: {
                type: String,
                required: false,
                default: 'BMW'
            }
        },

        data() {
            return {
                allLoaded: false,
                currentStep: 0,
                steps: ['selection', 'action']
            }
        },

        computed: {
            /**
             * Selects the next available date from the current day using momentjs
             *
             * @return date in format DD/MM/YYYY
             */
            nextAvailableDate() {
                const availableDays = this.getAvailableDates();
                const date = this.getFirstAvailableDate();

                if (availableDays.length) {
                    const today = date.day();
                    const minDay = availableDays.reduce((a, b) => Math.min(moment().day(a).format('d'), moment().day(b).format('d')));
                    let maxDay = availableDays.reduce((a, b) => Math.max(moment().day(a).format('d'), moment().day(b).format('d')));
                    let nextAvailable = -1;
                    availableDays.forEach(value => {
                        const dayNumber = parseInt(moment().day(value).format('d'));
                        if (dayNumber === today && maxDay > dayNumber) {
                            maxDay = parseInt(dayNumber);
                        }
                        nextAvailable = maxDay;
                    });
                    nextAvailable = typeof nextAvailable === 'number' ? nextAvailable : parseInt(moment().day(nextAvailable).format('d'));
                    const result = today <= nextAvailable ? date.isoWeekday(nextAvailable).format('MM/DD/YYYY') : date.add(1, 'week').isoWeekday(minDay).format('MM/DD/YYYY');
                    const firstAvailableDate = this.getFirstAvailableDate();

                    return moment(result, 'DD/MM/YYYY') < firstAvailableDate ? firstAvailableDate.format('MM/DD/YYYY') : result;
                }

                return false;
            },

            isTimesArrayNotEmpty() {
                return Array.isArray(this.times) && this.times.length;
            },

            collectionVisible() {
                return this.deliveryType === this.carriers.collection_free && this.isCollectionAvailable && this.actionsVisible;
            },

            homeDeliveryVisible() {
                return this.deliveryType === this.carriers.home_delivery && this.isDeliveryAvailable && this.actionsVisible;
            },

            actionsVisible() {
                return this.currentStep === 1;
            }
        },

        methods: {
            deliveryAddressFormatted(deliveryAddress) {
                return (deliveryAddress.streets).concat([
                    deliveryAddress.city,
                    deliveryAddress.region,
                    deliveryAddress.country,
                    deliveryAddress.postcode
                ])
                    .filter((entry) => entry ? entry.trim() !== '' : false)
                    .join(', ');
            },

            getHeadingText() {
                if (!this.actionsVisible) {
                    return {
                        retailersHeading: 'Please select your preferred retailer from the list below:',
                        stepHeading: 'Collection / Delivery'
                    };
                } else {
                    return this.homeDeliveryVisible ?
                        { retailersHeading: 'Schedule Delivery', stepHeading: 'Delivery' } :
                        { retailersHeading: 'Schedule Collection', stepHeading: 'Collection' };
                }
            },

            previousStep() {
                this.currentStep = this.currentStep - 1;
            },

            initScrollbar() {
                perfectScrollbar.initialize(
                    this.$el.firstElementChild.lastElementChild.firstElementChild,
                    {
                        suppressScrollX: true,
                        wheelPropagation: true
                    }
                );
            },

            /**
             * returns true or false based on if any of the stores have a distance
             */
            hasDistance() {
                return this.stores.some(this.checkDistance);
            },

            saveShippingSuccess(resp) {
                this.ajaxLoading = false;

                if (!resp.data.success) {
                    this.saveShippingFail(resp);
                    return;
                }

                let skipStepCode = null;
                if (typeof resp.data.skipStep !== 'undefined') {
                    skipStepCode = resp.data.skipStep;
                }

                // Login page is not in accordion group, indexes start with 0
                // So we must add 2 to compensate for that
                const stepIndex = this.openStep + 1,
                    optionValue = this.deliveryType === this.carriers.home_delivery ? 'Home Delivery' : 'Collection',
                    optionSelectedObject = {
                        'event': 'checkoutOption',
                        'ecommerce': {
                            'checkout_option': {
                                'actionField': {
                                    'step': stepIndex,
                                    'option': optionValue
                                }
                            }
                        }
                    };

                pushEcommerceTags(optionSelectedObject);

                this.$dispatch('CheckoutAccordionGroup::updateDeliveryStatusBar');
                this.$dispatch('CheckoutAccordionGroup::nextStep', this.$parent.stepCode);
                this.$dispatch('Checkout::updateSummaryStep', resp.data);
            },

            getStatusBar() {
                const action = 'edit';
                const statusBar = this.deliveryType === this.carriers.home_delivery ? 'Home Delivery' : 'Collection';

                return { message: statusBar, action };
            },

            /**
             * returns true or false based on if the store has a distance
             *
             * @param {Object} store
             */
            checkDistance(store) {
                return store.distance !== '';
            },

            /**
             * Time picker slot update based on "openingHours" range set in admin for a store
             *
             * Notice: no slots - no time picker.
             */
            updateCollectionDatepicker(date) {
                this.collectionDate = date.format('dddd Do MMMM');
                this.collectionDateObject = date;

                if (this.currentStore.hasOwnProperty('holidays')) {
                    if (this.currentStore.holidays.indexOf(date.format('DD/MM/YYYY')) !== -1) {
                        this.times = [];
                        this.activeTime = 0;

                        return;
                    }
                }

                if (this.currentStore.hasOwnProperty('openSlots')) {
                    const selectedWeekDay = date.format('dddd').toLowerCase();
                    const openSlots = this.currentStore.openSlots;

                    this.times = [];
                    this.activeTime = 0;

                    if (openSlots && openSlots.hasOwnProperty(selectedWeekDay.toLowerCase())) {
                        const storeWeekDay = openSlots[selectedWeekDay];

                        if (jQuery.isEmptyObject(storeWeekDay.is_working)) {
                            return;
                        }

                        const newTimeArray = [];
                        /**
                         * Add time slot to time-picker based on available time slots configured for selected store
                         *
                         * Available time slots reduced by already placed orders for chosen date ( if any )
                         */
                        storeWeekDay.slots.forEach((value, idx) => {
                            if (parseInt(storeWeekDay.slots_count[idx]) > 0) {
                                const collectionDate = `${date.format('YYYY-MM-DD')} ${value}:00`;
                                let availableSlots = storeWeekDay.slots_count[idx];

                                if (availableSlots) {
                                    // Check reserved slots for this date
                                    this.currentStore.takenTimeSlots.items.forEach((el) => {
                                        if (collectionDate === el.collection_date) {
                                            availableSlots--;
                                        }
                                    });

                                    if (availableSlots > 0) {
                                        const fullDateFormat = `${this.todayDate.format('YYYY/MM/DD')}/${value}:00`;
                                        const twelveHourFormat = moment(fullDateFormat, 'YYYY/MM/DD/HH:mm:ss').format('hA');

                                        newTimeArray.push(twelveHourFormat);
                                    }
                                }
                            }
                        });

                        this.times = newTimeArray;
                        this.activeTime = newTimeArray[0];
                    }
                }
            },

            canSave() {
                return (
                    !jQuery.isEmptyObject(this.currentStore)
                    && (this.collectionMethodPage === 1 || this.collectionMethodPage === 2)
                    && this.activeTime
                )
            },

            submit() {
                this.slotsError = false;
                if (this.currentStoreIsSet() && this.activeTime) {
                    if (this.collectionMethodPage === 1) {
                        this.nextView();
                    }

                    this.saveShipping();
                }
            },

            /**
             * Changes the delivery type
             *
             * @param {String} deliveryType
             */
            changeDeliveryType(deliveryType) {
                this.currentStep = 1;
                this.deliveryType = this.carriers[deliveryType];
                const shippingDate = this.deliveryType === this.carriers.home_delivery ? this.deliveryDateObject : this.collectionDateObject;

                if (this.deliveryType !== this.carriers.home_delivery && this.nextAvailableDate) {
                    this.updateCollectionDatepicker(moment(this.nextAvailableDate));
                    this.$broadcast('calendar::setDate', { id: this.deliveryType, date: this.nextAvailableDate });
                } else {
                    this.updateCollectionDatepicker(shippingDate);
                }

                const currentDate = this.nextAvailableDate || this.getFirstAvailableDate().format('MM/DD/YYYY');

                if (this.deliveryType === this.carriers.collection_free) {
                    this.$broadcast('calendar::setActiveDayMap',
                        {
                            id: this.carriers.collection_free,
                            startDate: this.currentDate,
                            range: this.deliveryRange,
                            holidays: this.currentStore.holidays
                        });

                    this.$broadcast('calendar::setDate', { id: this.carriers.collection_free, date: currentDate });
                    this.$broadcast('calendar::setMinDate', { id: this.carriers.collection_free, date: currentDate });
                } else {
                    this.$broadcast('calendar::setActiveDayMap',
                        {
                            id: this.carriers.home_delivery,
                            startDate: this.currentDate,
                            range: this.deliveryRange,
                            holidays: this.currentStore.holidays
                        });

                    this.$broadcast('calendar::setDate', { id: this.carriers.home_delivery, date: currentDate });
                    this.$broadcast('calendar::setMinDate', { id: this.carriers.home_delivery, date: currentDate });
                }
            },

            // update stores and turn off loader
            updateStoresData() {
                this.stores.sort(this.sortStoresByMultipleValues(
                    ['available_models', 'has_previous_orders', 'distance'],
                    ['available_models', 'has_previous_orders']
                ));
                this.selectDealer(0);
                this.allLoaded = true;
            },

            updateDeliveryAddress() {
                this.$super(coreDelivery, 'updateDeliveryAddress');

                // check if user has gone back and changed address
                if (this.allLoaded) {
                    this.allLoaded = false;

                    this.$broadcast('Map::reload', 0, true);
                }
            },

            selectDealer(index) {
                if (this.localStores[index] !== undefined) {
                    this.currentStore = this.localStores[index];
                    this.$broadcast('Map::reload', this.currentStore.id);
                }
            }
        },

        watch: {
            'openStep'(result) {
                if (result === this.CHECKOUT_DELIVERY) {
                    /**
                     * Fire event for tracking purposes on initial load of delivery
                     */
                    this.fireEventForTracking(
                        this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                        this.getEventConstants().EVENTRACKERVALUES.CHECKOUTDELIVERY
                    );
                }
            }
        },

        events: {
            /**
             * Selected date diffed by current date to see if out of calculated range
             * And update collection date so it is synced
             *
             * @param date
             */
            'Delivery::deliveryDate'(date) {
                this.checkOutOfRange(date);
                this.updateCollectionDatepicker(date);
                this.deliveryDate = date.format('DD MMMM YYYY');
                this.deliveryDateObject = date;
            },

            'Delivery::setDates'(obj) {
                const formattedDate = obj.date.format('DD MMMM YYYY');
                if (obj.id === this.carriers.collection_free) {
                    this.collectionDate = formattedDate;
                    this.collectionDateObject = obj.date;
                } else {
                    this.deliveryDate = formattedDate;
                    this.deliveryDateObject = obj.date;
                }
            },

            'Delivery::setCurrentStore'(store) {
                this.localStores.some((localStore, index) => {
                    if (localStore.id === store.id) {
                        this.selectDealer(index);

                        return true;
                    }

                    return false;
                });

                this.$nextTick(() => {
                    const dealerItem = jQuery('.dealer-preview.active');

                    if (dealerItem.length > 0) {
                        const dealerList = dealerItem.closest('.dealer-list');

                        // Make dealer visible if needed
                        if (
                            dealerItem.offset().top + dealerItem.height() > dealerList.offset().top + dealerList.height() ||
                            dealerItem.offset().top - dealerList.offset().top < 0
                        ) {
                            dealerList.stop().animate({
                                scrollTop: dealerItem.offset().top - dealerList.offset().top + dealerList.scrollTop()
                            });
                        }
                    }
                });
            }
        },

        ready() {
            this.initScrollbar();
        },

        components: {
            appMaps,
            appPageHeader
        }
    });
</script>
