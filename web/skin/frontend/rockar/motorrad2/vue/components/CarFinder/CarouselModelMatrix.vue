<template>
    <div class="carousel-wrapper">
        <div v-for="(index, slide) in slidesData" :key="slide.id">
            <div
                class="carousel-block"
                v-if="!isMobile"
                :class="{
                     'disabled' : slide.count === 0,
                     'selected' : slides[index].state
                }"
                 @click="allModels ? changeModel(slide.id) : ''"
            >
                <div
                    class="carousel-out-of-stock"
                    :class="{ 'active': !allModels && slide.count === 0 }"
                >
                    <p>{{ 'I’m out of stock or not within budget' | translate }}</p>
                </div>
                <div class="carousel-image-wrapper">
                    <div class="carousel-vertical-image-wrapper">
                        <label :for="`cs${$index + slide.id}`">
                            <img :src="slide.image" :alt="allModels ? slide.longTitle : slide.title">
                        </label>
                    </div>
                </div>
                <p>{{ slide.longTitle }}</p>

                <div class="checkbox-values" v-if="checkboxes">
                    <div class="carousel-checkbox-wrapper out-of-stock" v-if="slide.count === 0">
                        <input type="checkbox" :id="`cs${$index + slide.id}`" :disabled="true">
                        <label :for="`cs${$index + slide.id}`">
                            <span></span>{{ slide.title }}
                        </label>
                        <a class="notify-me" v-if="notifyMeEnabled" href="javascript:void(0);" @click="redirectToNotifyMe()">
                            <span>{{ 'Notify me' | translate }}</span>
                        </a>
                    </div>
                    <div class="carousel-checkbox-wrapper" v-else>
                        <input
                            v-model="slides[index].state"
                            type="checkbox"
                            :id="`cs${$index + slide.id}`"
                            @change="checkMatrixExists(slide)"
                        >
                        <label :for="`cs${$index + slide.id}`"><span></span>{{ slide.title }}</label>
                        <div class="checkbox-info">
                            <p>{{ slide.count }} {{ "Results" | translate }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="carousel-block">
                <div
                    @click="changeModel(slide.id)"
                    :class="{
                        'active' : selected === slide.id,
                        'has-selected' : this.hasSelected(slide.id)
                    }"
                >
                    <p class="dsp2-label">{{ slide.title }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import appCarouselModelMatrix from 'dsp2/components/CarFinder/CarouselModelMatrix';

    export default appCarouselModelMatrix.extend({});
</script>

