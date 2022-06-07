<script>
    import appSaveCar from 'dsp2/components/SaveCar';
    import translateString from 'core/filters/Translate';

    export default appSaveCar.extend({
        computed: {
            logInText() {
                return this.translateString('Login/Register to add this MINI to your Wishlist.');
            },

            addedText() {
                return this.translateString('This MINI has been added to your Wishlist.');
            },

            removeText() {
                return this.translateString('Are you sure you want to remove this vehicle from your Wishlist?');
            },

            removeConfirmationButtonText() {
                return this.translateString('Yes, I\'m sure');
            },

            viewWishlistButtonText() {
                return this.translateString('View My Wishlist');
            },

            titleText() {
                return this.productName;
            }
        },

        methods: {
            translateString
        }
    });
</script>
