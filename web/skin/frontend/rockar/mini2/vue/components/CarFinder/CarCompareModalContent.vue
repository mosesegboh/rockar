<template>
    <div>
        <div class="compare-content" v-if="!limitReached">
            <p v-if="errorMessage">{{ errorMessage }}</p>

            <p v-if="!errorMessage && serverError">
                {{ 'An error occurred. Please try again later.' | translate }}
            </p>
        </div>

        <div class="compare-content" v-if="limitReached">
            <p class="h1">{{ 'Compare limit reached.' | translate }}</p>
            <p>
                {{ 'We know you love them all, but only a maximum of three can be compared at a time.' | translate }}
                <br>
                {{ 'Please remove a vehicle from your Compare List to add this MINI.' | translate }}
            </p>
            <button type="button" name="button" class="button dsp2-money" @click="closeCompare()">
                {{ 'View Compare List' | translate }}
            </button>
        </div>
    </div>
</template>
<script>
    import appCarCompareModalContent from 'dsp2/components/CarFinder/CarCompareModalContent';

    export default appCarCompareModalContent.extend({})
</script>
