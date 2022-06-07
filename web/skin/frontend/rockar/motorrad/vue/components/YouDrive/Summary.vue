<template>
    <div class="summary-block">
        <p class="summary-title">
            {{ `${testDriveTitle} Summary` | translate }}
        </p>
        <div class="drive-car local-store">
            <p class="store-name">{{ localStore.name }}</p>
            <p class="address small-font">
                <span v-if="localStore.street">{{ localStore.street }}</span>
                <span v-if="localStore.city">{{ localStore.city }}</span>
                <span v-if="localStore.state">{{ localStore.state }}</span>
                <span v-if="localStore.postal_code">{{ localStore.postal_code }},</span>
            </p>
            <p class="address small-font">
                <span v-if="localStore.main_phone">{{ localStore.main_phone }}</span>
            </p>
        </div>

        <div class="your-test-drive" v-if="selectedModelData.id">
            <div class="booked-time" v-if="selectedModelData.bookingDatetime.isValid()">
                <p class="date-time">
                    {{ selectedModelData.bookingDatetime.format('dddd Do MMMM YYYY') }}
                </p>
                <p class="date-time">
                    {{ selectedModelData.bookingDatetime.format('h:mm a') }}
                </p>
            </div>
            <div class="selected-car-wrapper">
                <div class="selected-car-data">
                    <img :src="selectedModelData.image" :alt="selectedModelData.title">
                    <span class="selected-car-title">{{ selectedModelData.title }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import moment from 'moment';
    import appConfirmationModal from 'core/components/Elements/ConfirmationModal';

    export default Vue.extend({
        props: {
            localStore: {
                required: true,
                type: Object
            },
            currentState: {
                required: false,
                type: Number
            },
            selectedModelData: {
                required: false,
                type: Object
            },
            testDriveTitle: {
                required: true,
                type: String
            }
        },

        methods: {
            chooseDealer() {
                this.currentState = 1;
            },

            chooseTime() {
                this.currentState = 2;
            },

            isSelected(obj) {
                return obj.selected === true || obj.selected === 'true';
            },

            removeCar(bookingId) {
                this.$dispatch('YouDrive::cancelDrive', bookingId);
            },

            postBookingClearAction() {
                this.$dispatch('YouDrive::postBookingClearAction');
            }

        },
        events: {
            'Summary::clearData'(bookingId) {
                const tempData = {
                    'id': 0,
                    'carId': 0,
                    'title': null,
                    'subtitle': null,
                    'options': [],
                    'image': null,
                    'bookingId': 0,
                    'bookingDatetime': moment(),
                    'bookingDatetimeSet': false
                };

                Object.assign(this.selectedModelData, tempData);
                this.postBookingClearAction();
            }
        },

        components: {
            appConfirmationModal
        }
    });
</script>
