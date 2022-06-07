<template>
    <div>
        <div class="you-build-action-wrapper mobile-tablet">
            <a href="{{youBuildUrl}}">
                <div class="you-build-action-container" @click="redirectIfSingleModel()">
                    <span class="you-build-logo"></span>
                    <div class="message">
                        <p>
                            Looking for a custom creation? Build your own BMW motorcycle.
                        </p>
                    </div>
                </div>
            </a>
        </div>
        <div class="you-build-action-wrapper desktop-only">
            <div class="you-build-action-container">
                <div class="message">
                    <p>
                        Looking for a custom creation? Build your own BMW motorcycle.
                    </p>
                </div>
                <span class="actions">
                    <button type="button" class="button button-default" @click="redirectIfSingleModel()">
                    {{ 'Build Now' | translate }}
                    </button>
            </span>
            </div>
        </div>
    </div>
</template>

<script>
export default Vue.extend({
    props: {
        youBuildUrl: {
            required: true,
            type: String
        },
        modelAttribute: {
            required: false,
            type: String
        },
        youBuildProductsCount: {
            required: false,
            type: [Number, Boolean],
            default: false
        }
    },

    computed: {
        modelFilter() {
            const filters = this.$parent.appliedFilters;
            const modelFilter = filters.find((element) => element.code === this.modelAttribute);
            return typeof modelFilter === 'undefined' ? false : modelFilter;
        },

        isSingleModel() {
            if (!this.modelFilter) {
                return false;
            }
            let count = 0;
            this.modelFilter.options.forEach(model => {
                count += model.state ? 1 : 0;
            });

            return count === 1;
        },
    },

    methods: {
        redirectIfSingleModel() {
            if (this.isSingleModel) {
                const modelFilter = this.$store.state.carFinder.carFilters.find((element) => element.code.substr(-5) === 'model');
                if (modelFilter) {
                    if (this.youBuildProductsCount !== false && this.youBuildProductsCount < 1) {
                        this.$root.$refs.carFinder.updateCurrentStepInSession(0, this.$root.$refs.productGrid.options.updateYouBuildStepUrl);
                    } else {
                        modelFilter.options.forEach(model => {
                            if (model.state) {
                                sessionStorage.setItem('singleModel', model.title);
                            }
                        });
                        this.$root.$refs.carFinder.updateCurrentStepInSession(1, this.$root.$refs.productGrid.options.updateYouBuildStepUrl);
                    }
                }
            } else {
                this.$root.$refs.carFinder.updateCurrentStepInSession(0, this.$root.$refs.productGrid.options.updateYouBuildStepUrl);
            }
            window.location.href = this.youBuildUrl;
        }
    },
});
</script>
