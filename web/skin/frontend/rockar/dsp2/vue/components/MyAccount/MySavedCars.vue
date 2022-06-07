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

            <app-modal :show.sync="financeOverlay" v-if="financeOverlay" width="80%" >
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
    import coreMySavedCars from 'core/components/MyAccount/MySavedCars';
    import appFinanceOverlay from 'dsp2/components/FinanceOverlay';
    import appExtras from 'dsp2/components/MyAccount/MySavedCarsExtras';
    import translateString from 'core/filters/Translate';
    import appMySavedCarsCompare from 'dsp2/components/MyAccount/MySavedCarsCompare';
    import appConfirmationModal from 'dsp2/components/Elements/ConfirmationModal';
    import appSaveCar from 'dsp2/components/SaveCar';

    export default coreMySavedCars.extend({
        props: {
            wishlistItemPrice: {
                required: true,
                type: Number
            },

            financeOptions: {
                type: Object,
                require: false,
                default: null
            },

            compare: {
                required: true,
                type: Object
            },

            isInCompareList: {
                required: true,
                type: Boolean
            },

            compareAddUrl: {
                required: true,
                type: String
            },

            compareRemoveUrl: {
                required: true,
                type: String
            },

            baseUrl: {
                required: true,
                type: String
            },

            configuratorUrl: {
                required: false,
                type: String,
                default: ''
            }
        },

        computed: {
            /**
             * Temporary fix to show saved wishlist item price if product is out of stock
             */
            getOfferPrice() {
                return this.carStock >= 1 ? this.financeData.rockarPrice : this.wishlistItemPrice;
            },

            priceNote() {
                return `*${this.translateString('Based on')} ${this.activePaymentName}`;
            },

            activePaymentName() {
                const activeId = this.financeData.activePayment.group_id;
                const { group_title: groupTitle = '' } = this.financeOptions.items.find(group => parseInt(group.group_id) === activeId) || {};

                return groupTitle;
            },

            openButtonTitle() {
                return this.carStock >= 1 ? this.translateString('View Vehicle') : this.translateString('Find similar stock')
            },

            buildMyVehicleText() {
                return 'Build my BMW';
            }
        },

        data() {
            return {
                showSaveOverlay: false,
                beingRemoved: false
            }
        },

        methods: {
            ajaxCompareAdd() {
                this.$refs.compareListWishlist.ajaxCompareAdd();
            },

            removeSuccess() {
                this.$nextTick(() => {
                    const nextCount = this.$parent.$children.length - 1;

                    if (nextCount === 0) {
                        this.$parent.$parent.$destroy(true);
                    } else {
                        this.$parent.$parent.title = `${this.translateString('My Wishlist')} (${nextCount})`;
                        this.$nextTick(() => {
                            const currentIndex = jQuery(this.$el).index();

                            this.beingRemoved = true;

                            this.$dispatch('Carousel::removeFromSlider', currentIndex);
                        });
                    }
                });
            },

            openCar() {
                if (this.carStock >= 1) {
                    this.goTo(this.carRedirectUrl)
                } else {
                    this.goTo(this.carFinderUrl)
                }
            },

            openRemoveDialog() {
                this.showSaveOverlay = true;

                this.$nextTick(() => {
                    this.$broadcast('SaveCar::removeFromWishlist');
                });
            },

            closeRemoveDialog() {
                this.showSaveOverlay = false;
            },

            openCarConfigurator() {
                window.open(this.configuratorUrl, '_blank');
            },

            translateString
        },

        events: {
            'Carousel::itemRemoved'() {
                if (this.beingRemoved) {
                    this.$destroy(true);
                }
            }
        },

        components: {
            appFinanceOverlay,
            appExtras,
            appMySavedCarsCompare,
            appConfirmationModal,
            appSaveCar
        }
    });
</script>
