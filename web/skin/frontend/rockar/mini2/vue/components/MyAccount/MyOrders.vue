<script>
    import appMyOrders from 'dsp2/components/MyAccount/MyOrders';

    export default appMyOrders.extend({
        computed: {
            carTitle() {
                return this.car.title;
            }
        }
    });
</script>
