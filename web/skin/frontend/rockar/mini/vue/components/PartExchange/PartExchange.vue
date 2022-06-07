<template>
    <div class="part-exchange">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <app-part-exchange-custom-car v-show="openCustom" v-ref:part-exchange-custom-car></app-part-exchange-custom-car>

        <div class="part-exchange-regular row" v-show="!openCustom">
            <div class="part-exchange-left col-3 col-md-6">

                <app-part-exchange-vrm
                        car-alternative-details="url"
                        v-ref:part-exchange-vrm>
                </app-part-exchange-vrm>

                <div class="px-mileage-block">
                    <p class="px-regular-heading">{{ 'My car\'s mileage is' | translate }}</p>
                    <input type="text" placeholder="0" class="px-input keyboard-numbers" v-model="mileage | numberFormat" :disabled="disableSelects">
                </div>

            </div>

            <div class="part-exchange-middle col-4 col-md-6">
                <div class="px-condition-block">
                    <p class="px-light-text">{{ 'I\'d rate my car as:' | translate }}</p>

                    <app-range-slider
                        v-if="activeSliderCondition"
                        :use-id="true" :active-on-slide="true"
                        :options="carConditions" :display-steps="true"
                        :active="activeSliderCondition"
                        :is-disabled="disableSelects"
                        custom-event="PartExchange::changeCondition"
                        custom-event-change="PartExchange::changeCondition"
                        v-ref:condition-slider>
                    </app-range-slider>

                    <div class="px-condition-list" v-for="condition in carConditions" v-if="activeCondition == condition.id">
                        <p class="px-condition-title" >{{ condition.title }}</p>
                        <p class="px-light-text">{{{ condition.body }}}</p>
                    </div>

                </div>
            </div>

            <div class="part-exchange-right col-5 col-md-12">
                <div class="px-additional-info-block">

                    <div class="px-additional-info-checkbox" v-for="info in additionalInfo">
                        <input type="checkbox" :id="'info-checkbox'+info.id" v-model="info.checked" :disabled="disableSelects">
                        <label :for="'info-checkbox'+info.id" ><span></span></label>

                        <div class="info-label-wrap">
                            <label :for="'info-checkbox'+info.id" class="px-light-text">{{ info.title }}</label>
                            <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                                <span class="action-badge info-small" slot="tooltipElement"></span>

                                <div slot="tooltipContent">
                                    <p>{{ info.hint }}</p>
                                </div>
                            </app-tooltip>
                        </div>

                        <p :class="{ 'warning' : !info.isRequired }" v-if="info.error.length > 0 && !info.checked">{{ info.error }}</p>
                    </div>

                </div>

                <div class="px-submit-block">
                    <button
                            :class="[checkData() ? 'button-default' : 'button-disabled']"
                            :disabled="!checkData()" @click="getValuation()"
                            v-if="!valuationResult">{{ 'Get Valuation' | translate }}
                    </button>
                    <button class="button button-empty" @click="resetFilters()" v-if="!valuationResult">{{ '...Or start again' | translate }}</button>
                </div>
            </div>
        </div>

        <app-part-exchange-valuation v-ref:part-exchange-valuation-result></app-part-exchange-valuation>
    </div>
</template>

<script>
    import PartExchange from 'mini/components/Shared/PartExchange';
    import appPartExchangeVrm from 'mini/components/PartExchange/PartExchangeVRM';
    import appPartExchangeCustomCar from 'mini/components/PartExchange/PartExchangeCustomCar';
    import appPartExchangeValuation from 'mini/components/PartExchange/PartExchangeValuation';
    import appPartExchangeNegativeEquity from 'mini/components/PartExchange/PartExchangeNegativeEquity';
    import appTooltip from 'core/components/Elements/Tooltip';

    export default Vue.extend({
        mixins: [PartExchange],

        methods: {
            closePartExchange() {
                this.$parent.show = false;
                this.$root.$refs.financeFilter.$parent.show = true;
            }
        },

        components: {
            appPartExchangeVrm,
            appPartExchangeCustomCar,
            appPartExchangeValuation,
            appPartExchangeNegativeEquity,
            appTooltip
        },

        events: {
            'PartExchange::softReset'(deletePx) {
                this.softReset(deletePx);
                this.$root.$refs.financeFilter.filterCollection();
            }
        }
    });
</script>
