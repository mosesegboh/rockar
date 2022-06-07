<template>
    <div :class="pxClass" class="px-bottom">
        <template v-if="isExpired && valuationResult">
            <button
                class="button dsp2-money"
                :class="{ disabled: !partExchangeIsValid }"
                v-show="partExchangeSecondStep"
                @click="saveValuation()"
                :disabled="!partExchangeIsValid"
            >
                {{ 'Save' | translate }}
            </button>
        </template>
        <template v-else>
            <button
                class="button dsp2-money"
                :class="{ disabled: !partExchangeIsValid }"
                v-show="partExchangeSecondStep"
                @click="nextStep(false)"
                :disabled="!partExchangeIsValid"
            >
                {{ isExpired ? 'Update Valuation' : 'Get Valuation' | translate }}
            </button>
        </template>
        <p class="px-required">{{ '* Indicates a required field.' | translate }}</p>
        <slot></slot>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            isExpired: {
                required: false,
                type: Boolean,
                default: false
            },

            valuationResult: {
                required: true,
                type: Boolean
            },

            partExchangeIsValid: {
                required: true,
                type: Boolean
            },

            partExchangeSecondStep: {
                required: true,
                type: Boolean
            },

            pxClass: {
                required: false,
                type: String,
                default: ''
            }
        },

        methods: {
            saveValuation() {
                this.$emit('save');
            },

            nextStep(val) {
                this.$emit('next', val);
            }
        },

        ready() {
            if (this.partExchangeIsValid && this.isExpired && this.$parent.pxAccordion) {
                this.partExchangeSecondStep = true;
                this.$parent.valuationResult = false;
            }
        }
    })
</script>
