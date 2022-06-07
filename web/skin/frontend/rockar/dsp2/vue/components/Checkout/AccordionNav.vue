<template>
    <div id="accordion-nav">
        <div class="accordion-nav-body">
            <div v-for="(index, code) in stepCodes" :key="index" class="accordion-step">
                <div class="accordion-step-top">
                    <div class="accordion-step-connector accordion-step-connector-left" :class="[{ 'active': activeStepIndex === index }, { 'checked': activeStepIndex > index }]" v-if="index !== 0" ></div>
                    <div class="accordion-step-number"
                        :class="[{ 'active': activeStepIndex === index}, { 'checked': activeStepIndex > index }]"
                        @click="changeStep(code, index)"
                    >
                        <div><span>{{ index + 1 }}</span></div>
                    </div>
                    <div class="accordion-step-connector accordion-step-connector-right" :class="[{ 'active': activeStepIndex === index }, { 'checked': activeStepIndex > index }]" v-if="index < stepCodes.length - 1" ></div>
                </div>
                <div class="accordion-step-title" :class="{ 'active': activeStepIndex === index }" @click="changeStep(code, index)" v-html="stepTitles[index] | translate"></div>
            </div>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            stepCodes: {
                required: false,
                type: Array,
                default: []
            }
        },

        data() {
            return {
                stepTitles: [
                    'Your personal<br/> details',
                    'Collection<br/>/ Delivery',
                    'Trade-in<br/>(optional)',
                    'Choose your<br/>payment option',
                    'Apply for finance<br/>(optional)',
                    'Summary'
                ]
            };
        },

        methods: {
            changeStep(step, index) {
                if (index < this.activeStepIndex) {
                    this.$root.$refs.accordionGroup.showStep(step);
                }
            }
        },

        computed: {
            activeStepIndex() {
                return this.$store.state.checkout.activeStepIndex;
            }
        }
    });
</script>
