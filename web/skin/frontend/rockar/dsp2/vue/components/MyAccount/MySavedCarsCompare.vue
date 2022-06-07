<template>
    <div class="car-compare">
        <div>
            <app-modal class-name="car-compare-modal" :show="!noMessages" @close-popup="closeCompareModal" v-ref:modal>
                <div slot="content" class="compare-add">
                    <app-car-compare-modal-content
                        @close="showCompareList"
                        :limit-reached="limitReached"
                        :error-message="errorMessage"
                        :server-error="serverError"
                    ></app-car-compare-modal-content>
                </div>
            </app-modal>
        </div>
    </div>
</template>

<script>
    import appModal from 'core/components/Elements/Modal';
    import appCarCompareModalContent from 'dsp2/components/CarFinder/CarCompareModalContent';
    import EventTracker from 'dsp2/mixins/EventTracker';

    export default Vue.extend({
        mixins: [EventTracker],

        data: () => ({
            serverError: false,
            errorMessage: false,
            ajaxLoading: false
        }),

        props: {
            compare: {
                required: true,
                type: Object
            },

            isInCompareList: {
                required: true,
                type: Boolean
            },

            compareAddUrl: {
                required: true,
                type: String
            },

            compareRemoveUrl: {
                required: true,
                type: String
            },

            baseUrl: {
                required: true,
                type: String
            },

            renderInBody: {
                required: false,
                type: Boolean,
                default: false
            }
        },

        computed: {
            noMessages() {
                return !this.errorMessage || !this.serverError || !this.limitReached;
            },

            limitReached() {
                let compareLimitReached = false;

                if (this.compare.carsIn > this.compare.limit) {
                    compareLimitReached = true;
                }

                return compareLimitReached;
            }
        },

        methods: {
            ajaxCompareAdd() {
                this.$root.$refs.myDetails.ajaxLoading = true;

                this.$http({
                    url: this.isInCompareList ? this.compareRemoveUrl : this.compareAddUrl,
                    method: 'GET',
                }).then(this.ajaxSaveCarSuccess, this.ajaxSaveCarFail);
            },

            ajaxSaveCarSuccess(data) {
                data = data.data;
                this.compare.carsIn = data.carsInCompare;
                this.$parent.isInCompareList = !this.$parent.isInCompareList;

                if (this.$parent.isInCompareList) {
                    this.fireEventForTracking(
                        this.getEventConstants().PAGEDESCRIPTION.TRIGGERS,
                        `${this.getEventConstants().TRIGGERTRACKERVALUES.ADDTOCOMPARE}${this.$parent.carTitle} ${this.$parent.carDescription}`
                    );
                    sessionStorage.setItem('CarFinder::redirectToResults', 'true');
                    window.location.href = this.baseUrl;
                } else {
                    this.$root.$refs.myDetails.ajaxLoading = false;
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

                this.$root.$refs.myDetails.ajaxLoading = false;
            },

            closeCompareModal() {
                this.compare.carsIn -= 1;
                this.errorMessage = false;
                this.serverError = false;
                this.limitReached = false;
                this.noMessages = true;
            },

            showCompareList() {
                this.$root.$refs.myDetails.ajaxLoading = true;
                sessionStorage.setItem('CarFinder::redirectToResultsCompare', 'true');
                window.location.href = this.baseUrl;
            }
        },

        ready() {
            if (this.renderInBody) {
                this.$nextTick(() => {
                    this.$refs.modal.$appendTo(document.body);
                });
            }
        },

        components: {
            appModal,
            appCarCompareModalContent
        }
    });
</script>
