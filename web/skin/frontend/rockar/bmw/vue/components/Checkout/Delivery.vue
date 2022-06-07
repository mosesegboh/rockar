<template>
    <div>
        <div class="checkout-delivery" v-if="this.stores.length > 1">
            <div class="general-preloader" v-show="ajaxLoading || !allLoaded"><div class="show-loading"></div></div>
            <ul class="messages" v-if="slotsError">
                <li class="error-msg">
                    <ul>
                        <li>
                            <span>{{ errorMessage }}</span>
                        </li>
                    </ul>
                </li>
            </ul>
            <div v-if="stores.length">
                <div class="row">
                    <p class="section-heading">
                        {{ 'Retailers near your address:' | translate }}
                    </p>
                </div>
            </div>

            <div v-if="isCollectionAvailable || isDeliveryAvailable" class="row main-content dealers">
                <div class="col-5 dealer-list" :class="{ 'dealer-list-border': !hasDistance }">
                    <div v-show="hasDistance" class="dealer-preview-wrapper" :class="{recommended: store.recommended}" v-for="(index, store) in localStores">
                        <div class="dealer-preview" :class="{active: currentStore.id === store.id, recommended: store.recommended}" @click="selectDealer(index)">
                            <div class="title" v-if="store.recommendedType === 'order'">
                                {{ 'You placed an order at this retailer' | translate }}
                            </div>
                            <div class="title" v-if="store.recommendedType === 'youdrive'">
                                {{ 'You took a test drive at this retailer' | translate }}
                            </div>
                            <div class="number">
                                <span>{{ index + 1 }}</span>
                            </div>
                            <div class="details">
                                <div class="name">{{ store.name }}</div>
                                <div class="address" v-if="storeFormattedAddress(store)">
                                    {{ storeFormattedAddress(store) }}
                                </div>
                                <div class="rating-distance-wrapper">
                                    <div class="rating">
                                        <div v-if="!isNaN(store.rating)">
                                            <span class="icon star" v-for="star in getRatingStars(store.rating, 'full')"></span>
                                            <span class="icon half-star" v-for="halfStar in getRatingStars(store.rating, 'half')"></span>
                                            <span class="icon empty-star" v-for="emptyStar in getRatingStars(store.rating, 'empty')"></span>
                                        </div>
                                        <div v-if="isNaN(store.rating)">
                                            <div class="rating-text">{{ store.rating }}</div>
                                        </div>
                                    </div>
                                    <div class="distance">{{ store.distanceFormatted ? store.distanceFormatted : '' }}</div>
                                </div>
                            </div>
                        </div>

                        <div v-if="store.recommended && isAnotherDealerAvailable" class="another-dealer-wrapper">{{ 'Or choose another retailer near you:' | translate }}</div>
                    </div>
                </div>

                <div class="col-7">
                    <div class="dealer-box" v-if="currentStore.id">
                        <div class="details">
                            <div class="name">{{ currentStore.name }}</div>
                            <div class="row">
                                <div class="col-6">
                                    <div v-if="currentStoreFormattedAddress">
                                        <div class="heading">
                                            {{ 'Address' | translate }}:
                                        </div>
                                        <div class="address">
                                            {{ currentStoreFormattedAddress }}
                                        </div>
                                    </div>
                                    <div class="main-phone" v-if="currentStore.main_phone">
                                        {{ currentStore.main_phone }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div v-if="currentStore.openingHours">
                                        <div class="heading">
                                            {{ 'Opening hours' | translate }}:
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

                    <div class="delivery-actions">
                        <button class="button"
                            v-if="isCollectionAvailable && checkDeliveryAvailability(carriers.collection_free)"
                            :class="collectionButtonClass"
                            @click="changeDeliveryType('collection_free')"
                            :id=" 'delivery-type-' + carriers.collection_free"
                            :data-title=" 'Collection' | translate"
                        >{{ 'Collection' | translate }}
                        </button>
                        <button class="button"
                            v-if="checkDeliveryAvailability(carriers.home_delivery)"
                            :class="[homeDeliveryButtonClass, (!isDeliveryAvailable ? 'button-disabled' : '')]"
                            @click="changeDeliveryType('home_delivery')"
                            :id=" 'delivery-type-' + carriers.home_delivery"
                            :data-title=" 'Home delivery' | translate"
                        >{{ 'Home delivery' | translate }}
                            <span v-if="deliveryPrice > 0"> - {{ currencySymbol }}{{ deliveryPrice }}</span>
                        </button>
                    </div>

                    <!-- FREE COLLECTION START -->
                    <div class="delivery-method collection_free" v-show="collectionVisible">
                        <div v-show="collectionMethodPage === 1">
                            <div class="description">{{{ collectionInfoBlockContent | cmsBlock }}}</div>

                            <div class="row">
                                <div class="col-12">
                                    <app-maps
                                        :js-api="true"
                                        :ajax-loading.sync="ajaxLoading"
                                        :api-key="mapApiKey"
                                        :locations="stores"
                                        :type-of-travel="'WALKING'"
                                        :home="getHomeLocation()"
                                        @update-stores-data="updateStoresData"
                                        :toggle-map="toggleMap"
                                    ></app-maps>
                                </div>
                            </div>
                        </div>

                        <div class="delivery-opening" v-else>
                            <p>{{ 'Collection from' | translate }} <strong>{{ currentStore.name }}</strong></p>
                            <p>{{ 'Select date and time for collection.' | translate }}</p>
                            <p>{{ 'Your collection appointment will last approximately 2 hours.' | translate }}</p>
                            <div class="row inner-content">
                                <div class="col-md-12 col-lg-9">
                                    <app-datepicker
                                        :identifier="carriers.collection_free"
                                        :next-available-date="nextAvailableDate"
                                    >
                                    </app-datepicker>
                                </div>

                                <div class="col-md-12 col-lg-9 right-col">
                                    <p>{{ collectionDate }}</p>

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
                                    <p>{{ 'Collection cost: Free' | translate }}</p>
                                </div>
                            </div>
                            <div class="row inner-content">
                                <p>{{vehicleBrandName + ' will contact you to confirm this time within the next 3 days.' | translate }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- FREE COLLECTION END -->

                    <!-- HOME DELIVERY START -->
                    <div v-show="homeDeliveryVisible" class="delivery-method home-delivery">
                        <div class="row">
                            <p>{{ "Once you've selected a BMW Retailer near your address, your new BMW will either be transported to your nearest Retailer or selected from their current models in stock. Your new BMW will then be driven to your home with care by a licensed driver. That means less kilometers and more convenience for you." | translate }}</p>
                            <p class="section-heading">{{ 'Your delivery details:' | translate }}</p>
                            <p>{{ 'Please select your preferred delivery date - subject to availability:' | translate }}</p>
                        </div>

                        <div class="row">
                            <div class="col-8">
                                <app-datepicker :identifier="carriers.home_delivery"></app-datepicker>
                            </div>

                            <div class="col-4">
                                <div class="info">
                                    <div class="address-details">
                                        <p class="bold">{{ 'Your address:' | translate }}</p>
                                        <div v-if="deliveryAddress.streets.length > 0">
                                            <span v-for="street in deliveryAddress.streets" track-by="$index">{{ street }}</span>
                                            <span>{{ deliveryAddress.city }}</span>
                                            <span>{{ deliveryAddress.region }}</span>
                                            <span>{{ deliveryAddress.country }}</span>
                                            <span>{{ deliveryAddress.postcode }}</span>
                                        </div>
                                        <div v-else><strong>{{ 'Address is not set (home delivery is not available).' | translate }}</strong></div>
                                    </div>

                                    <div class="chosed-date">
                                        <p class="bold">{{ 'Delivery date:' | translate }}</p>
                                        <span>{{ deliveryDate }}</span>
                                    </div>

                                    <div class="delivery">
                                        <p class="bold">{{ 'Delivery Cost:' | translate }}</p>
                                        <span>{{ currencySymbol }}{{ deliveryPrice }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <p v-if="!isTimesArrayNotEmpty">
                                {{ 'Retailer is not open on this day. Please, choose another day.' | translate }}
                            </p>
                        </div>
                        <div class="row">
                            <p>
                                {{`Your ${vehicleBrandName} Retailer will contact you within the next 3 days to confirm your delivery date and time` | translate }}
                            </p>
                        </div>
                    </div>
                    <!-- HOME DELIVERY END -->

                    <div class="row button-set">
                        <div class="col-6">
                            <button v-if="collectionMethodPage == 2 && deliveryType == carriers.collection_free" class="button button-empty" @click="previousView()">{{ 'Back' | translate }}</button>
                            <div class="empty" v-else>&nbsp</div>
                        </div>

                        <div class="col-6">
                            <button class="button f-right" :class="{ 'button-disabled': !this.canSave() }" @click="submit()">{{ 'Save and continue' | translate }}</button>
                        </div>
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
        <div class="checkout-delivery" v-if="this.stores.length === 1">
            <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
            <ul class="messages" v-if="slotsError">
                <li class="error-msg">
                    <ul>
                        <li>
                            <span>{{ errorMessage }}</span>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="row">
                <p class="section-heading">
                    {{ 'Please select your preferred delivery option' | translate }}
                </p>

                <div class="delivery-actions">
                    <button v-if="isCollectionAvailable"
                        class="button"
                        :class="[deliveryType === carriers.collection_free && isCollectionAvailable || !isDeliveryAvailable ? 'button-dark' : 'button-empty']"
                        @click="deliveryType = carriers.collection_free"
                        :id=" 'delivery-type-' + carriers.collection_free"
                        :data-title=" 'COLLECTION - FREE' | translate">
                        {{ 'COLLECTION - FREE' | translate }}
                    </button>
                    <button v-if="isDeliveryAvailable"
                        class="button"
                        :class="[deliveryType === carriers.home_delivery && isDeliveryAvailable || !isCollectionAvailable ? 'button-dark' : 'button-empty']"
                        @click="deliveryType = carriers.home_delivery"
                        :id=" 'delivery-type-' + carriers.home_delivery"
                        :data-title=" 'HOME DELIVERY' | translate"
                    >
                        {{ 'HOME DELIVERY' | translate }}
                        <span v-if="deliveryPrice > 0"> - {{ currencySymbol }}{{ deliveryPrice }}</span>
                    </button>
                </div>
            </div>

            <div v-if="isCollectionAvailable || isDeliveryAvailable" class="row main-content">
                <div class="delivery-method collection_free" v-show="deliveryType === carriers.collection_free && isCollectionAvailable || !isDeliveryAvailable">
                    <div v-show="collectionMethodPage === 1">
                        <p>{{{ collectionInfoBlockContent | cmsBlock }}}</p>
                        <div class="row">
                            <div class="col-8 col-md-6">
                                <app-maps
                                    :js-api="true"
                                    :ajax-loading.sync="ajaxLoading"
                                    :api-key="mapApiKey"
                                    :locations="stores"
                                    :home="getHomeLocation()"
                                    @update-stores-data="updateStoresData"
                                ></app-maps>
                            </div>
                            <div class="col-4 col-md-6">
                                <div v-if="currentStoreIsSet()">
                                    <p><strong>{{ currentStore.title }}</strong></p>
                                    <div v-for="street in currentStore.streets" track-by="$index">
                                        <p>{{ street }}</p>
                                    </div>
                                    <p>{{ currentStore.city }}</p>
                                    <p>{{ currentStore.state }}</p>
                                    <p>{{ currentStore.region }}</p>
                                    <p>{{ currentStore.postal_code }}</p>
                                    <p>{{ currentStore.phone }}</p>

                                    <div class="delivery-opening" v-if="currentStore.openingHours">
                                        <p><strong>{{ 'Opening hours:' | translate }}</strong></p>
                                        <div class="row" v-for="(day, hours) in currentStore.openingHours">
                                            <div>
                                                <div class="day col-6">{{ day }}</div>
                                                <div class="hours col-6">{{ hours }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>{{ 'No stores are added to the map' | translate }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="delivery-opening" v-else>
                        <p class="section-heading">{{ 'Collection from' | translate }} {{ currentStore.title }}</p>
                        <p class="section-heading">{{ 'Select date and time for collection' | translate }}</p>
                        <p>{{ 'Your collection appointment will last approximately 2 hours' | translate }}</p>
                        <div class="row inner-content">
                            <div class="col-8">
                                <app-datepicker :identifier="carriers.collection_free"></app-datepicker>
                            </div>
                            <div class="col-4 right-col">
                                <div>
                                    <p>{{ collectionDate }}</p>
                                </div>
                                <div v-if="times.length > 0">
                                    <div id="timepicker" class="timepicker">
                                        <div class="timepicker-time">
                                            <div class="timepicker-body">
                                                <div
                                                    v-for="time in times"
                                                    track-by="$index"
                                                    @click="selectTime(time)"
                                                    class="timepicker-hour" :class="{ 'selected' : activeTime == time }">
                                                    {{ time }}
                                                </div>
                                            </div>
                                            <h3 class="timepicker-title">{{ collectionDateLabel }}</h3>
                                        </div>
                                    </div>
                                    <div>{{vehicleBrandName + ' will contact you to confirm this time within the next 3 days' | translate }}</div>
                                </div>
                                <div class="not-available" v-else>{{ 'Retailer is not open on this day. Please, choose another day.' | translate }}</div>
                                <div class="cost">{{ 'Collection cost: Free' | translate }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-show="deliveryType === carriers.home_delivery && isDeliveryAvailable || !isCollectionAvailable" class="delivery-method home-delivery">
                    <p>{{{ deliveryInfoBlockContent | cmsBlock }}}</p>
                    <div class="row">
                        <p class="section-heading">
                            {{ 'Select your preferred delivery date:' | translate }}
                        </p>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <app-datepicker :identifier="carriers.home_delivery"></app-datepicker>
                        </div>

                        <div class="col-6">
                            <div class="info">
                                <div class="address-details">
                                    <p>{{ 'Your address:' | translate }}</p>
                                    <div v-if="deliveryAddress.streets.length > 0">
                                        <p v-for="street in deliveryAddress.streets" track-by="$index">{{{ street }}}</p>
                                        <p>{{{ deliveryAddress.city }}}</p>
                                        <p>{{{ deliveryAddress.region }}}</p>
                                        <p>{{{ deliveryAddress.country }}}</p>
                                        <p>{{{ deliveryAddress.postcode }}}</p>
                                    </div>
                                    <div v-else><strong>Address is not set.( Home delivery is not available )</strong></div>
                                </div>

                                <div class="chosed-date">
                                    <p>{{ 'You have selected:' | translate }}</p>
                                    <span>{{ deliveryDate }}</span>
                                </div>

                                <div class="delivery">
                                    <p>{{ 'Delivery Cost:' | translate }}</p>
                                    <span>{{ currencySymbol }}{{ deliveryPrice }}</span>
                                    <span>{{`Your ${vehicleBrandName} Retailer will contact you within the next 3 days to confirm your delivery date and time` | translate }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row button-set">
                    <div class="col-8">
                        <button v-if="collectionMethodPage == 2 && deliveryType == carriers.collection_free" class="button button-empty" @click="previousView()">{{ 'BACK' | translate }}</button>
                        <div v-else>&nbsp</div>
                    </div>
                    <div class="col-4">
                        <button class="button right save-button" :class="{ 'button-disabled': !this.canSave() }" @click="submit()">{{ 'SAVE AND CONTINUE' | translate }}</button>
                    </div>
                </div>
            </div>

            <div v-else class="row main-content">
                <p>{{ 'Delivery methods are not available' | translate }}</p>
            </div>
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
    import appMaps from 'bmw/components/Elements/Maps';
    import perfectScrollbar from 'perfect-scrollbar';
    import coreDelivery from 'core/components/Checkout/Delivery';

    export default coreDelivery.extend({
        props: {
            vehicleBrandName: {
                type: String,
                required: false,
                default: 'BMW'
            }
        },

        data() {
            return {
                allLoaded: false
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
            }
        },

        methods: {
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
                if (this.currentStoreIsSet()) {
                    if (this.collectionMethodPage === 1) {
                        this.nextView();
                    } else if (this.collectionMethodPage === 2 && this.activeTime) {
                        this.saveShipping();
                    }
                }
            },

            /**
             * Changes the delivery type
             *
             * @param {String} deliveryType
             */
            changeDeliveryType(deliveryType) {
                this.deliveryType = this.carriers[deliveryType];

                if (this.nextAvailableDate) {
                    this.updateCollectionDatepicker(moment(this.nextAvailableDate));
                    this.$broadcast('calendar::setDate', { id: this.carriers[deliveryType], date: this.nextAvailableDate });

                    if (deliveryType === 'collection_free') {
                        setTimeout(() => this.$broadcast('Map::reload', this.currentStore.id), 1);
                    }
                }
            },

            // update stores and turn off loader
            updateStoresData() {
                this.$super(coreDelivery, 'updateStoresData');
                this.allLoaded = true;
            },

            updateDeliveryAddress() {
                this.$super(coreDelivery, 'updateDeliveryAddress');

                // check if user has gone back and changed address
                if (this.allLoaded) {
                    this.allLoaded = false;

                    this.$broadcast('Map::reload', 0, true);
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
                this.deliveryDate = date.format('dddd Do MMMM');
                this.deliveryDateObject = date;
            }
        },

        ready() {
            this.initScrollbar();
        },

        components: {
            appMaps
        }
    });
</script>
