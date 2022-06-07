<template>
    <div class="my-account-wrapper my-test-drives">
        <div class="my-test-drives-wrapper">
            <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

            <template v-if="activeTestDrives.length">
                <div class="my-account-header">
                    <p class="my-account-heading h-common">
                        {{ 'Upcoming test drives' | translate }}
                    </p>
                </div>

                <div class="my-account-content upcoming-test-drives">
                    <div class="upcoming-test-drive" v-for="drive in activeTestDrives">
                        <div class="row">
                            <div class="col-3 car-cover">
                                <img :src="drive.vehicles[0].image" :alt="drive.vehicles[0].title" />
                            </div>

                            <div class="col-3 car-detail">
                                <h4>
                                    {{ drive.vehicles[0].title }}
                                </h4>

                                <p class="h-common h-small block-title">
                                    {{ drive.vehicles[0].model }}
                                </p>
                            </div>

                            <div class="col-6 car-extras" v-if="drive.vehicles[0].extras && drive.vehicles[0].extras.length > 0">
                                <p class="h-common h-small block-title">
                                    {{ 'Extras on this car' | translate }}:
                                </p>

                                <ul>
                                    <li class="options-item" v-for="extra in drive.vehicles[0].extras">
                                        <p>
                                            {{ extra }}
                                        </p>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="row test-drive-location-picker">
                            <div class="col-4">
                                <p>
                                    {{ drive.time | dateFormat 'dddd MMMM Do - ha' 'ddd MMM Do YYYY - hh:mm A' }}
                                </p>
                            </div>

                            <div class="col-4">
                                <p>
                                    {{ drive.place }}
                                </p>
                            </div>

                            <div class="col-4 align-right-tablet two-buttons">
                                <button class="button-narrow button-edit" :class="['edit-' + drive.id]" @click="editDrive(drive.id)" v-if="drive.vehicles[0].enabled">
                                    {{ 'Edit' | translate }}
                                </button>

                                <app-confirmation-modal :confirmation-question="'Do you really want to cancel?' | translate">
                                    <button class="button-empty button-narrow" :class="['cancel-' + drive.id]" @click="cancelDrive(drive.id)">
                                        {{ 'Cancel' | translate }}
                                    </button>
                                </app-confirmation-modal>
                            </div>
                        </div>

                        <app-maps
                            :js-api="false"
                            :api-key="mapApiKey"
                            :zoom="10"
                            :query="drive.place"
                        ></app-maps>
                    </div>

                    <p v-if="!activeTestDrives.length">
                        {{ 'You currently don\'t have any active test drive' | translate }}
                    </p>
                </div>
            </template>

            <template v-if="testDriveRequests.length">
                <div class="my-account-header">
                    <p class="my-account-heading h-common">
                        {{ 'Upcoming test drive requests' | translate }}
                    </p>
                </div>

                <div class="my-account-content test-drive-requests">
                    <div class="upcoming-test-drive" v-for="drive in testDriveRequests">
                        <div class="row">
                            <div class="col-3 car-cover">
                                <img :src="drive.vehicles[0].image" :alt="drive.vehicles[0].title" />
                            </div>

                            <div class="col-3 car-detail">
                                <h4>
                                    {{ drive.vehicles[0].title }}
                                </h4>

                                <p class="h-common h-small block-title">
                                    {{ drive.vehicles[0].model }}
                                </p>
                            </div>

                            <div class="col-6 car-extras" v-if="drive.vehicles[0].extras && drive.vehicles[0].extras.length > 0">
                                <p class="h-common h-small block-title">
                                    {{ 'Extras on this car' | translate }}:
                                </p>

                                <ul>
                                    <li class="options-item" v-for="extra in drive.vehicles[0].extras">
                                        <p>
                                            {{ extra }}
                                        </p>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="row test-drive-location-picker">

                            <div class="col-4">
                                <p>
                                    {{ drive.place }}
                                </p>
                            </div>

                            <div class="col-8 align-right-tablet">
                                <app-confirmation-modal :confirmation-question="'Do you really want to cancel?' | translate">
                                    <button class="button-empty button-narrow" :class="['cancel-' + drive.id]" @click="cancelDrive(drive.id)">
                                        {{ 'Cancel' | translate }}
                                    </button>
                                </app-confirmation-modal>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template v-if="cancelledTestDrives.length">
                <div class="my-account-header">
                    <p class="my-account-heading h-common">
                        {{ 'Uncompleted test drives' | translate }}
                    </p>
                </div>

                <div class="my-account-content cancelled-test-drives">
                    <div class="cancelled-test-drive" v-for="drive in cancelledTestDrives">
                        <div class="row">
                            <div class="col-3 car-cover">
                                <img :src="drive.vehicles[0].image" :alt="drive.vehicles[0].title">
                            </div>

                            <div class="col-3 car-detail">
                                <h4>
                                    {{ drive.vehicles[0].title }}
                                </h4>

                                <p class="h-common h-small block-title">
                                    {{ drive.vehicles[0].model }}
                                </p>
                            </div>

                            <div class="col-6 car-extras" v-if="drive.vehicles[0].extras && drive.vehicles[0].extras.length > 0">
                                <p class="h-common h-small block-title">
                                    {{ 'Extras on this car' | translate }}:
                                </p>

                                <ul>
                                    <li class="options-item" v-for="extra in drive.vehicles[0].extras">
                                        {{ extra }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="row test-drive-location-picker">
                            <div class="col-5" v-show="drive.booked_on">
                                <p>
                                    {{ drive.time | dateFormat 'dddd MMMM Do - ha' 'ddd MMM Do YYYY - hh:mm A' }}
                                </p>
                            </div>

                            <div class="col-5">
                                <p>
                                    {{ drive.place }}
                                </p>
                            </div>

                            <div class="col-2 align-right-tablet">
                                <button
                                    class="button-narrow button-rebook"
                                    @click="editDrive(drive.id)"
                                    v-if="drive.vehicles[0].enabled && drive.booked_on"
                                >
                                    {{ 'Re-book' | translate }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <p v-if="!cancelledTestDrives.length">
                        {{ 'You currently don\'t have any uncompleted test drive' | translate }}
                    </p>
                </div>
            </template>

            <template v-if="completedTestDrives.length">
                <div class="my-account-header">
                    <p class="my-account-heading h-common">
                        {{ 'Completed test drives' | translate }}
                    </p>
                </div>

                <div class="my-account-content completed-test-drives">
                    <div class="completed-test-drive" v-for="drive in completedTestDrives">
                        <div class="row">
                            <div class="col-3 car-cover">
                                <img :src="drive.vehicles[0].image" :alt="drive.vehicles[0].title" />
                            </div>

                            <div class="col-3 car-detail">
                                <h4>
                                    {{ drive.vehicles[0].title }}
                                </h4>

                                <p class="h-common h-small block-title">
                                    {{ drive.vehicles[0].model }}
                                </p>
                            </div>

                            <div class="col-6 car-extras" v-if="drive.vehicles[0].extras && drive.vehicles[0].extras.length > 0">
                                <p class="h-common h-small block-title">
                                    {{ 'Extras on this car' | translate }}:
                                </p>

                                <ul>
                                    <li class="options-item" v-for="extra in drive.vehicles[0].extras">
                                        {{ extra }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="row test-drive-location-picker">
                            <div class="col-5">
                                <p>
                                    {{ drive.time | dateFormat 'dddd MMMM Do - ha' 'ddd MMM Do YYYY - hh:mm A' }}
                                </p>
                            </div>

                            <div class="col-5">
                                <p>
                                    {{ drive.place }}
                                </p>
                            </div>

                            <div class="col-2 align-right-tablet">
                                <button v-if="drive.vehicles[0].chooseUrl" class="button-narrow" @click="chooseDrive(drive.vehicles[0].chooseUrl)">
                                    {{ 'Choose' | translate }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <p v-if="!completedTestDrives.length">
                        {{ 'You currently don\'t have any completed test drive' | translate }}
                    </p>
                </div>
            </template>

        </div>

        <div class="my-test-drives-mobile">

            <template v-if="activeTestDrives.length">
                <div class="my-account-header">
                    <p class="my-account-heading h-common">{{ 'Upcoming test drives' | translate }}</p>
                </div>

                <div class="hero-car" v-for="drive in activeTestDrives">
                    <div class="hero-content">
                        <div class="hero-image">
                            <img :src="drive.vehicles[0].image" :alt="drive.vehicles[0].title">
                        </div>

                        <div class="hero-info">
                            <div class="hero-title">
                                <p class="h4">
                                    {{ drive.vehicles[0].title }}
                                </p>

                                <p class="h5">
                                    {{ drive.vehicles[0].model }}
                                </p>
                            </div>

                            <div class="hero-date">
                                <p class="h4">
                                    {{ 'Date, Time and Location' | translate }}
                                </p>

                                <p>
                                    {{ drive.time | dateFormat 'dddd MMMM Do - ha' 'ddd MMM Do YYYY - hh:mm A' }} {{ 'at' | translate }} <span>{{ drive.place }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="hero-buttons">
                        <button class="button button-edit" @click="editDrive(drive.id)" v-if="drive.vehicles[0].enabled">
                            {{ 'Edit' | translate }}
                        </button>

                        <app-confirmation-modal :confirmation-question="'Do you really want to cancel?' | translate">
                            <button class="button-empty button-narrow" @click="cancelDrive(drive.id)">
                                {{ 'Cancel the Booking' | translate }}
                            </button>
                        </app-confirmation-modal>
                    </div>
                </div>
            </template>

            <template v-if="testDriveRequests.length">
                <div class="my-account-header">
                    <p class="my-account-heading h-common">{{ 'Upcoming test drive requests' | translate }}</p>
                </div>

                <div class="hero-car" v-for="drive in testDriveRequests">
                    <div class="hero-content">
                        <div class="hero-image">
                            <img :src="drive.vehicles[0].image" :alt="drive.vehicles[0].title">
                        </div>

                        <div class="hero-info">
                            <div class="hero-title">
                                <p class="h4">
                                    {{ drive.vehicles[0].title }}
                                </p>

                                <p class="h5">
                                    {{ drive.vehicles[0].model }}
                                </p>
                            </div>

                            <div class="hero-date">
                                <p>
                                    <span>{{ drive.place }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="hero-buttons">
                        <app-confirmation-modal :confirmation-question="'Do you really want to cancel?' | translate">
                            <button class="button-empty button-narrow" @click="cancelDrive(drive.id)">
                                {{ 'Cancel the Request' | translate }}
                            </button>
                        </app-confirmation-modal>
                    </div>
                </div>
            </template>

            <template v-if="cancelledTestDrives.length">
                <div class="my-account-header">
                    <p class="my-account-heading h-common">{{ 'Uncompleted test drives' | translate }}</p>
                </div>

                <p v-if="!cancelledTestDrives.length">
                    {{ 'You currently don\'t have any uncompleted test drive' | translate }}
                </p>

                <div class="hero-car" v-for="drive in cancelledTestDrives">
                    <div class="hero-content">
                        <div class="hero-image">
                            <img :src="drive.vehicles[0].image" :alt="drive.vehicles[0].title">
                        </div>

                        <div class="hero-info">
                            <div class="hero-title">
                                <p class="h4">
                                    {{ drive.vehicles[0].title }}
                                </p>

                                <p class="h5">
                                    {{ drive.vehicles[0].model }}
                                </p>
                            </div>

                            <div class="hero-date">
                                <p class="h4">
                                    {{ 'Date, Time and Location' | translate }}
                                </p>

                                <p class="h5">
                                    <span v-show="drive.booked_on">
                                        {{ drive.time | dateFormat 'dddd MMMM Do - ha' 'ddd MMM Do YYYY - hh:mm A' }} {{ 'at' | translate }}
                                    </span>
                                    {{ drive.place }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="hero-buttons">
                        <button
                            class="button button-rebook"
                            @click="editDrive(drive.id)"
                            v-if="drive.vehicles[0].enabled && drive.booked_on"
                        >
                            {{ 'Re-book' | translate }}
                        </button>
                    </div>
                </div>
            </template>

            <template v-if="completedTestDrives.length">
                <div class="my-account-header">
                    <p class="my-account-heading h-common">
                        {{ 'Completed test drives' | translate }}
                    </p>
                </div>

                <p v-if="!completedTestDrives.length">
                    {{ 'You currently don\'t have any completed test drive' | translate }}
                </p>

                <div class="hero-car" v-for="drive in completedTestDrives">
                    <div class="hero-content">
                        <div class="hero-image">
                            <img :src="drive.vehicles[0].image" :alt="drive.vehicles[0].title">
                        </div>

                        <div class="hero-info">
                            <div class="hero-title">
                                <p class="h4">
                                    {{ drive.vehicles[0].title }}
                                </p>

                                <p class="h5">
                                    {{ drive.vehicles[0].model }}
                                </p>
                            </div>

                            <div class="hero-date">
                                <p class="h4">
                                    {{ 'Date, Time and Location' | translate }}
                                </p>

                                <p class="h5">
                                    {{ drive.time | dateFormat 'dddd MMMM Do - ha' 'ddd MMM Do YYYY - hh:mm A' }} {{ 'at' | translate }} <span>{{ drive.place }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="hero-buttons">
                        <button v-if="drive.vehicles[0].chooseUrl && drive.vehicles[0].enabled" class="button" @click="chooseDrive(drive.vehicles[0].chooseUrl)">
                            {{ 'Choose' | translate }}
                        </button>
                    </div>
                </div>
            </template>

        </div>
    </div>
</template>

<script>
    import coreAppMyTestDrives from 'core/components/MyAccount/MyTestDrives';
    import appMaps from 'mini/components/Elements/Maps';

    export default coreAppMyTestDrives.extend({
        props: {
            testDriveRequests: {
                required: true,
                type: Array
            },
        },
        methods: {
            cancelDriveSuccess(resp) {
                if (resp.data.bookingId) {
                    this.removeCanceledTestDrives(resp.data.bookingId);
                    this.removeCanceledTestDriveRequests(resp.data.bookingId);
                }

                this.ajaxLoading = false;
            },

            removeCanceledTestDrives(bookingId) {
                var index = null;

                this.activeTestDrives.forEach((e, i) => {
                    if (e.id === bookingId) {
                        index = i;
                    }
                });

                if (index !== null) {
                    this.cancelledTestDrives.push(this.activeTestDrives[index]);
                    this.activeTestDrives.splice(index, 1);
                }
            },

            removeCanceledTestDriveRequests(bookingId) {
                var index = null;

                this.testDriveRequests.forEach((e, i) => {
                    if (e.id === bookingId) {
                        index = i;
                    }
                });

                if (index !== null) {
                    this.cancelledTestDrives.push(this.testDriveRequests[index]);
                    this.testDriveRequests.splice(index, 1);
                }
            }
        },

        components: {
            appMaps
        }
    });
</script>
