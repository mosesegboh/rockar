<template>
    <div class="carousel-wrapper">
        <div v-for="(index, slide) in slidesData" :key="slide.id">
            <div class="carousel-block" :class="{ 'disabled' : slide.count === 0 }">
                <div class="carousel-out-of-stock">
                    <p>{{ "I’m out of stock or not within budget" | translate }}</p>
                </div>
                <div class="carousel-body-main">
                    <div class="carousel-image-wrapper">
                        <div class="carousel-body-main-overlay" v-if="slide.count === 0"></div>
                        <label :for="`cs${$index + slide.id}`">
                            <img :class="{ 'active' : slides[index].state }" :src="slide.image" :alt="slide.title">
                        </label>
                    </div>
                    <div class="checkbox-values">
                        <div class="carousel-checkbox-wrapper">
                            <input
                                v-model="slides[index].state"
                                type="checkbox"
                                :id="`cs${$index + slide.id}`"
                                @change="checkMatrixExists(slide)"
                                :disabled="slide.count === 0"
                            >
                            <label :for="`cs${$index + slide.id}`">{{ slide.title }}</label>
                            <div class="checkbox-info" v-if="slide.count !== 0">
                                <p>{{ slide.count }} {{ "Results" | translate }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <a class="notify-me" v-if="slide.count === 0 && notifyMeEnabled" href="javascript:void(0);" @click="redirectToNotifyMe()">
                    <span>{{ 'Notify me' | translate }}</span>
                </a>
                <div class="carousel-item-selected" v-if="slides[index].state"></div>
            </div>
        </div>
    </div>
</template>

<script>
import appCarouselModelMatrix from 'dsp2/components/CarFinder/CarouselModelMatrix';

export default appCarouselModelMatrix.extend({
    props: {
        allModels: {
            required: false,
            type: Boolean,
            default: true
        }
    },

    methods: {
        checkMatrixExists(slide) {
            this.$parent.checkMatrixExists(slide);

            if (this.$root.$refs.carFinder.currentStep === 1) {
                // Update grid if we are at corresponding step
                this.$dispatch('CarFinder::updateFilters');
            }

            this.$nextTick(() => {
                // Proceed to the next step only from the landing page
                if (this.$root.$refs.carFinder.currentStep === 0) {
                    this.$root.$refs.carFinder.nextStep(false);
                }
            });
        }
    }
});
</script>
