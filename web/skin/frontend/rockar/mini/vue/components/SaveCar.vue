<template>
    <button
        :class="buttonClasses"
        type="button"
        name="button"
        @click.stop="openPopup"
        v-if="!isInWishlist && !showAsLink"
    >
        {{ 'Wishlist' | translate }}
    </button>
    <button
        :class="buttonClasses"
        class="added-to-wishlist"
        type="button"
        name="button"
        v-if="isInWishlist && !showAsLink"
        @click.stop="ajaxSaveCar"
    >
        {{ 'Wishlist' | translate }}
    </button>

    <a
        v-if="!isInWishlist && showAsLink"
        @click.stop="openPopup"
        class="button button-empty-dark"
        href="javascript:void(0);"
        title="{{ 'Save Vehicle' | translate }}"
    >
        {{ 'Save Vehicle' | translate }}
    </a>
    <a
        v-if="isInWishlist && showAsLink"
        href="javascript:void(0);"
        class="button button-empty-dark"
        title="{{ 'View Vehicle' | translate }}"
        @click.stop="openCar"
    >
        {{ 'View Vehicle' | translate }}
    </a>

    <app-modal :show.sync="openSave" class-name="simple-popup">
        <div slot="content" class="my-saved-cars-save">
            <div class="preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
            <form :action="saveWishlistUrl">
                <div class="my-saved-cars-content" v-if="!resultReceived">
                    <p class="h1">{{ 'Ready to add this MINI to your wishlist?' | translate }}</p>
                    <p class="my-saved-cars-name">
                        {{ customerName }}, {{ 'the' | translate }} {{ productName }} 
                        {{ 'will be added to your wishlist located in your account.' | translate }}
                    </p>

                    <p v-if="serverError">{{ errorMessage }}</p>
                    <p v-if="duplicateError">{{ 'Sorry, but name of your vehicle should be unique.' | translate }}</p>
                    <div class="validation-advice" v-if="!validationResult">
                        {{ 'Please use only allowed characters for vehicle name' | translate }}
                    </div>

                    <div class="my-saved-cars-save-action">
                        <button type="submit"
                                @click="saveCar"
                                :class="[!validationResult ? 'button-disabled' : 'button-blue-lagoon']"
                                :disabled="!validationResult"
                                class="button-blue-lagoon">
                            <span>{{ 'ADD TO WISHLIST' | translate }}</span>
                        </button>
                    </div>
                </div>

                <div class="my-saved-cars-content" v-if="resultReceived">
                    <p class="h1">{{ 'MINI successfully added to your wishlist.' | translate }}</p>
                    <p class="my-saved-cars-content-name">
                        {{ "You can view your" | translate }} {{ productName }} {{ "in" | translate }}
                        <a :href="myAccountUrlFull" class="my-saved-cars-content-link">{{ 'your account.' | translate }}</a>
                    </p>
                    <p>{{ 'Please note: Adding a vehicle to your wishlist does not reserve it on our system.' | translate }}</p>
                    <div class="my-saved-cars-save-action">
                        <button type="button"
                            @click="stepBack"
                            class="button-blue-lagoon"
                        >
                            <span>{{ 'CONTINUE SHOPPING' | translate }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </app-modal>
</template>

<script>
    import coreSaveCar from 'core/components/SaveCar';
    import SaveCar from 'mini/components/Shared/SaveCar';

    export default coreSaveCar.extend({
        mixins: [SaveCar],

        methods: {
            openPopup() {
                if (this.customerName.toLowerCase() === 'guest') {
                    this.openSave = false;
                    window.location.href = this.myAccountUrl;
                } else {
                    this.openSave = true;
                }
            },

            /**
             * Closes the second wishlist popup and steps back to result page
             */
            stepBack() {
                this.closePopup();
                EventsBus.$emit('configurator::stepBack', null);
            },

            /**
             * Closes The wishlist popup
             */
            closePopup() {
                this.openSave = false;
            },

            ajaxSaveCarSuccess() {
                this.openCar();
            }
        }
    });
</script>
