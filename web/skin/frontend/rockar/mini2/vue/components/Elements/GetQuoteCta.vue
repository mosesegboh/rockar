<template>
    <div>
        <button @click="getQuoteCta" class="button dsp2-outline get-quote-cta-icon">
            {{ 'Email Quote' | translate }}
        </button>
        <app-modal
            :show.sync="popupOpen"
            class-name="simple-popup"
            @close-popup="reopenFinanceQuote"
            step-parent=".finance-quote-wrapper"
        >
            <div slot="content">
                <div class="get-quote-popup" v-if="showPopupStep('cta')">
                    <header class="modal-header">
                        {{ 'Please Send Me a Quote.' | translate }}
                    </header>
                    <div class="row">
                        <p>
                            {{ 'A quote for ' | translate }}
                            {{ quoteName }}
                            {{ 'will be emailed to you.' | translate }}
                        </p>
                    </div>
                    <div class="row actions-row">
                        <button
                            type="button"
                            @click="getQuote"
                            class="button"
                        >
                            {{ 'Email Quote' | translate }}
                        </button>
                    </div>
                </div>

                <div class="get-quote-popup-success" v-if="showPopupStep('success')">
                    <header class="modal-header">
                        {{ 'Your Quote Has Been Sent.' | translate }}
                    </header>
                    <div class="row">
                        <p>
                            {{ `High five! An indicative quote should be hitting your inbox now now. To make your life easier, we’ve also added your ` | translate }}
                            {{ quoteName }}
                            {{ `to the wish list in` | translate }}
                            <a :href="myAccountUrl" class="my-account-link">
                                {{ 'your account' | translate }}
                            </a>
                            {{ `Go ahead and reserve it. We know you want to.` | translate }}
                        </p>
                        <p class="notes">
                            {{`Just a note: requesting a quote or adding a MINI to your Wishlist does not reserve a vehicle on our system.` | translate }}
                        </p>
                    </div>
                    <div class="row actions-row">
                        <template v-if="showContinueShoppingCta">
                            <a
                                v-if="redirectToContinueShopping"
                                :href="parsedContinueShoppingUrl"
                                class="button"
                                @click="closePopup"
                            >
                                {{ 'Continue Shopping' | translate }}
                            </a>
                            <button
                                v-if="!redirectToContinueShopping"
                                class="button"
                                @click="closePopup"
                            >
                                {{ 'Continue Shopping' | translate }}
                            </button>
                        </template>
                    </div>
                </div>

                <div class="get-quote-popup-failure" v-if="showPopupStep('failure')">
                    <div class="row">
                        <p>{{ errorMessage || 'An error occurred...' | translate }}</p>
                    </div>
                    <div class="row actions-row">
                        <a
                            v-if="showContinueShoppingCta"
                            :href="parsedContinueShoppingUrl"
                            @click="closePopup"
                            class="button"
                        >
                            {{ 'Continue Shopping' | translate }}
                        </a>
                    </div>
                </div>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import appGetQuoteCta from 'dsp2/components/Elements/GetQuoteCta';

    export default appGetQuoteCta.extend({});
</script>
