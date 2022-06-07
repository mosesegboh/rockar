<template>
    <div id="car-finder">
        <div class="general-preloader" v-show="ajaxLoading || CarFilter.showLoading"><div class="show-loading"></div>
        </div>
        <div class="filters-header-mobile" v-show="currentStep !== 0 && this.$root.$refs.carFilter.showMobileMenu" @click="closeFilters()">
            {{ 'Filters' | translate }}
            <span class="close">&nbsp;</span>
        </div>
        <slot name="model-filter-slot"></slot>
        <slot name="car_filter"></slot>
        <slot></slot>
        <div class="car-finder-container" v-show="currentStep !== steps.length - 1">
        </div>
    </div>
</template>

<script>
import appCarFinder from 'dsp2/components/CarFinder/CarFinder.vue';

export default appCarFinder.extend({});
</script>
