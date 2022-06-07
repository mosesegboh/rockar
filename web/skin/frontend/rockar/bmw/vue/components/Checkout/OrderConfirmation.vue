<template>
    <div v-if="orderData" class="order-confirmation">
        <p v-if="!tradeIn" class="order-confirm-subtitle">{{ 'Congratulations! You\'ve placed your order.' | translate }}</p>

        <p v-if="tradeIn" class="order-confirm-subtitle">{{ 'Quote successfully sent.' | translate }}</p>

        <div class="row">
            <div v-if="orderData.images.in" :class="getImageGridClass()">
                <img :src="orderData.images.in" :alt="'Interior Image' | translate" />
            </div>
            <div v-if="orderData.images.ex" :class="getImageGridClass()">
                <img :src="orderData.images.ex" :alt="'Exterior Image' | translate" />
            </div>
        </div>

        <p class="order-confirm-subtitle">{{ 'What happens now?' | translate }}</p>

        <div class="order-confirmation-body">
            <div v-if="!tradeIn">
                <template v-if="orderData.deliveryDate">
                    <p class="no-margin">{{ getDeliveryTypeText() | translate }}: <strong>{{ orderData.deliveryDate }}</strong> <span v-if="orderData.deliveryStore">({{ orderData.deliveryStore }})</span></p>
                    <p>({{ 'We will contact you closer to this date to confirm exact details' | translate }})</p>
                </template>

                <p>{{ 'Your Order Number' | translate }}: <strong>{{ orderData.orderNumber }}</strong></p>

                <p v-if="orderData.couponCode">{{ 'Coupon Code Used' | translate }}: <strong>{{ orderData.couponCode }}</strong></p>

                <p :class="orderData.isHomeDelivery ? 'no-margin' : ''" v-if="!orderData.isHire">{{ 'Deposit taken' | translate }}: <strong>{{ orderData.fullDeposit | numberFormat '0,00.00' }}</strong></p>
                <p :class="orderData.isHomeDelivery ? 'no-margin' : ''" v-else>{{ 'Initial Payment' | translate }}: <strong>{{ orderData.fullDeposit | numberFormat '0,00.00' }}</strong></p>

                <p v-if="orderData.depositTakes != orderData.fullDeposit">
                    (<span><strong>{{ orderData.depositTakes | numberFormat '0,00.00' }}</strong> {{ 'cash deposit' | translate }}</span>
                    <span v-if="orderData.deliveryCharge > 0">and <strong>{{ orderData.deliveryCharge | numberFormat '0,00.00' }}</strong> {{ 'delivery fee' | translate }}</span>
                    <span v-if="orderData.surcharge > 0">and <strong>{{ orderData.surcharge | numberFormat '0,00.00' }}</strong> {{ 'credit card surcharge' | translate }}</span>)
                </p>

                {{{ successMessage | cmsBlock }}}
            </div>

            <div v-if="tradeIn">
                <p>
                    The "Trade-In Value of your vehicle is an estimate based on the information you've provided.
                    In order to calculate the actual value, we will need to conduct a physical vehicle inspection of the vehicle.
                    The final or actual "Trade-In Value" for your vehicle will be confirmed once an authorised and approved BMW Retailer has inspected the vehicle.
                </p>
                <br>
                <p>Once your quote has been amended by the Retailer. You can accept the Trade-In and place your order right here</p>
            </div>

            <a :href="myAccountUrlFull" :title="'My Account' | translate" v-if="!inStore" class="button">{{ 'Go to My Profile' | translate }}</a>
            <a :href="logoutUrl" :title="'Logout' | translate" v-if="inStore" class="button button-empty">{{ 'Log out' | translate }}</a>
        </div>
    </div>
</template>

<script>
    import coreOrderConfirmation from 'core/components/Checkout/OrderConfirmation';

    export default coreOrderConfirmation.extend({
        components: {
            coreOrderConfirmation
        },

        props: {
            partExchangeValue: {
                required: false,
                type: String,
                default: ''
            }
        },

        computed: {
            tradeIn() {
                return this.partExchangeValue !== '';
            }
        }
    });
</script>
