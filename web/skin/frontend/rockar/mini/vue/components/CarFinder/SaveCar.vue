<template>
    <div class="save-car">
        <app-modal :show="openSave" class-name="simple-popup" @close-popup="closeSave">
            <div slot="content" class="my-saved-cars-save">
                <div class="preloader" v-show="ajaxLoading">
                    <div class="show-loading"></div>
                </div>
                <form :action="saveWishlistUrl" @submit="saveCar">
                    <div class="my-saved-cars-content" v-if="!resultReceived">
                        <p class="h1">
                            {{ 'Ready to add this MINI to your wishlist?' | translate }}
                        </p>
                        <p class="my-saved-cars-name">
                            {{ customerName }}, {{ 'the' | translate }} {{ productName }} 
                            {{ 'will be added to your wishlist located in your account.' | translate }}
                        </p>
                        <p v-if="serverError">
                            {{ errorMessage }}
                        </p>
                        <p v-if="duplicateError">
                            {{ 'Sorry, but name of your car should be unique.' | translate }}
                        </p>
                        <div class="validation-advice" v-if="!validationResult">
                            {{ 'Please use only allowed characters for car name' | translate }}
                        </div>
                        <div class="my-saved-cars-save-action">
                            <button type="submit"
                                    :class="[!validationResult ? 'button-disabled' : 'button-blue-lagoon']"
                                    :disabled="!validationResult">
                                <span><span>{{ 'ADD TO WISHLIST' | translate }}</span></span>
                            </button>
                        </div>
                    </div>

                    <div class="my-saved-cars-content" v-if="resultReceived">
                        <p class="h1">
                            {{ 'MINI successfully added to your wishlist.' | translate }}
                        </p>
                        <p class="my-saved-cars-content-name">
                            {{ "You can view your" | translate }} {{ productName }} {{ "in" | translate }}
                            <a :href="myAccountUrlFull" class="my-saved-cars-content-link">{{ 'your account.' | translate }}</a>
                        </p>
                        <p>
                            {{ 'Please note: Adding a vehicle to your wishlist does not reserve it on our system.' | translate }}
                        </p>
                        <div class="my-saved-cars-save-action">
                            <button type="button"
                                @click="closeSave"
                                class="button-blue-lagoon">
                                <span><span>{{ 'CONTINUE SHOPPING' | translate }}</span></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import appModal from 'core/components/Elements/Modal';
    import SaveCar from 'mini/components/Shared/SaveCar';

    export default Vue.extend({
        mixins: [SaveCar],
        props: {
            open: {
                required: true,
                type: Boolean
            }
        },

        computed: {
            openSave() {
                return this.open;
            }
        },

        methods: {
            closeSave() {
                this.$dispatch('close-save');
            },

            ajaxSaveCar() {
                this.error = false;
                this.ajaxLoading = true;

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
                this.resultReceived = true;
                this.isInWishlist = true;
                this.$dispatch('ProductPod::isInWishlist', true);
            },

            ajaxSaveCarFail(error) {
                this.ajaxLoading = false;

                if (typeof error.data.duplicate !== 'undefined') {
                    this.duplicateError = true;
                } else {
                    this.serverError = true;
                    this.errorMessage = error.data.errorMessage;
                    console.error('Wishlist Save:', error);
                }
            },

            openCar() {
                window.location.href = this.myAccountUrlFull;
            }
        },

        components: {
            appModal
        }
    });
</script>
