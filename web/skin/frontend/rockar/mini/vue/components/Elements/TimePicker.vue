<template>
    <div id="timepicker" class="timepicker">
        <div class="timepicker-calendar calendar"></div>
        <div class="timepicker-time">
            <h3 v-if="titleOnTop" class="timepicker-title">{{ selectedDatetime }}</h3>
            <div class="timepicker-body">
                <div
                        v-for="time in times"
                        @click="available.indexOf($index) >= 0 ? selectTime($index) : ''"
                        :class="['timepicker-hour', available.indexOf($index) < 0 ? 'not-available' : 'available', active == $index ? 'selected' : '']">
                    {{ formatTimeForSelector(time) }}
                </div>
            </div>
            <h3 v-if="!titleOnTop" class="timepicker-title">{{ selectedDatetime }}</h3>
            <h3 v-if="!times.length && ajaxLoading">{{ 'Currently loading available times' | translate }}</h3>
            <h3 v-if="!times.length && !ajaxLoading">{{ 'No time slots available for this day' | translate }}</h3>
        </div>
    </div>
</template>

<script>

    import moment from 'moment';

    export default Vue.extend({
        props: {
            availableTimesUrl: {
                required: true,
                type: String
            },
            ajaxLoading: {
                required: false,
                type: Boolean
            },
            requestData: {
                required: false,
                type: Object,
                default: {}
            },
            localStore: {
                required: true,
                type: Object
            },
            selectedModelData: {
                required: true,
                type: Object
            },
            identifier: {
                type: String,
                required: false
            },
            titleOnTop: {
                required: false,
                type: Boolean,
                default: false
            },
            vehicleIds: {
                required: false,
                type: Array,
                default: []
            },
            firstAvailableDateUrl: {
                required: false,
                type: String
            }
        },

        data() {
            return {
                times: [],
                available: [],
                active: 0,
                loaded: false,
                selectedDate: moment(),
                selectedTime: moment(),
                nextAvailableDate: new Date()
            }
        },

        computed: {
            selectedDatetime() {
                if (typeof this.active === 'undefined') {
                    return null;
                }
                return `${this.selectedDate.format('dddd Do MMMM')} - ${this.selectedTime.format('ha')}`;
            }
        },

        methods: {
            formatTimeForSelector(time) {
                return moment(time, 'HH:mm').format('ha')
            },

            selectTime(index) {
                this.active = index;
                this.selectedTime = moment(this.times[this.active], 'HH:mm');
                this.datetimeUpdated();
            },

            selectDate(date) {
                if (this.selectedDate !== moment(date, 'MM/D/YYYY')) {
                    this.selectedDate = moment(date, 'MM/D/YYYY');
                    this.getAvailableTimes();
                }
            },

            selectDateManually(date) {
                this.selectedModelData.bookingDatetime = moment.invalid();

                this.selectDate(date);
            },

            getAvailableTimes() {
                this.ajaxLoading = true;

                this.$http({
                    url: this.availableTimesUrl,
                    method: 'GET',
                    emulateJSON: true,
                    data: {
                        date: this.selectedDate.format('YYYY-MM-DD'),
                        localStoreId: this.localStore.id,
                        vehicleIds: this.vehicleIds
                    }
                }).then(this.getAvailableTimesSuccess, this.requestFail);
            },

            getAvailableTimesSuccess(resp) {
                this.ajaxLoading = false;
                this.times = [];
                this.available = [];

                if (resp.data && resp.data.hasOwnProperty('available') && resp.data.hasOwnProperty('times')) {
                    this.available = resp.data.available;
                    this.times = resp.data.times;
                    let index = false;

                    if (this.selectedModelData.bookingDatetime.isValid()) {
                        for (let i = 0; i < this.available.length; i++) {
                            if (this.available[i] !== false) {
                                if (this.times[this.available[i]] === this.selectedModelData.bookingDatetime.format('HH:mm')) {
                                    index = this.available[i];
                                    break;
                                }
                            }
                        }
                    }

                    if (index === false) {
                        for (let i = 0; i < this.available.length; i++) {
                            if (this.available[i] !== false) {
                                index = this.available[i];
                                break;
                            }
                        }
                    }

                    if (index !== false) {
                        this.selectTime(index);
                    }
                }

                this.datetimeUpdated();
            },

            datetimeUpdated() {
                this.$dispatch(
                    'TimePicker::updated',
                    (typeof this.active !== 'undefined' && this.available.filter(time => time !== false).length) ?
                        moment(`${this.selectedDate.format('YYYY-MM-DD')} ${this.selectedTime.format('HH:mm:ss')}`) :
                        false
                );
            },

            requestFail() {
                this.ajaxLoading = false;
            },

            /**
             * Get first available date with timeslots in it
             */
            getFirstAvailableDate() {
                this.ajaxLoading = true;

                this.$http({
                    url: this.firstAvailableDateUrl,
                    method: 'GET',
                    emulateJSON: true,
                    data: {
                        localStoreId: this.localStore.id,
                        localStoreCode: this.localStore.code,
                        vehicleIds: this.vehicleIds
                    }
                }).then(this.getFirstAvailableDateSuccess, this.requestFail);
            },

            /**
             * On success, trigger available date selection in calendar
             *
             * @param resp
             */
            getFirstAvailableDateSuccess(resp) {
                this.nextAvailableDate = new Date(resp.data.year, resp.data.month, resp.data.day);
                this.$emit('TimePicker::chooseAvailableDate');
            }
        },

        events: {
            'TimePicker::getAvailableTimes'() {
                if (!this.loaded) {
                    this.getAvailableTimes();
                    this.loaded = true;
                }
            },

            'TimePicker::resetTimeSlots'() {
                this.loaded = false;
            },

            'TimePicker::setAvailableDates'(obj) {
                if (this.identifier === obj.id) {
                    jQuery(this.$el).find('.timepicker-calendar').datepicker('option', 'beforeShowDay', (date) => {
                        var calendarDate = moment(moment(date), 'YYYY-MM-DD');
                        var isAvailable = true;
                        var cssClass = '';
                        var title = '';

                        if (obj.hasOwnProperty('holidays')) {
                            if (obj.holidays !== undefined) {
                                obj.holidays.forEach((el) => {
                                    if (calendarDate.format('DD/MM/YYYY') === el) {
                                        isAvailable = false;
                                        cssClass = 'holiday';
                                        title = 'Not Available';
                                    }
                                });
                            }
                        }

                        return [isAvailable, cssClass, title];
                    });
                } else {
                    return [true, '', ''];
                }
            },

            'TimePicker::chooseAvailableDate'() {
                const nextAvailableDate = moment(this.nextAvailableDate).format('MM/D/YYYY');
                const availableDate = this.selectedModelData.bookingDatetime.isValid() ?
                    this.selectedModelData.bookingDatetime.format('MM/D/YYYY') :
                    nextAvailableDate;

                this.selectDate(availableDate);

                jQuery(this.$el).find('.timepicker-calendar').datepicker('option', 'minDate', nextAvailableDate);
                jQuery(this.$el).find('.timepicker-calendar').datepicker('setDate', availableDate);
            },

            'TimePicker::getFirstAvailableDate'() {
                this.getFirstAvailableDate();
                this.datetimeUpdated();
            }
        },

        ready() {
            jQuery(this.$el).find('.timepicker-calendar').datepicker({
                minDate: 0,
                prevText: '',
                nextText: '',
                showOtherMonths: true,
                selectOtherMonths: false,
                onSelect: this.selectDateManually
            });
        }
    });
</script>
