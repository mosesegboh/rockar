<template>
    <div id="finance-filter" v-show="isVisible">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <div class="finance-filter-wrapper">
            <div class="title-wrapper" v-show="this.$root.$refs.carFinder.currentStep !== 0">
                <template v-if="appliedFilters.length > 0">
                    <div class="sort-filter mobile">
                        <div class="applied-filters">
                            <ul>
                                <li v-for="(index, filter) in appliedFilters" :key="index">
                                    <div>
                                        <span>{{ filter.label }}</span>
                                        <a href="javascript:void(0)" @click="removeFilter(filter)">
                                            <span class="icon-close-blue"></span>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>
                <span class="filter-title"
                      :class="{ 'active' : appliedFilters.length === 0 }"
                >
                    {{ 'Payment Filters' | translate }}
                </span>
            </div>
            <app-finance-options
                :finance-options="financeOptions"
                :active-finance-group="defaultPayment.group_id"
                v-ref:finance-options
            ></app-finance-options>
            <div class="finance-filter-sliders" v-show="!isPayInFull">
                <div class="finance-filter-slider-wrapper deposit-filter" v-show="!isHirePaymentByGroupId(section)">
                    <div class="finance-filter-slider-wrapper">
                        <p v-show="abTestHelper" class="field-title">{{ 'Monthly budget' | translate }} </p>
                        <div v-show="abTestHelper" class="finance-filter-slider" data-id="monthlypay-filter">
                            <app-range-slider
                                :use-id="true"
                                :options="currentFinanceSteps.monthlypay"
                                :active="financeParams.monthlypay"
                                :active-on-slide="true"
                                custom-event-slide="FinanceFilter::mps-change"
                                custom-event="FinanceFilter::mps-update"
                                v-ref:monthly-pay-slider>
                            </app-range-slider>
                            <div class="finance-filter-slider-input">
                                <div>
                                    <input
                                        type="text"
                                        v-model="monthlyPay | numberCurrencyFormat"
                                        class="keyboard-numbers"
                                        size="10"
                                        id="monthly-pay"
                                        maxlength="14"
                                        @change="updateFields($event, 'monthlypay', 'monthlyPay')"
                                    >
                                </div>
                                <a class="dsp2-link filter" @click="openMoreFiltersOverlay()">
                                    <span class="arrow-next"></span>
                                    {{ "More Filters" | translate }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="finance-filter-sliders" v-show="isPayInFull">
                <div class="finance-filter-slider-wrapper">
                    <p class="field-title">{{ 'Your total budget' | translate }}</p>
                    <div data-id="pay-in-full-filter">
                        <app-range-slider
                            :use-id="false"
                            v-show="typeof currentFinanceSteps.payinfull !== 'undefined'"
                            :active="[payInFull[0], payInFull[1]]"
                            :active-on-slide="true"
                            :range="true"
                            :min="currentFinanceSteps.payinfull.min"
                            :max="currentFinanceSteps.payinfull.max"
                            :step="currentFinanceSteps.payinfull.step"
                            custom-event-slide="FinanceFilter::pif-change"
                            custom-event="FinanceFilter::pif-update"
                            v-ref:pay-in-full-slider
                        ></app-range-slider>
                    </div>
                    <div class="finance-filter-slider small three-action">
                        <div class="finance-filter-slider-input input-one">
                            <div>
                                <input
                                    @change="areFinanceSlidersChanged = true"
                                    type="text"
                                    :value="payInFull[0] | numberInputFormat"
                                    v-model="payInFullInputs[0] | numberInputFormat"
                                    size="10"
                                    class="keyboard-numbers"
                                    maxlength="14"
                                    @blur="changePayInFull()"
                                    @keyup.enter="changePayInFull()"
                                >
                            </div>
                        </div>
                        <div class="finance-filter-slider-input input-two">
                            <div>
                                <input
                                    @change="areFinanceSlidersChanged = true"
                                    type="text"
                                    :value="payInFull[1] | numberInputFormat"
                                    v-model="payInFullInputs[1] | numberInputFormat"
                                    size="10"
                                    class="keyboard-numbers"
                                    maxlength="14"
                                    @blur="changePayInFull()"
                                    @keyup.enter="changePayInFull()"
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <a class="dsp2-link filter" @click="openMoreFiltersOverlay()">
                    <span class="arrow-next"></span>
                    {{ 'More Filters' | translate }}
                </a>
            </div>
            <app-finance-filter-overlay
                :show.sync="moreFiltersOverlay"
                v-if="moreFiltersOverlay"
                :finance-options="financeOptions"
                :finance-steps="financeSteps"
                :finance-groups-params="financeGroupsParams"
                :selected-finance-group-data="selectedFinanceGroupData"
                :est-value-disclaimer="estValueDisclaimer"
                :px-removed="pxRemoved"
                @close-popup="closeMoreFiltersOverlay"
            >
            </app-finance-filter-overlay>
            <app-modal
                v-ref:trade-in-modal
                :blur-background="false"
                @close-popup="flipModals"
                class="px-popup-wrapper"
            >
                <div slot="content"><slot name="part-exchange-filter"></slot></div>
            </app-modal>
        </div>
    </div>
</template>

<script>
import appRangeSlider from 'core/components/Elements/RangeSlider';
import appFinanceFilter from 'dsp2/components/CarFinder/FinanceFilter';
import appFinanceOptions from 'dsp2/components/CarFinder/FinanceOptions';
import appSelect from 'core/components/Elements/Select';
import appFinanceFilterOverlay from 'mini2/components/CarFinder/FinanceFilterOverlay';
import appModal from 'dsp2/components/Elements/Modal';

export default appFinanceFilter.extend({
    components: {
        appFinanceOptions,
        appRangeSlider,
        appSelect,
        appFinanceFilterOverlay,
        appModal
    }
});
</script>
