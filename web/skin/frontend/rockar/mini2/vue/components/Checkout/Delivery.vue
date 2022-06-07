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
                <p>{{ `Getting behind the wheel of your new MINI has been made much simpler,
                    as we offer the choice of both pick up and delivery
                    to or from your favourite MINI retailer.` | translate }}</p>
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
                                        <div class="title" v-if="store.recommendedType === 'order'" :class="{'substep': actionsVisible}">
                                            {{ 'You placed an order at this retailer' | translate }}
                                        </div>
                                        <div class="title" v-if="store.recommendedType === 'youdrive'" :class="{'substep': actionsVisible}">
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
                                                            {{ 'Trading Hours' | translate }}
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
                            <p :class="{ 'actions': this.actionsVisible }">
                                {{ actionsVisible ? this.getHeadingText().retailersHeading : 'Select you new MINI family below' | translate }}
                            </p>
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
import appCheckoutDelivery from 'dsp2/components/Checkout/Delivery';
import appDatepicker from 'mini2/components/Elements/DatePicker';

export default appCheckoutDelivery.extend({
    methods: {
        getHeadingText() {
            if (!this.actionsVisible) {
                return {
                    retailersHeading: 'Select your new MINI family below:',
                    stepHeading: 'Collection / Delivery'
                };
            }

            return this.homeDeliveryVisible ?
                { retailersHeading: 'Schedule delivery', stepHeading: 'Collection / Delivery' } :
                { retailersHeading: 'Schedule collection', stepHeading: 'Collection / Delivery' };
        }
    },

    components: {
        appDatepicker
    }
});
</script>
