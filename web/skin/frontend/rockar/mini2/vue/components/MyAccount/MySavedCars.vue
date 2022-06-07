<template>
    <div
        class="my-saved-cars"
        :class="{ 'oos': carStock <= 0 }"
    >
        <app-my-saved-cars-compare
            :is-in-compare-list="isInCompareList"
            :compare-remove-url="compareRemoveUrl"
            :compare-add-url="compareAddUrl"
            :base-url="baseUrl"
            :compare="compare"
            :render-in-body="true"
            v-ref:compare-list-wishlist
        ></app-my-saved-cars-compare>
        <div class="my-saved-cars-wrapper">
            <div class="saved-cars-info">
                <p class="h-common user-title">{{ carTitle }}</p>
                <p class="block-title h-common h-small">{{ carDescription }}</p>
            </div>
            <img :src="carCoverExterior" :alt="carTitle">
            <p class="price-date" :class="{ 'out-of-stock': carStock <= 0 }">
                <span v-if="carStock >= 1">{{ 'Date of price' | translate }}</span>
                <span v-if="carStock >= 1">{{ carAddedDateFormatted }}</span>
                <span v-else>{{ 'Out of stock' | translate }}</span>
            </p>
            <div class="product-prices">
                <div class="item-details-price-block">
                    <div class="item-details-price rockar-price" v-if="!isHire">
                        <div class="price-wrapper">
                            <span class="label">{{ 'Offer Price' | translate }}</span>
                            <span class="price">{{ getOfferPrice | numberFormat '0,0' true }}</span>
                        </div>
                    </div>
                    <div class="item-details-price monthly-price" v-if="financeData.monthlyPrice > 0">
                        <div class="price-wrapper">
                            <span class="label">{{ 'Per Month' | translate }}</span>
                            <p class="price">
                                <span class="price-value">{{ Math.round(financeData.monthlyPrice) | numberFormat '0,0' true }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="product-actions">
                <button class="product-action action-buy" @click="openCar">
                    {{ openButtonTitle }}
                </button>
                <button
                    v-if="carStock >= 1"
                    class="product-action button dsp2-action"
                    :class="[ isInCompareList ? 'remove' : 'add' ]"
                    @click="ajaxCompareAdd()"
                >
                    {{ isInCompareList ? 'Remove' : 'Compare' | translate }}
                </button>
                <button
                    v-else
                    class="product-action button dsp2-action"
                    @click="openCarConfigurator()"
                >
                    {{ buildMyVehicleText | translate }}
                </button>
            </div>

            <button class="save-overlay" @click="openRemoveDialog"></button>

            <app-save-car
                v-if="showSaveOverlay"
                :open="showSaveOverlay"
                :customer-name="userTitle"
                :product-name="carTitle"
                :product-title="carTitle"
                :product-subtitle="carDescription"
                :is-in-wishlist="true"
                :is-ajax-request="isAjaxRequest"
                :image="carCoverExterior"
                :remove-from-wishlist-url="carRemoveUrl"
                :render-in-body="true"
                @toggle-is-in-wishlist="removeSuccess"
                @close-save="showSaveOverlay = false"
                v-ref:save-modal>
            </app-save-car>

            <app-modal :show.sync="financeOverlay" v-if="financeOverlay" width="80%">
                <div slot="content">
                    <app-finance-overlay
                        :product-id='financeData.productId'
                        :finance-url='financeData.financeUrl'
                        :finance-params-origin='financeData.financeParams'
                        :finance-slider-steps='financeData.financeSliderSteps'
                        :pay-in-full-payment='financeData.payInFullPayment'
                        :hire-payments='financeData.hirePayments'
                        :active-payment='financeData.activePayment'
                        :payment-save-url='financeData.paymentSaveUrl'
                        :product-url='financeData.carRedirectUrl'
                        :update-finance-quote='true'
                        :calc-type='financeData.calcType'
                        :use-short-version='true'
                    ></app-finance-overlay>
                </div>
            </app-modal>
        </div>
    </div>
</template>
<script>
    import coreMySavedCars from 'dsp2/components/MyAccount/MySavedCars';
    import appMySavedCarsCompare from 'mini2/components/MyAccount/MySavedCarsCompare';
    import appSaveCar from 'mini2/components/SaveCar';

    export default coreMySavedCars.extend({
        computed: {
            buildMyVehicleText() {
                return 'Build my MINI';
            }
        },

        components: {
            appSaveCar,
            appMySavedCarsCompare
        }
    });
</script>
