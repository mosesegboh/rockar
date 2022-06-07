<template>
    <div class="col-12"
         v-bind:class="{ 'navigation-buttons-wrapper': currentState != state.SUMMARY, ' add-you-drive-navigation-borders': disabledNextStepButton || currentStep === state.DATE}">
        <div class="navigation-outer-container">
            <button v-show="currentState != state.SUMMARY" class="button button-narrow step-back desktop-tablet-only"
                    v-if="stepBackEnabled"
                    @click="prevStep()"
            >{{ previousStepButtonText }}
            </button>

            <button class="button button-narrow next-step desktop-tablet-only"
                    :class="{ disabled: disabledNextStepButton }"
                    :disabled="disabledNextStepButton"
                    @click="nextStep()"
            >{{ nextStepButtonText }}
            </button>

            <!-- Mobile navigation buttons START -->
            <button v-show="currentState != state.SUMMARY" class="button button-narrow step-back mobile-only"
                    :class="{ 'add-you-drive-navigation-border-right' : disabledNextStepButton || currentStep === state.DATE}"
                    v-if="stepBackEnabled"
                    @click="prevStep()"
            ></button>

            <button class="button button-narrow next-step mobile-only"
                    :class="[ disabledNextStepButton ? 'disabled' : '', currentStep === 0 ? 'full-width' : 'with-back', !stepBackEnabled ? 'stepBackDisabled' : '']"
                    :disabled="disabledNextStepButton"
                    @click="nextStep()"
            >{{ nextStepButtonText }}
            </button>
            <!-- Mobile navigation buttons END -->
        </div>
    </div>
</template>

<script>
    import coreBottomNavigation from 'core/components/YouDrive/BottomNavigation';

    export default coreBottomNavigation.extend({
        computed: {
            state() {
                return this.youDrive.state;
            },

            stepBackEnabled() {
                // If its not a rebooking show on every but first step
                return (!this.isRebooking && this.currentStep > this.state.MODEL) ||
                    // OR if it is a rebooking show where appropriate
                    (this.isRebooking && this.currentStep >= this.state.DATE);
            }
        }
    });
</script>