<template>
    <div v-if="orderData" class="order-confirmation">
        <div v-if="orderData.images.ex" class="col-12">
            <img :src="orderData.images.ex" :alt="'Exterior Image' | translate" />
        </div>
        <div class="order-confirmation-body">
            <div class="content">
                <p class="order-confirm-title">{{ 'ON YOUR MARKS…' | translate }}</p>
                <p class="order-confirm-subtitle">{{ `And get set to go! Your order for a new ${orderData.product.title} has been successful!` | translate }}</p>
                <p class="order-confirm-subtitle second">{{ 'Congratulations! Your MINI’s been reserved and is being prepped for pick-up or delivery –we’re just getting all the paperwork in order.' | translate }}</p>
                <ul>
                    <li v-if="tradeIn">
                        <p class="order-confirm-text" >
                            {{ 'Should you be levelling up your MINI journey with a trade-in, remember to bring your current vehicle into your chosen dealership for a final assessment and value confirmation.' | translate }}
                        </p>
                    </li>
                    <li>
                        <p class="order-confirm-text" >
                            {{ 'To help us out, please be sure to ' | translate }}
                            <a :href="myAccountUrlFull"> {{ 'upload all relevant documents ' | translate }} </a>
                            {{ 'to your ' | translate }}
                            <a :href="myAccountUrlFull"> {{ 'MINI Account.' | translate }} </a>
                        </p>
                    </li>
                    <li>
                        <p class="order-confirm-text" >
                            {{ 'Remember: keep your phone on hand as your selected MINI retailer will be in touch shortly to make sure you’re as happy with your purchase as we are.' | translate }}
                        </p>
                    </li>
                    <li>
                        <p class="order-confirm-text" >
                            {{ 'You are always welcome to reach out, too. For questions and queries, please do not hesitate to contact us on 0800 600 555.' | translate }}
                        </p>
                    </li>
                    <li>
                        <p class="order-confirm-text" >
                            {{ 'Lastly: don’t forget that you can track and manage this order in your account.' | translate }}
                        </p>
                    </li>
                </ul>
                <div class="bottom">
                    <p>{{ 'That’s all! We can’t wait to see you on the streets!' | translate }}</p>
                </div>
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
                <a :href="myAccountUrlFull" :title="'My Account' | translate" v-if="!inStore" class="button dsp2-money">
                    {{ 'Track order in my account' | translate }}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import appCheckoutOrderConfirmation from 'dsp2/components/Checkout/OrderConfirmation';

    export default appCheckoutOrderConfirmation.extend({})
</script>
