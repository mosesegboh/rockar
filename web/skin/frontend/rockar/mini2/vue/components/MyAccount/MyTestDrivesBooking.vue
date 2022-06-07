<script>
    import appMyTestDrivesBooking from 'dsp2/components/MyAccount/MyTestDrivesBooking';

    export default appMyTestDrivesBooking.extend({
        methods: {
            getCarTitle(drive) {
                return drive.vehicles[0].title;
            }
        }
    });
</script>
