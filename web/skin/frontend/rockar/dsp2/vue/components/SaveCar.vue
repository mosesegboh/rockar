<template>
    <div v-if="!renderInBody || openSave" class="save-car">
        <app-modal :show="openSave" class-name="simple-popup" @close-popup="closeSave" v-ref:modal>
            <div slot="content" class="my-saved-cars-save">
                <div class="preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
                <div class="my-saved-cars-content">
                    <div class="desktop">
                        <div class="left-section">
                            <div :style="{ backgroundImage: `url('${image}')` }"></div>
                        </div>
                        <div class="right-section">
                            <div class="information-wrapper">
                                <h3 class="product-title">{{ titleText }}</h3>
                                <p class="dsp2-body" v-if="!isUserLoggedIn">
                                    {{ logInText }}
                                </p>
                                <p class="dsp2-body" v-if="showAddedToWishlist && !errorMessage">
                                    {{ addedText }}
                                </p>
                                <p class="dsp2-body" v-if="showRemoveFromWishlist">
                                    {{ removeText }}
                                </p>
                                <p class="dsp2-body error" v-if="errorMessage">
                                    {{ errorMessage }}
                                </p>
                            </div>
                            <div class="my-saved-cars-save-action grid row">
                                <div class="col-md-12 col-6">
                                    <button v-if="showAddedToWishlist"
                                        type="button"
                                        @click="closeSave"
                                        class="dsp2-outline"
                                    >
                                        <span>{{ 'Close' | translate }}</span>
                                    </button>
                                    <button v-else
                                        type="button"
                                        @click="closeSave"
                                        class="dsp2-outline"
                                    >
                                        <span>{{ 'Cancel' | translate }}</span>
                                    </button>
                                </div>
                                <div class="col-md-12 col-6" >
                                    <button v-if="!isUserLoggedIn"
                                        type="button"
                                        @click="login"
                                        class="dsp2-money"
                                    >
                                        <span>{{ 'Login/Register' | translate }}</span>
                                    </button>
                                    <button v-if="showAddedToWishlist"
                                        type="button"
                                        @click="openCar"
                                        class="dsp2-money"
                                    >
                                        <span>{{ viewWishlistButtonText | translate }}</span>
                                    </button>
                                    <button v-if="showRemoveFromWishlist"
                                        type="button"
                                        @click="ajaxRemoveCar"
                                        class="dsp2-money"
                                    >
                                        <span>{{ removeConfirmationButtonText | translate }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mobile">
                        <h4 class="product-title">{{ this.myAccount ? this.productSubtitle : this.productName }}</h4>
                        <p class="dsp2-caption product-subtitle">{{ this.myAccount ? this.productName : this.productSubtitle }}</p>
                        <div :style="{ backgroundImage: `url('${image}')` }"></div>
                        <p class="dsp2-body" v-if="!isUserLoggedIn">
                            {{ logInText }}
                        </p>
                        <p class="dsp2-body" v-if="showAddedToWishlist && !errorMessage">
                            {{ addedText }}
                        </p>
                        <p class="dsp2-body" v-if="showRemoveFromWishlist">
                            {{ removeText }}
                        </p>
                        <p class="dsp2-body error" v-if="errorMessage">
                            {{ errorMessage }}
                        </p>
                        <div class="my-saved-cars-save-action">
                            <button v-if="showAddedToWishlist"
                                type="button"
                                @click="closeSave"
                                class="dsp2-outline"
                            >
                                <span>{{ 'Close' | translate }}</span>
                            </button>
                            <button v-else
                                type="button"
                                @click="closeSave"
                                class="dsp2-outline"
                            >
                                <span>{{ 'Cancel' | translate }}</span>
                            </button>
                            <button v-if="!isUserLoggedIn"
                                type="button"
                                @click="login"
                                class="dsp2-money"
                            >
                                <span>{{ 'Login/Register' | translate }}</span>
                            </button>
                            <button v-if="showAddedToWishlist"
                                type="button"
                                @click="openCar"
                                class="dsp2-money"
                            >
                                <span>{{ viewWishlistButtonText }}</span>
                            </button>
                            <button v-if="showRemoveFromWishlist"
                                type="button"
                                @click="ajaxRemoveCar"
                                class="dsp2-money"
                            >
                                <span>{{ removeConfirmationButtonText }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import appModal from 'core/components/Elements/Modal';
    import coreSaveCar from 'core/components/SaveCar';
    import SaveCar from 'dsp2/components/Shared/SaveCar';
    import EventTracker from 'dsp2/mixins/EventTracker';
    import translateString from 'core/filters/Translate';

    export default coreSaveCar.extend({
        mixins: [SaveCar, EventTracker],

        components: {
            appModal
        },

        props: {
            open: {
                required: true,
                type: Boolean
            },

            removeFromWishlistUrl: {
                required: true,
                type: String
            },

            carId: {
                required: false,
                type: Number,
                default: -1
            },

            saveWishlistUrl: {
                required: false,
                type: String,
                default: ''
            },

            myAccountUrl: {
                required: false,
                type: String,
                default: ''
            },

            renderInBody: {
                required: false,
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                showAddedToWishlist: false,
                showRemoveFromWishlist: false
            }
        },

        computed: {
            openSave() {
                return this.open;
            },

            myAccount() {
                return this.$root.$refs.myDetails;
            },

            logInText() {
                return this.translateString('Log in/Register to add this vehicle to your wishlist.');
            },

            addedText() {
                return this.translateString('Vehicle added to your wishlist.');
            },

            removeText() {
                return this.translateString('Are you sure you want to remove this vehicle from your wishlist?');
            },

            removeConfirmationButtonText() {
                return this.translateString('Yes, remove');
            },

            viewWishlistButtonText() {
                return this.translateString('View Wishlist');
            },

            titleText() {
                return `${this.productName} ${this.productSubtitle}`
            }
        },

        methods: {
            translateString,

            closeSave() {
                this.showAddedToWishlist = false;
                this.showRemoveFromWishlist = false;

                if (this.renderInBody) {
                    this.$refs.modal.$remove();
                }

                this.$dispatch('close-save');
            },

            ajaxSaveCar() {
                this.error = false;
                this.ajaxLoading = true;
                this.errorMessage = '';

                this.$http({
                    url: this.saveWishlistUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        name: this.customName.trim()
                    }
                }).then(this.ajaxSaveCarSuccess, this.ajaxSaveCarFail);
            },

            ajaxSaveCarSuccess(data) {
                data = data.data;
                this.ajaxLoading = false;
                this.isInWishlist = true;
                this.showAddedToWishlist = true;
                this.$emit('toggle-is-in-wishlist', true);
                this.$emit('open-save-car-overlay');
                this.$dispatch('Main::updateWishlistProp', { productId: this.carId, isInWishlist: this.isInWishlist });
                this.fireEventForTracking(
                    this.getEventConstants().PAGEDESCRIPTION.TRIGGERS,
                    `${this.getEventConstants().TRIGGERTRACKERVALUES.WISHLIST}${data.added_vehicles}`
                );
            },

            ajaxSaveCarFail(error) {
                this.ajaxLoading = false;
                this.serverError = true;
                this.errorMessage = error.data.errorMessage;
                console.error('Wishlist Save:', error);
                this.$emit('open-save-car-overlay');
            },

            ajaxRemoveCar() {
                this.error = false;
                this.ajaxLoading = true;
                this.errorMessage = '';

                this.$http({
                    url: this.removeFromWishlistUrl,
                    method: 'POST',
                    emulateJSON: true,
                }).then(this.ajaxRemoveCarSuccess, this.ajaxRemoveCarFail);
            },

            ajaxRemoveCarSuccess(data) {
                data = data.data;
                this.ajaxLoading = false;
                this.isInWishlist = false;
                this.showAddedToWishlist = true;
                this.$emit('toggle-is-in-wishlist', false);
                this.$dispatch('Main::updateWishlistProp', { productId: this.carId, isInWishlist: this.isInWishlist });
                this.closeSave();
            },

            ajaxRemoveCarFail(error) {
                this.ajaxLoading = false;
                this.serverError = true;
                this.errorMessage = error.data.errorMessage;
                console.error('Wishlist Remove:', error);
            }
        },

        ready() {
            if (this.renderInBody) {
                this.$nextTick(() => {
                    this.$refs.modal.$appendTo(document.body);
                });
            }
        },

        events: {
            'SaveCar::addToWishlist'() {
                this.ajaxSaveCar();
            },

            'SaveCar::removeFromWishlist'() {
                this.showRemoveFromWishlist = true;
            }
        }
    });
</script>
