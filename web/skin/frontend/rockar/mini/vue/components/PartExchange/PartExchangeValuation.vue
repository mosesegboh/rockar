<template>
    <div class="part-exchange-valuation row" v-show="valuationResult">
        <div class="col-3 px-car-worth">
            <p class="px-bold-heading">{{ 'Today your vehicle is worth' | translate }}</p>
            <p class="h1">{{ partExchangeValue | numberFormat '0,0' true }}</p>
        </div>

        <div class="col-3 px-outstanding-finance">
            <div class="row">
                <div class="col-12">
                    <p class="px-bold-heading">{{ 'Outstanding finance on your vehicle?' | translate }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <p class="px-how-much">{{ 'Left to pay?' | translate }}</p>
                </div>

                <div class="col-6">
                    <input type="text" v-model="outstandingFinance | numberOutstandingFinanceFormat" class="px-input keyboard-numbers">
                </div>
            </div>
        </div>

        <div class="col-6 px-result-actions">
            <div class="row">
                <p class="px-bold-heading">&nbsp;</p>
                <p class="px-light-text">
                    {{ 'We value your vehicle based on what you\'ve told us and what it\'s worth today.  This value can change if the details you have told us changes or you choose to buy a vehicle later.' | translate }}
                </p>
            </div>
        </div>

        <div class="col-12">
            <div class="row">
                <div class="px-result-actions-buttons">
                    <button class="button-medium button-empty-blue" @click="continueWithout()">{{ 'Continue without Trade in' | translate }}</button>
                    <button class="button-medium confirm-button"
                        :class="[isValuationValid ? '' : 'button-disabled']"
                        :disabled="!isValuationValid"
                        @click="saveValuation()">{{ 'Continue with this Trade in' | translate }}
                    </button>
                    <span v-if="!isValuationValid">{{ 'As you have negative equity on your vehicle, please click Continue without Trade in or' | translate}}
                        <a href="/contact-us">{{ 'Contact Us' | translate }}</a>{{' to discuss.' | translate }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import PartExchangeValuation from 'mini/components/Shared/PartExchangeValuation';
    import translateString from 'core/filters/Translate';

    export default Vue.extend({
        mixins: [PartExchangeValuation],

        computed: {
            ProductGrid() {
                return this.$root.$refs.productGrid;
            }
        },

        methods: {
            translateString,

            saveValuationSuccess(save) {
                save = save.data;
                this.PX.peId = save.px_id;

                if (this.outstandingFinance > 0) {
                    this.PX.$parent.title = `${this.translateString('Vehicle for Trade-In:')}<span class="description"><span>${this.PX.PXVrm.carInfo.title}: <b>${this.titleValue}</b></span></span>`;
                } else {
                    this.PX.$parent.title = `${this.translateString('Vehicle for Trade-In:')}<span class="description"><span>${this.PX.PXVrm.carInfo.title}: <b>${this.titleValue}</b></span></span>`;
                }

                this.PX.ajaxLoading = false;
                this.valuationResult = true;
                this.PX.valuationResult = true;
                this.valuationCompleted = true;
                this.PX.closePartExchange();
                this.$root.$refs.financeFilter.filterCollection();

                EventsBus.$emit('CarFinder::saveValuationSuccess');
            },

            savePartExchangeToSessionSuccess() {
                this.PX.ajaxLoading = false;
                this.valuationCompleted = true;
                this.PX.closePartExchange();
                this.$root.$refs.financeFilter.filterCollection();
            },

            continueWithout() {
                this.valuationResult = true;
                this.PX.valuationResult = true;
                this.valuationCompleted = true;
                this.PX.$parent.title = this.translateString('I\'m not swapping a vehicle');
                this.savePartExchangeToSession();
                this.closePartExchange();

                this.$dispatch('CarFinder::saveValuationSuccess');
            }
        }
    });
</script>