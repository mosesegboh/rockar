<template>
    <div>
        <div :class="['navigation-steps-block', isPxSubStepOneOrTwo ? 'navigation-px-step' : '']" v-if="requestedStep === 'finance' || requestedStep === 'partExchange' || requestedStep === 'modelFilter'">
            <div class="navigation-steps-block-wrapper">
                <div id="navigation-steps" class="navigation-steps-wrapper">
                    <div class="navigation-steps">
                        <div class="navigation-step"
                             v-for="(key, step) in steps"
                             :class="{ current: step.activeSteps.indexOf(requestedStep) !== -1 }"
                             @click="switchStep(step.id)">
                            <p class="number"></p>
                            <p class="description">{{ step.description }}</p>
                        </div>
                    </div>
                </div>
                <a href="#" class="navigation-learn-more" @click="showLearnMoreOverlay()">
                    <span>{{ 'Learn More' | translate }}</span>
                </a>
            </div>

            <div v-if="isNavigationVisible">
                <div v-for="(key, step) in steps">
                    <div class="row header-row"
                         v-if="step.activeSteps.indexOf(requestedStep) !== -1">
                        <div class="col-12">
                            <div class="content-card-header" v-if="step.id !== 2">
                                <div class="header-title">
                                    <span class="header-highlight">{{ 'Step' | translate }} {{ step.id + 1 }}</span> {{ step.description }}
                                </div>
                                <div class="header-description">{{ step.subDescription }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <app-modal :show.sync="showPopup">
                <div slot="content" class="learn-more-popup" v-html="popupContent"></div>
            </app-modal>
        </div>
    </div>
</template>

<script>
    import translateString from 'core/filters/Translate';
    import appModal from 'core/components/Elements/Modal';

    export default Vue.extend({
        data() {
            return {
                /**
                 * Values are in reverse due flex-direction: row-reverse applied to the parent.
                 * This is used to style previous elements via ~ (elements after) for styling previous steps
                 */
                steps: {
                    checkout: {
                        id: 3,
                        activeSteps: [
                            'checkout'
                        ],
                        description: translateString('CHECKOUT'),
                    },
                    productResults: {
                        id: 2,
                        description: translateString('CHOOSE YOUR MINI'),
                        subDescription: translateString('Select one or more models to begin searching.'),
                        activeSteps: [
                            'modelFilter'
                        ],
                    },
                    partExchange: {
                        id: 1,
                        description: translateString('TRADE IN'),
                        activeSteps: [
                            'partExchange'
                        ],
                    },
                    finance: {
                        id: 0,
                        description: translateString('SET YOUR BUDGET?'),
                        activeSteps: [
                            'finance'
                        ],
                    },
                },
                currentStep: 0,
            }
        },

        props: {
            requestedStep: {
                type: String,
                required: true
            },
            showPopup: {
                required: false,
                type: Boolean
            },
            popupContent: {
                type: String,
                required: false,
                default: ''
            },
            navigationContentUrl: {
                type: String,
                required: true,
            },
            navigationSubstep: {
                type: Number,
                required: false,
            }
        },

        computed: {
            currentStepCode() {
                return this.requestedStep;
            },

            isNavigationVisible() {
                return !(this.requestedStep === 'partExchange' && this.navigationSubstep > 0)
            },

            isPxSubStepOneOrTwo() {
                if ((this.navigationSubstep === 1 || this.navigationSubstep === 2) && this.requestedStep === 'partExchange') {
                    return true;
                }
            }
        },

        methods: {
            switchStep(id) {
                this.$parent.goToStep(id);
            },

            showLearnMoreOverlay() {
                this.getLearnMoreContent();
                this.showPopup = true;
            },

            getLearnMoreContent() {
                this.$http({
                    method: 'POST',
                    url: this.navigationContentUrl,
                    emulateJSON: true,
                    data: {
                        'currentStep': this.$parent.currentStep,
                    }
                }).then(this.getLearnMoreContentSuccess, this.getLearnMoreContentFail);
            },

            getLearnMoreContentSuccess(response) {
                this.popupContent = response.data.success ? response.data.message : translateString('There was an error while loading "Learn More" popup content');
            },

            getLearnMoreContentFail(response) {
                this.popupContent = response.data.message;
            }
        },

        watch: {
            currentStepCode(step) {
                EventsBus.$emit('CarFinder::UpdateUrl');
            },
        },

        components: {
            appModal
        }
    });
</script>
