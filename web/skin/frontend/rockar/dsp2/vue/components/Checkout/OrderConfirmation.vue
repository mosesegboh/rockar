<template>
    <div v-if="orderData" class="order-confirmation">
        <div v-if="orderData.images.ex" class="col-12">
            <img :src="orderData.images.ex" :alt="'Exterior Image' | translate" />
        </div>
        <div class="order-confirmation-body">
            <div class="content">
                <p class="order-confirm-title">{{ `sheer driving pleasure awaits` | translate }}</p>
                <p class="order-confirm-subtitle">{{ 'You have successfully placed your order for a ' | translate }} {{ orderData.product.title }}</p>
                <template v-if="!tradeIn">
                    <p class="order-confirm-text" >
                        {{ "Your preferred BMW Retailer will contact you to confirm the details of your order. If you have any questions or queries, please don't hesitate to contact us on 0800 600 555 or customer.infoservice@bmw.co.za. You can also track and manage your order via your secure BMW account:" | translate }}
                    </p>
                </template>
                <template v-else>
                    <p class="order-confirm-text">
                        {{ 'Your preferred BMW Retailer will contact you to confirm the details of your request. The "Trade-In Value" of your vehicle is an estimate based on the information you\'ve provided. In order to calculate the actual value, we will need to conduct a physical vehicle inspection of the vehicle. The final or actual "Trade-In Value" for your vehicle will be confirmed once an authorised and approved BMW Retailer has inspected the vehicle.' | translate}}
                    </p>
                    <p class="order-confirm-text">
                        {{ "Once your quote has been amended by the Retailer. You can accept the Trade-In and place your order right here.If you have any questions or queries, please don't hesitate to contact us on 0800 600 555 or customer.infoservice@bmw.co.za. You can also track and manage your order via your secure BMW account:" | translate }}
                    </p>
                </template>
            </div>
            <div class="details">
                <div class="row">
                    <div class="title">
                        {{ 'Order Date' | translate }}
                    </div>
                    <div class="content">
                        {{ orderData.orderDate }}
                    </div>
                </div>
                <div class="row">
                    <div class="title">
                        {{ 'Order Number' | translate }}
                    </div>
                    <div class="content">
                        {{ orderData.orderNumber }}
                    </div>
                </div>
                <div class="row">
                    <div class="title">
                        {{ 'Collection' | translate }}
                    </div>
                    <div class="content" v-if="orderData.deliveryStore">
                        {{ orderData.deliveryStore }}
                    </div>
                </div>
                <div class="row">
                    <div class="title">
                        {{ getDeliveryTypeText() | translate }}
                    </div>
                    <div class="content" v-if="orderData.deliveryStore">
                        {{ orderData.deliveryDate }}
                    </div>
                </div>
                <a :href="myAccountUrlFull" :title="'My Account' | translate" v-if="!inStore" class="button">{{ 'Track order in my account' | translate }}</a>
                <a :href="logoutUrl" :title="'Logout' | translate" v-if="inStore" class="button button-empty">{{ 'Log out' | translate }}</a>
            </div>
        </div>
    </div>
</template>

<script>
    import coreOrderConfirmation from 'core/components/Checkout/OrderConfirmation';
    import EventTracker from 'dsp2/mixins/EventTracker';

    export default coreOrderConfirmation.extend({
        mixins: [EventTracker],

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
        },

        methods: {
            getDeliveryTypeText() {
                const helper = new ObjectHelpers(this.orderData);
                return helper.getValue('isHomeDelivery', false) ? 'Delivery Date' : 'Collection Date';
            }
        },

        ready() {
            /**
             * Fire event for tracking purposes on initial load of success page
             */
            this.fireEventForTracking(
                this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                this.getEventConstants().EVENTRACKERVALUES.CHECKOUTSUCCESS
            );
        }
    });
</script>
