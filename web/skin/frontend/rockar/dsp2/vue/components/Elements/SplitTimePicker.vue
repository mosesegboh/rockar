<template>
    <div id="timepicker" class="timepicker">
        <div class="additional-view mobile-table">
            <slot name="additional-block"></slot>
        </div>
        <div class="timepicker-calendar calendar"></div>
        <div class="timepicker-time-wrapper">
            <div class="additional-view desktop-only">
                <slot name="additional-block"></slot>
            </div>
            <div class="timepicker-time">
                <div class="timepicker-body desktop-only">
                    <div
                            v-for="time in times"
                            @click="available.indexOf($index) >= 0 ? selectTime($index) : ''"
                            :class="[
                                'timepicker-hour',
                                available.indexOf($index) < 0
                                    ? 'not-available'
                                    : 'available',
                                active == $index
                                    ? 'selected'
                                    : ''
                            ]">
                        {{ formatTimeForSelector(time) }}
                    </div>
                </div>
                <div class="timepicker-body mobile-table">
                    <app-select
                            class="grey-dropdown"
                            @select="updateSelectTime"
                            :init-selected="active"
                            :disabled="false"
                            :options="createSelect(times)"
                    ></app-select>
                </div>
                <h3 v-if="!times.length && ajaxLoading">{{ 'Currently loading available times' | translate }}</h3>
                <h3 v-if="!times.length && !ajaxLoading">{{ 'No time slots available for this day' | translate }}</h3>
            </div>
        </div>
    </div>
</template>

<script>
    import appTimepicker from 'core/components/Elements/TimePicker';
    import appSelect from 'core/components/Elements/Select';

    export default Vue.extend({
        mixins: [appTimepicker],

        methods: {
            createSelect(list) {
                const options = [];

                list.forEach((item, index) => {
                    if (this.available.indexOf(index) >= 0) {
                        options.push({
                            title: this.formatTimeForSelector(item),
                            value: index
                        });
                    }
                });

                return options;
            },

            updateSelectTime(option) {
                this.selectTime(option.value);
            },
        },

        components: {
            appSelect
        }
    });
</script>
