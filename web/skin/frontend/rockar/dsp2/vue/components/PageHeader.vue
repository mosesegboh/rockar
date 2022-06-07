<template>
    <div id="page-header" v-show="visible">
        <div class="header-desktop">
            <div class="previous" @click="previous()" v-show="page !== ''">
                <span>{{ 'Previous' | translate }}</span>
            </div>
            <div class="title">
                <span>{{ title | translate }}</span>
            </div>
        </div>
        <div class="header-mobile">
            <span class="previous" @click="previous()"></span>
            {{ title | translate }}
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            page: {
                required: false,
                type: String,
                default: ''
            },

            title: {
                required: true,
                type: String
            },

            previousUrl: {
                required: false,
                type: String,
                default: ''
            }
        },

        computed: {
            visible() {
                if (this.page === 'carFinder') {
                    return this.$store.state.carFinder.step > 0;
                }

                return true;
            },

            AccordionGroup() {
                return this.$root.$refs.accordionGroup;
            }
        },

        methods: {
            previous() {
                switch (this.page) {
                    case 'carFinder':
                        this.carFinderPrevious();
                        break;
                    case 'pdp':
                        this.PDPPrevious();
                        break;
                    case 'checkout':
                        this.checkoutPrevious();
                        break;
                    default:
                        this.defaultPrevious();
                }
            },

            carFinderPrevious() {
                this.$root.$refs.carFinder.prevStep();
            },

            PDPPrevious() {
                sessionStorage.setItem('CarFinder::redirectToResults', 'true');
                const carFinderUrl = window.sessionStorage.getItem('CarFinder::redirectToPdp');

                if (carFinderUrl) {
                    window.location.href = carFinderUrl;
                    return;
                }

                if (this.previousUrl) {
                    window.location.href = this.previousUrl;
                }
            },

            checkoutPrevious() {
                const openStep = this.AccordionGroup.openStep;
                const activeSubStep = this.AccordionGroup.$children[openStep].$children[0];

                if (activeSubStep.steps !== undefined && activeSubStep.currentStep !== 0) {
                    activeSubStep.previousStep(0);
                    return;
                }

                if (openStep > 0) {
                    this.AccordionGroup.showStep(openStep - 1);
                    return;
                }

                window.location.href = this.$root.$refs.checkout.product.url;
            },

            defaultPrevious() {
                if (this.previousUrl) {
                    if (this.previousUrl.includes('car-finder')) {
                        sessionStorage.setItem('CarFinder::redirectToResults', 'true');
                    }

                    window.location.href = this.previousUrl;
                }
            }
        }
    })
</script>
