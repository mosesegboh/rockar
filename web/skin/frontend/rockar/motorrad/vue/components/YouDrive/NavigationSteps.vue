<template>
    <div class="navigation-steps-block">
        <div class="navigation-steps-block-wrapper">
            <div id="navigation-steps" class="navigation-steps-wrapper">
                <div class="navigation-steps" :class="{'summary': requestedStep === 'summary'}">
                    <div class="navigation-step"
                         v-for="(key, step) in steps"
                         :class="{'current': step.activeSteps.indexOf(requestedStep) !== -1, 'checked': (currentStepData ? currentStepData.id > step.id : true)}"
                         @click="switchStep(step.id)">
                        <p class="number"></p>
                        <p class="description">{{ step.description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="navigation-steps-mobile-block-wrapper">
            <div class="col-12">
                <h3 class="content-card-header" v-if="currentStepData">
                    <span class="header-highlight">{{ `Step ${currentStepData.id + 1}` | translate }}</span>
                    {{ currentStepData.description }}
                </h3>
            </div>
        </div>
    </div>
</template>

<script>
import coreNavigationSteps from 'core/components/YouDrive/NavigationSteps';
import translateString from 'core/filters/Translate';

export default coreNavigationSteps.extend({
    data() {
        return {
            /**
             * Values are in reverse due flex-direction: row-reverse applied to the parent.
             * This is used to style previous elements via ~ (elements after) for styling previous steps
             */
            steps: {
                confirmation: {
                    id: 3,
                    activeSteps: [
                        'confirmation'
                    ],
                    description: translateString('Confirmation')
                },
                datetime: {
                    id: 2,
                    description: translateString('Date and Time'),
                    activeSteps: [
                        'datetime'
                    ]
                },
                vehicle: {
                    id: 1,
                    description: translateString('Location'),
                    activeSteps: [
                        'vehicle'
                    ]
                },
                model: {
                    id: 0,
                    description: translateString('Select Motorcycle'),
                    activeSteps: [
                        'model'
                    ]
                }
            },
            currentStep: 0
        }
    }
});
</script>
