<template>
    <app-save-car
        :open="saveCarOverlay"
        :car-id="parseInt(carId)"
        :customer-name="customerName"
        :product-name="productTitle"
        :product-title="productName"
        :product-subtitle="productSubtitle"
        :my-account-url="myAccountUrl"
        :is-in-wishlist="isInWishlist"
        :is-ajax-request="isAjaxRequest"
        :save-wishlist-url="saveWishlistUrl"
        :remove-from-wishlist-url="removeFromWishlistUrl"
        :image="image"
        @toggle-is-in-wishlist="toggleIsInWishlist"
        @open-save-car-overlay="openSaveCarOverlay"
        @close-save="saveCarOverlay = false"
    ></app-save-car>
</template>

<script>
    import appSaveCar from 'dsp2/components/SaveCar';

    export default Vue.extend({
        components: {
            appSaveCar
        },

        props: {
            carId: {
                required: true,
                type: String
            },

            customerName: {
                required: true,
                type: String
            },

            productName: {
                required: true,
                type: String
            },

            productTitle: {
                required: true,
                type: String
            },

            productSubtitle: {
                required: true,
                type: String
            },

            myAccountUrl: {
                required: true,
                type: String
            },

            isInWishlist: {
                required: true,
                type: Boolean
            },

            isAjaxRequest: {
                required: true,
                type: Boolean
            },

            saveWishlistUrl: {
                required: true,
                type: String
            },

            removeFromWishlistUrl: {
                required: true,
                type: String
            },

            image: {
                required: true,
                type: String
            },

            ajaxLoading: {
                required: true,
                type: Boolean
            }
        },

        data() {
            return {
                saveCarOverlay: false
            }
        },

        methods: {
            toggleSaveCarOverlay() {
                if (this.customerName.toLowerCase() === 'guest') {
                    this.saveCarOverlay = !this.saveCarOverlay;
                }

                if (this.customerName.toLowerCase() !== 'guest' && this.isInWishlist === false) {
                    this.ajaxLoading = true;
                    this.$broadcast('SaveCar::addToWishlist');
                }

                if (this.customerName.toLowerCase() !== 'guest' && this.isInWishlist === true) {
                    this.saveCarOverlay = !this.saveCarOverlay;
                    this.$broadcast('SaveCar::removeFromWishlist');
                }
            },

            toggleIsInWishlist(value) {
                this.ajaxLoading = false;
                this.isInWishlist = value;
            },

            openSaveCarOverlay() {
                this.saveCarOverlay = true;
            }
        },

        events: {
            'openSaveTrigger'(carId) {
                if (carId === this.carId) {
                    this.toggleSaveCarOverlay();
                }
            },

            'CarComapreSaveCar::toggleIsInWishlist'(data) {
                if (data.productId === parseInt(this.carId)) {
                    this.toggleIsInWishlist(data.isInWishlist);
                }
            }
        }
    });
</script>
