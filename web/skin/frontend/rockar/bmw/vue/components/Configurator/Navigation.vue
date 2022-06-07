<template>
    <div class="configurator-nav">
        <div class="configurator-actions row">
            <template v-if="currentDevice !== 'mobile'">
                <div class="nav-buttons">
                    <div class="configurator-actions-desktop" :class="[currentDevice !== 'mobile' ? 'col-3' : 'col-1']">
                        <button class="button button-narrow back-button button-gray-light nav-buttons" @click="movePrev">
                            <span class="nav-arrow arrow-back"></span>
                            <template v-if="currentDevice !== 'mobile'">
                                <template v-if="prev === false">{{ 'Back to Results' | translate }}</template>
                                <template v-if="prev === 'details'">{{ 'Back to Model' | translate }}</template>
                                <template v-if="prev === 'accessories'">{{ 'Back to Accessories' | translate }}</template>
                            </template>
                        </button>
                    </div>

                    <div class="configurator-actions-desktop shift-6" :class="[currentDevice !== 'mobile' ? 'col-3' : 'col-1']">
                        <button class="button button-narrow button-gray-light nav-buttons right" @click="moveNext">
                            <template v-if="currentDevice !== 'mobile'">
                                <template v-if="next === 'accessories'">{{ 'Add accessories' | translate }}</template>
                                <template v-if="next === 'msp'">{{ 'Add extras' | translate }}</template>
                                <template v-if="next === 'checkout'">{{ 'Checkout' | translate }}</template>
                            </template>
                            <span class="nav-arrow arrow-next"></span>
                        </button>
                    </div>
                </div>
            </template>

            <template v-else>
                <div class="col-12 configurator-actions-mobile">
                    <div class="select-item select-item-steps">
                        <button class="back-button button button-empty-dark" @click="movePrev">
                            <span class="nav-arrow arrow-back"></span>
                        </button>
                    </div>

                    <div class="select-item select-item-middle select-item-middle-left">
                        <div class="select-block" :class="{ 'select-item-single': activeStep !== 'details' }">
                            <button class="button button-empty-dark" @click="toggleActions">
                                {{ 'Select' | translate }}<span class="nav-arrow arrow-down"></span>
                            </button>

                            <div class="select-content" :style="isActionsOpen">
                                <div class="menu-item">
                                    <slot name="save"></slot>
                                </div>

                                <div class="menu-item">
                                    <slot name="compare"></slot>
                                </div>

                                <template v-if="hasYoudrive">
                                    <div class="menu-item">
                                        <button class="button button-slate-gray" @click="$dispatch('open-youdrive')">
                                            {{ 'Test drive' | translate }}
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="select-item select-item-middle" v-if="activeStep === 'details'">
                        <div class="select-block">
                            <button class="button button-empty-dark" @click="toggleSlider">
                                <template v-if="isExterior">{{ 'Exterior' | translate }}</template>
                                <template v-if="!isExterior">{{ 'Interior' | translate }}</template>
                                <span class="nav-arrow arrow-down"></span>
                            </button>
                        </div>

                        <div class="select-content" :style="isSliderOpen">
                            <div class="menu-item">
                                <button class="button" :class="[isExterior ? 'button-slate-gray' : 'button-silver-chalice']" @click="setExterior">
                                    {{ 'Exterior' | translate }}
                                </button>
                            </div>

                            <div class="menu-item">
                                <button class="button" :class="[!isExterior ? 'button-slate-gray' : 'button-silver-chalice']" @click="setInterior">
                                    {{ 'Interior' | translate }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="select-item select-item-steps">
                        <button class="button button-empty-dark" @click="moveNext">
                            &nbsp;<span class="nav-arrow arrow-next"></span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            steps: {
                required: true,
                type: Array
            },

            activeStep: {
                required: true,
                type: String
            },

            isExterior: {
                required: true,
                type: Boolean
            },

            hasYoudrive: {
                required: true,
                type: Boolean
            }
        },

        data() {
            return {
                actionsOpen: false,
                sliderOpen: false
            }
        },

        computed: {
            currentDevice() {
                return this.$store.state.general.device;
            },

            isActionsOpen() {
                return {
                    display: this.actionsOpen ? 'block' : 'none'
                }
            },

            isSliderOpen() {
                return {
                    display: this.sliderOpen ? 'block' : 'none'
                }
            },

            next() {
                const stepID = this.steps.indexOf(this.activeStep);
                return this.steps[stepID + 1] || this.steps[stepID];
            },

            prev() {
                const stepID = this.steps.indexOf(this.activeStep);
                return stepID === 0 ? false : this.steps[stepID - 1];
            }
        },

        methods: {
            toggleActions() {
                this.actionsOpen = !this.actionsOpen;
                this.sliderOpen = false;
            },

            toggleSlider() {
                this.sliderOpen = !this.sliderOpen;
                this.actionsOpen = false;
            },

            setExterior() {
                this.$dispatch('set-exterior');
                this.sliderOpen = false;
            },

            setInterior() {
                this.$dispatch('set-interior');
                this.sliderOpen = false;
            },

            movePrev() {
                this.$dispatch('move-prev');
                this.actionsOpen = false;
                this.sliderOpen = false;
            },

            moveNext() {
                this.$dispatch('move-next');
                this.actionsOpen = false;
                this.sliderOpen = false;
            }
        },

        events: {
            'Configurator::collapse'() {
                this.actionsOpen = false;
                this.sliderOpen = false;
            }
        }
    });
</script>
