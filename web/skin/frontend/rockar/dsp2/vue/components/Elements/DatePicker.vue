<template>
    <div id="timepicker">
        <div class="timepicker-calendar calendar"></div>
    </div>
</template>

<script>
    import moment from 'moment';

    export default Vue.extend({
        props: {
            identifier: {
                type: String,
                required: false
            },

            nextAvailableDay: {
                type: [String, Boolean],
                required: false,
                default() {
                    return moment().format('dddd Do MMMM')
                }
            }
        },

        data() {
            return {
                active: 0,
                selectedDate: moment().format('dddd Do MMMM'),
                activeDayMap: {}
            }
        },

        methods: {
            /**
             * Select date by calendar ID
             *
             * @Todo : prevent conditional statement hardcoding if possible
             */
            selectDate(date) {
                this.selectedDate = moment(date);

                if (this.identifier === 'matrixrate') {
                    this.$dispatch('Delivery::deliveryDate', this.selectedDate);
                }

                if (this.identifier === 'flatrate') {
                    this.$dispatch('Delivery::collectionDate', this.selectedDate);
                }

                if (this.identifier === 'service_calendar') {
                    this.$dispatch('ServiceBooking::bookingDate', this.selectedDate);
                }
            },

            changeMonth(year, month) {
                if (this.identifier === 'service_calendar') {
                    this.$dispatch('ServiceBooking::updateAvailability', year, month);
                }
            },

            validateDate(date) {
                var calendarDate = moment(moment(date), 'YYYY-MM-DD');

                if (this.activeDayMap.hasOwnProperty('futureDays') && this.activeDayMap.futureDays !== undefined) {
                    if (this.activeDayMap.futureDays.hasOwnProperty(calendarDate.format('YYYY-MM-DD'))) {
                        return [true, '', ''];
                    }

                    return [false, 'not-available', 'Not Available'];
                } else {
                    var isAvailable = true;
                    var cssClass = '';
                    var title = '';

                    if (this.activeDayMap.hasOwnProperty('holidays')) {
                        if (this.activeDayMap.holidays !== undefined) {
                            this.activeDayMap.holidays.forEach((el) => {
                                if (calendarDate.format('DD/MM/YYYY') === el) {
                                    isAvailable = false;
                                    cssClass = 'holiday';
                                    title = 'Not Available';
                                }
                            });
                        }
                    }

                    var fromDate = moment(this.activeDayMap.startDate).add(this.activeDayMap.range, 'day');
                    if (calendarDate.isSameOrAfter(fromDate.format('YYYY-MM-DD'))) {
                        cssClass = 'not-available';
                        title = 'Not Available';
                    }

                    return [isAvailable, cssClass, title];
                }
            }
        },

        events: {
            /**
             * Set offset days by calendar ID
             *
             * @param obj
             */
            'calendar::setDate'(obj) {
                if (this.identifier === obj.id) {
                    jQuery(this.$el).find('.timepicker-calendar').datepicker('setDate', obj.date);

                    // @Todo: date assignment to be carefully refactored to prevent picking date from calendar instance
                    var currentDate = jQuery(this.$el).find('.timepicker-calendar').datepicker('getDate');
                    this.selectedDate = moment(currentDate);
                    this.$dispatch('Delivery::setDates', { id: obj.id, date: this.selectedDate });
                }
            },

            /**
             * Set min date by calendar ID
             *
             * @param obj
             */
            'calendar::setMinDate'(obj) {
                if (this.identifier === obj.id) {
                    jQuery(this.$el).find('.timepicker-calendar').datepicker('option', 'minDate', obj.date);
                }
            },

            'calendar::setActiveDayMap'(obj) {
                if (this.identifier === obj.id) {
                    this.activeDayMap = obj;
                }
            }
        },

        ready() {
            jQuery(this.$el).find('.timepicker-calendar').datepicker({
                minDate: 0,
                prevText: '',
                nextText: '',
                showOtherMonths: true,
                selectOtherMonths: false,
                onSelect: this.selectDate,
                onChangeMonthYear: this.changeMonth,
                beforeShowDay: this.validateDate
            });
        }
    });
</script>
