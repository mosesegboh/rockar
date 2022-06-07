<template>
    <div class="car-compare">
        <app-modal :show="ProductTopContainer ? !openCompareState : openCompareState" class-name="simple-popup" @close-popup="closeCompare">
            <div slot="content" class="compare-add">
                <div class="preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
                <app-car-compare-modal-content
                    @close="closeCompare"
                    :limit-reached="limitReached"
                    :error-message="errorMessage"
                    :server-error="serverError"
                ></app-car-compare-modal-content>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import coreCarCompare from 'core/components/CarFinder/CarCompare';
    import appCarCompareModalContent from 'dsp2/components/CarFinder/CarCompareModalContent';

    export default coreCarCompare.extend({
        props: {
            compareRemoveUrl: {
                required: true,
                type: String
            }
        },

        computed: {
            limitReached() {
                let compareLimitReached = false;

                if (this.compare.carsIn > this.compare.limit) {
                    compareLimitReached = true;
                    // Since this component is rendered from productPod, avoiding more than one popup appearance.
                    jQuery('.car-compare').first().css({ display: 'block' });
                }

                return compareLimitReached;
            },

            ProductTopContainer() {
                return this.$root.$refs.productTopContainer;
            },

            openCompareState() {
                if (this.ProductTopContainer) {
                    return !this.errorMessage || !this.serverError || !this.limitReached;
                } else {
                    return this.errorMessage || this.serverError || this.limitReached;
                }
            }
        },

        methods: {
            closeCompare() {
                this.compare.carsIn -= 1;
                this.$dispatch('close-compare');
            },

            ajaxStart() {
                if (!this.limitReached) {
                    this.ajaxCompareAdd();
                }
            },

            ajaxCompareAdd() {
                if (this.ProductTopContainer) {
                    this.ProductTopContainer.ajaxLoading = true;
                } else {
                    this.$dispatch('CarFinder::ajaxLoad', true);
                }

                this.$http({
                    url: this.isInCompareList ? this.compareRemoveUrl : this.compareAddUrl,
                    method: 'GET',
                }).then(this.ajaxSaveCarSuccess, this.ajaxSaveCarFail);
            },

            ajaxSaveCarSuccess(data) {
                data = data.data;
                this.compare.carsIn = data.carsInCompare;
                this.isInCompareList = !this.isInCompareList;
                this.$dispatch('Main::updateCompareData');

                if (this.ProductTopContainer) {
                    this.ProductTopContainer.ajaxLoading = false;
                } else {
                    this.$dispatch('CarFinder::ajaxLoad', false);
                }
            },

            ajaxSaveCarFail(error) {
                this.serverError = true;

                if (typeof error.data.errorMessage !== 'undefined') {
                    this.errorMessage = error.data.errorMessage;
                    this.compare.carsIn = error.data.carsInCompare + 1;
                } else {
                    console.error('CarCompare:', error);
                }

                if (this.ProductTopContainer) {
                    this.ProductTopContainer.ajaxLoading = false;
                } else {
                    this.$dispatch('CarFinder::ajaxLoad', false);
                }
            }
        },

        events: {
            'CarCompare::triggerAddRemove'() {
                this.ajaxCompareAdd();
            }
        },

        components: {
            appCarCompareModalContent
        }
    });
</script>
