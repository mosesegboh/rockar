<template>
    <div class="calendar-wrap">
        <div class="general-preloader" v-show="ajaxLoading"></div>

        <div class="calendar-content">
            <div class="calendar"></div>

            <section class="calendar-timeslots">
                <ul class="slots-list">
                    <li class="slot-block" v-for="(index, slots) in showDates">
                        <button @click="$dispatch('select-date', slot)">
                            {{ slots.time }}
                        </button>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';

    export default Vue.extend({
        props: {
            datesMap: {
                required: false,
                type: Object,
                default() { // Remove when BE is ready
                    return {
                        '07': [
                            { time: '13:00', slots: 15 },
                            { time: '14:00', slots: 10 },
                            { time: '15:00', slots: 10 },
                            { time: '16:00', slots: 10 }
                        ],

                        '08': [
                            { time: '13:00', slots: 10 },
                            { time: '14:00', slots: 15 }
                        ]
                    }
                }
            },

            disabledDays: {
                required: false,
                type: Array,
                default() { // Remove when BE is ready
                    return [10, 20, 30]
                }
            }
        },

        data() {
            return {
                ajaxLoading: false,
                showDates: []
            }
        },

        computed: {
            availableTimes() {
                const availableDays = [];

                Object.keys(this.datesMap).forEach((date) => {
                    const day = parseInt(moment(date, 'DD').format('D'));
                    availableDays.push(day);
                });

                return availableDays;
            }
        },

        methods: {
            getAvailableDates(newMonth, newYear) {
                this.ajaxLoading = true;

                // Change to AJAX request when BE is ready
                setTimeout(() => {
                    this.showDates = [];
                    this.ajaxLoading = false;

                    this.$set('datesMap', {
                        '14': [
                            { time: '13:00', slots: 15 },
                            { time: '14:00', slots: 10 },
                            { time: '15:00', slots: 10 },
                            { time: '16:00', slots: 10 }
                        ],

                        '15': [
                            { time: '13:00', slots: 10 },
                            { time: '14:00', slots: 15 }
                        ]
                    });

                    const $calendar = jQuery(this.$el).find('.calendar');
                    $calendar.datepicker('setDate', moment(`${newMonth}/${this.availableTimes[0]}/${newYear}`, 'M/D/YYYY').toDate());
                    $calendar.datepicker('refresh');
                    this.showDates = this.datesMap[moment(this.availableTimes[0], 'D').format('DD')];
                }, 2500);
            }
        },

        ready() {
            this.showDates = this.datesMap[moment(this.availableTimes[0], 'D').format('DD')];

            jQuery(this.$el).find('.calendar').datepicker({
                defaultDate: moment(this.availableTimes[0], 'D').toDate(),

                beforeShowDay: (date) => {
                    const day = parseInt(moment(date).format('D'));

                    if (this.availableTimes.indexOf(day) > -1) {
                        return [true];
                    }

                    return [false];
                },

                onSelect: (date) => {
                    const day = moment(date).format('DD');
                    this.showDates = this.datesMap[day];
                },

                onChangeMonthYear: (year, month) => {
                    this.showDates = [];
                    this.getAvailableDates(month, year);
                }
            });
        }
    });
</script>
