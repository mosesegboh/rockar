<template>
    <div>
        <div class="compare-content" v-if="!limitReached">
            <p v-if="errorMessage">{{ errorMessage }}</p>

            <p v-if="!errorMessage && serverError">
                {{ 'An error occurred. Please try again later.' | translate }}
            </p>
        </div>

        <div class="compare-content" v-if="limitReached">
            <p class="h1">
                {{ 'Compare limit reached' | translate }}
            </p>
            <p>
                {{ 'You can only compare a maximum of three vehicles at a time.' | translate }}
                {{ 'Please remove a vehicle from your Compare List to add this vehicle.' | translate }}
            </p>

            <button type="button" name="button" class="button dsp2-money" @click="closeCompare()">
                {{ 'View Compare List' | translate }}
            </button>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            limitReached: {
                required: true,
                type: Boolean
            },

            errorMessage: {
                required: true,
                type: [Boolean, String]
            },

            serverError: {
                required: true,
                type: [Boolean, String]
            }
        },

        methods: {
            closeCompare() {
                this.$emit('close');
            }
        }
    })
</script>
