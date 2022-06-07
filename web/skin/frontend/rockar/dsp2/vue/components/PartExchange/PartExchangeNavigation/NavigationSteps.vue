<template>
    <div class="trade-in-navigation">
        <div v-if="showPrevious" class="navigation-link-mobile">
            <a class="arrow-back" @click="switchStep(currentStep, navigationSubstep)"></a>
        </div>
            <div :class="['navigation-steps-block', isPxSubStep ? 'navigation-px-step' : '']" v-if="requestedStep === 'vehicleDetails' || requestedStep === 'vehicleCondition' || requestedStep === 'valuation'">
                <div class="navigation-steps-block-wrapper">
                    <div id="navigation-steps" class="navigation-steps-wrapper">
                        <div class="navigation-steps">
                            <div class="navigation-step"
                                 v-for="(key, step) in steps"
                                 :class="{ current: step.activeSteps.indexOf(requestedStep) !== -1 }">
                                <p class="number">{{ step.id + 1 }}</p>
                                <p class="description">{{ step.description }}</p>
                            </div>
                        </div>
                        <div v-if="showPrevious" class="navigation-link-desktop">
                            <a class="dsp2-link" @click="switchStep(currentStep, navigationSubstep)">
                                <span class="arrow-back"></span>{{ 'Previous' | translate }}
                            </a>
                        </div>
                    </div>
                </div>
          </div>
        <input type="hidden" :value="currentStep">
    </div>
</template>

<script>
    import translateString from 'core/filters/Translate';

    export default Vue.extend({
        data() {
            return {
                /**
                 * Values are in reverse due flex-direction: row-reverse applied to the parent.
                 * This is used to style previous elements via ~ (elements after) for styling previous steps
                 */
                steps: {
                    valuation: {
                        id: 2,
                        description: translateString('Valuation'),
                        activeSteps: [
                            'valuation'
                        ],
                    },
                    vehicleCondition: {
                        id: 1,
                        description: translateString('Vehicle condition'),
                        activeSteps: [
                            'vehicleCondition'
                        ],
                    },
                    vehicleDetails: {
                        id: 0,
                        description: translateString('Vehicle details'),
                        activeSteps: [
                            'vehicleDetails'
                        ],
                    },
                }
            }
        },

        props: {
            requestedStep: {
                required: true,
                type: String
            },
            navigationSubstep: {
                required: false,
                type: Number,
                default: 0
            },
            currentStep: {
                required: false,
                type: Number,
                default: 0
            },
            hidePrevious: {
                required: false,
                type: Boolean,
                default: false
            }
        },

        computed: {
            currentStepCode() {
                return this.requestedStep;
            },

            isPxSubStep() {
                if ((this.navigationSubstep === 0 || this.navigationSubstep === 1 || this.navigationSubstep === 2)
                    && this.requestedStep === 'vehicleDetails') {
                    return true;
                }
            },

            showPrevious() {
                if ((this.navigationSubstep > 0 || this.currentStep > 0) && !this.hidePrevious) {
                    return true;
                }
            }
        },

        methods: {
            switchStep(current, sub) {
                this.$parent.goToStep(current, sub);
            }
        }
    });
</script>
