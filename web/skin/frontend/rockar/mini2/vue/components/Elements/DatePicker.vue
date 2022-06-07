<template>
    <div id="timepicker">
        <div class="timepicker-calendar calendar"></div>
    </div>
</template>

<script>
    import appDatepicker from 'dsp2/components/Elements/DatePicker';

    export default appDatepicker.extend({
        ready() {
            jQuery(this.$el).find('.timepicker-calendar')
                .datepicker('option', {
                    dayNamesMin: ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT']
                });
        }
    });
</script>
