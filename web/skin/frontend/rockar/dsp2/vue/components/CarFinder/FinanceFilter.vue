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
            </div>
            <a class="dsp2-link-s" @click="openMoreFiltersOverlay()">
                <span class="icon-cross"></span>
                {{ "More Filters" | translate }}
            </a>
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
import appFinanceOptions from 'dsp2/components/CarFinder/FinanceOptions';
import numeral from 'numeral';
import translateString from 'core/filters/Translate';
import appSelect from 'core/components/Elements/Select';
import appFinanceFilterOverlay from 'dsp2/components/CarFinder/FinanceFilterOverlay';
import appModal from 'dsp2/components/Elements/Modal';

export default Vue.extend({
    components: {
        appFinanceOptions,
        appRangeSlider,
        appSelect,
        appFinanceFilterOverlay,
        appModal
    },

    props: {
        estValueDisclaimer: {
            required: true,
            type: String
        },

        financeDefaultParams: {
            required: true,
            type: Object
        },

        financeItemsInRow: {
            required: true,
            type: Number
        },

        financeGroupsParams: {
            required: true,
            type: Object
        },

        defaultPayment: {
            required: true,
            type: Object
        },

        financeSteps: {
            required: true,
            type: Object
        },

        financeOptions: {
            required: true,
            type: Object
        },

        helpOverlayContent: {
            required: true,
            type: String
        },

        resetFinanceUrl: {
            required: true,
            type: String
        },

        financeActivePayment: {
            required: true,
            type: Object
        },

        isPxRemoved: {
            required: false,
            type: Boolean,
            default: false
        },

        expireDate: {
            required: false,
            type: String,
            default: ''
        },

        resetUrl: {
            required: false,
            type: String,
            default: ''
        },

        peId: {
            required: false,
            type: [Number, String],
            default: 0
        }
    },

    data: () => ({
        areFinanceSlidersChanged: false,
        financeParams: {},
        currentFinanceSteps: {},
        monthlyPay: 0,
        payInFull: 0,
        isPayInFull: false,
        payInFullInputs: 0,
        instalmentID: 0,
        showMonthlyPaymentSlider: false,
        previousSettings: '',
        previousPayInFullSettings: '',
        ajaxLoading: false,
        moreFiltersOverlay: false,
        annualMileage: 0,
        balloonPercentage: 0,
        payOver: 0,
        initialPay: 0,
        pxRemoved: false,
        showPxInfoBlock: true,
        openFromTradeInBlock: false,
        filtersToHide: ['visible_in']
    }),

    computed: {
        section() {
            return this.$store.state.finance.financeGroupId;
        },

        productGrid() {
            return this.$root.$refs.productGrid;
        },

        payInFullPayment() {
            return this.productGrid !== null ? this.productGrid.payInFullPayment : [];
        },

        pifSlider() {
            return this.$refs.payInFullSlider;
        },

        carFilter() {
            return this.$root.$refs.carFilter;
        },

        enableFilters() {
            return !this.manualFilterDisable && !this.activePayment.default;
        },

        selectedFinanceGroupData() {
            // Filter will return one item in array, so just take it
            return this.financeOptions.items.filter(group => parseInt(group.group_id) === this.section)[0];
        },

        activePayment() {
            return this.productGrid !== null ? this.productGrid.activePayment : {};
        },

        mpsSlider() {
            return this.$refs.monthlyPaySlider;
        },

        isFinanceStep() {
            return ['finance', 'landing', 'carFilter'].includes(this.$parent.steps[this.$parent.currentStep]);
        },

        isVisible() {
            return this.$parent.steps[this.$parent.currentStep] === 'carFilter' && !this.carFilter.showMobileMenu ? false : this.isFinanceStep;
        },

        /**
         * this serves as a reference to financeParams
         * @return {Boolean}
         */
        acceptedFinance() {
            return this.financeParams.acceptedFinance;
        },

        initialPaymentAmount() {
            return this.isPayInFull ? 0 : this.initialPay;
        },

        /**
         * property hides/shows the container for the monthly payment slider.
         * @return {String}, the inline styling of the container.
         */
        abTestHelper() {
            return this.showMonthlyPaymentSlider;
        },

        carFilters() {
            return this.$store.state.carFinder.carFilters;
        },

        appliedFilters() {
            return this.carFilters.filter(filter => filter.hasSelected && !this.filtersToHide.includes(filter.code));
        },

        isFilterVisible() {
            return this.appliedFilters.some(key => key.visible !== false);
        },

        options() {
            return this.$root.$refs.carFinder.options;
        },

        showLoading() {
            return this.ajaxLoading || this.$root.$refs.carFinder.ajaxLoading;
        },

        PartExchangeFilter() {
            return this.$root.$refs.partExchange.$refs.partExchangeFilter;
        }
    },

    methods: {
        validateParams() {
            this.updateFields(null, 'monthlypay', 'monthlyPay');
            this.changePayInFull();
            EventsBus.$emit('CarFinder::UpdateUrl');
        },

        /**
         * This takes in the active group ID and checks whether
         * it is the same as the instalment sale ID, if it is the same a true value is returned
         * @param {Integer} groupId - The ID given to each finance option
         * @return {Boolean}
         */
        isInstalmentSaleID(groupId) {
            return parseInt(this.instalmentID) === parseInt(groupId);
        },

        translateString,

        makeInputSelected() {
            this.isSelected = true;
        },

        getFinanceDefaultParams() {
            return JSON.parse(JSON.stringify(this.financeDefaultParams));
        },

        isHirePaymentByGroupId(groupId) {
            const hirePayments = this.productGrid !== null ? this.productGrid.hirePayments : [];
            for (let i = 0; i < hirePayments.length; i++) {
                if (parseInt(hirePayments[i].group_id) === parseInt(groupId)) {
                    return true;
                }
            }

            return false;
        },

        resetFilters() {
            const groupId = parseInt(this.defaultPayment.group_id);

            this.financeGroupsParams = this.getFinanceDefaultParams();
            this.financeParams = this.financeGroupsParams[groupId];
            this.financeGroupType = this.financeParams.financeGroupType;

            this.activePayment.group_id = groupId;

            if (this.section === groupId) {
                this.updateFinanceFilters(groupId);
            } else {
                this.$store.commit('setFinanceGroupId', groupId);
            }

            this.setupFilterCollection();
        },

        setupFilterCollection() {
            // FIXME: this.section is required twice in this object
            // under the keys `method` and `group_id`
            const filterData = {
                method: this.section,
                group_id: this.section,
                monthlypay: this.monthlyPay,
                payinfull: this.payInFull,
                maintenance: this.maintenance,
                acceptedFinance: this.acceptedFinance,
                deposit: this.initialPay,
                term: this.payOver,
                balloonPercentage: this.balloonPercentage,
                mileage: this.annualMileage
            };

            // FIXME: Don't directly read another components data
            if (this.productGrid && this.productGrid.CarFilter && this.productGrid.CarFilter.financeFilters) {
                // FIXME: Don't directly edit another components data
                this.productGrid.CarFilter.financeFilters = filterData;
            }

            // Ensure we return the final data here so that this method can be overwritten in another component
            return filterData;
        },

        hardResetFilters(callback) {
            this.resetFilters();

            this.$http({
                url: this.resetFinanceUrl,
                method: 'POST',
                emulateJSON: true
            }).then(callback);
        },

        updateFinanceFilters(groupId) {
            if (!this.isHirePaymentByGroupId(groupId)) {
                this.maintenance = 0;
            }

            this.currentFinanceSteps = this.financeSteps[groupId];
            this.financeParams = JSON.parse(JSON.stringify(this.financeGroupsParams[groupId]));

            this.mpsSlider.changeSteps(this.currentFinanceSteps.monthlypay);
            const monthlypay = parseInt(this.financeParams.monthlypay);
            this.mpsSlider.changeActive(monthlypay);
            this.monthlyPay = monthlypay;
            this.productGrid.financeParams = this.financeGroupsParams;
        },

        filterCollection() {
            this.setupFilterCollection();
            // Need to keep group_id and method synced
            this.productGrid.activePayment.group_id = parseInt(this.section);
            this.productGrid.activePayment.method = parseInt(this.section);
            this.productGrid.activePayment.default = 0;
            // Need to keep group_id and method synced
            this.productGrid.financeParams[parseInt(this.section)].method = this.section;
            this.productGrid.financeParams[parseInt(this.section)].group_id = this.section;

            this.productGrid.CarFilter.loadNextPage(
                this.productGrid.productsList.page.currentPageResetUrl,
                `${this.productGrid.productsList.page.totalCount} ${this.productGrid.productsList.page.totalCountString}`,
                false
            );

            this.$parent.show = false;
        },

        updateFields(event, param, valueElem) {
            if (param !== undefined && valueElem !== undefined) {
                const minVal = this.currentFinanceSteps[param][0].id;
                const maxVal = this.currentFinanceSteps[param][this.currentFinanceSteps[param].length - 1].id;

                if (this[valueElem] < minVal) {
                    this[valueElem] = minVal;
                } else if (this[valueElem] > maxVal) {
                    this[valueElem] = maxVal;
                }
            }

            const activeGroupId = this.section;
            this.areFinanceSlidersChanged = true;
            this.financeGroupsParams[activeGroupId].monthlypay = this.monthlyPay;
            this.productGrid.financeParams[activeGroupId] = this.financeGroupsParams[activeGroupId];
            this.mpsSlider.changeActive(this.monthlyPay);
        },

        /**
         * Override default2 changePayInFull method.
         * We need to change steps on slider in order to pull in correct min, max, step for slider
         */
        changePayInFull() {
            const minVal = this.financeGroupsParams[this.section].payinfull[0];
            const maxVal = this.financeGroupsParams[this.section].payinfull[1];

            if (this.payInFullInputs[0] > this.payInFullInputs[1]) {
                this.payInFullInputs.$set(0, this.payInFullInputs[1]);
            }

            if (this.payInFullInputs[0] < minVal) {
                this.payInFullInputs.$set(0, minVal);
            }

            if (this.payInFullInputs[1] > maxVal) {
                this.payInFullInputs.$set(1, maxVal);
            }

            this.areFinanceSlidersChanged = true;

            this.payInFull = this.payInFullInputs;
            this.pifSlider.min = this.financeSteps[this.section].payinfull.min;
            this.pifSlider.changeActive(this.payInFull);
            this.$nextTick(() => this.pifSlider.changeSteps(this.currentFinanceSteps.payinfull.step));
        },

        /**
         * Get a string that represents the current finance settings
         *
         * @returns {string}
         */
        getSettingsString() {
            return [
                this.monthlyPay.toString(),
                this.payOver.toString(),
                this.annualMileage.toString(),
                this.initialPay.toString(),
                this.balloonPercentage.toString(),
                this.section.toString()
            ].join('/');
        },

        /**
         * Get a string that represents the current payInFull slider state
         *
         * @returns {string}
         */
        getPayInFullSettingsString() {
            return Array.isArray(this.payInFullInputs) ? this.payInFullInputs.join('/') : '';
        },

        /**
         * Apply filter and proceed with next steps
         */
        acceptFilter() {
            this.ajaxLoading = true;
            // do finance apply
            this.acceptFinance();
            this.areFinanceSlidersChanged = false;

            if (!this.isHirePaymentByGroupId(this.section)) {
                this.maintenance = 0;
            }

            this.currentFinanceSteps = this.financeSteps[this.section];
            this.financeParams = JSON.parse(JSON.stringify(this.financeGroupsParams[this.section]));
            this.monthlyPay = parseInt(this.financeParams.monthlypay);
            this.productGrid.financeParams = this.financeGroupsParams;

            this.applyFilter();
            EventsBus.$emit('CarFinder::UpdateUrl');
            this.$dispatch('CarFinder::updateFilters');
            this.ajaxLoading = false;
        },

        /**
         * Apply finance filter if settings have changed,
         * otherwise just close the popup
         */
        applyFilter() {
            /** @var {string} currentSettings **/
            const currentSettings = this.getSettingsString();
            /** @var {string} currentPayInFullSettings **/
            const currentPayInFullSettings = this.getPayInFullSettingsString();

            if (
                currentSettings !== this.previousSettings
                // If pay in full slider change we also need to reload the finance
                || (this.isPayInFull && currentPayInFullSettings !== this.previousPayInFullSettings)
            ) {
                this.previousSettings = currentSettings;

                this.previousPayInFullSettings = currentPayInFullSettings;
                this.areFinanceSlidersChanged = false;
                this.financeConfirmationPopup = false;
                this.filterCollection();
                this.manualFilterDisable = false;
            }
            this.$dispatch('Main::updateCompareData');
        },

        acceptFinance() {
            this.financeParams.acceptedFinance = true;
        },

        resetAcceptedFinance() {
            this.financeParams.acceptedFinance = false;
            EventsBus.$emit('CarFinder::UpdateUrl');
        },

        /**
         * Clear previous settings so next time applyFilter is
         * called, it will reload the filters
         */
        clearPreviousSettings() {
            this.previousSettings = '';
            this.previousPayInFullSettings = '';
        },

        /**
         * Check if given group ID is pay in full payment
         *
         * @param groupId
         * @returns {boolean}
         */
        isPayInFullPaymentByGroupId(groupId) {
            const payInFullGroup = this.payInFullPayment.group_id;

            if (payInFullGroup) {
                return payInFullGroup === groupId;
            }

            for (let i = 0; i < this.payInFullPayment.length; i += 1) {
                if (this.payInFullPayment[i].group_id === groupId) {
                    return true;
                }
            }

            return false;
        },

        /**
         * Enable the more filters overlay
         */
        openMoreFiltersOverlay() {
            this.moreFiltersOverlay = true;
            this.changeHeaderNavigationClass(true);
        },

        /**
         * Disable the more filters overlay
         */
        closeMoreFiltersOverlay() {
            this.moreFiltersOverlay = false;
            this.changeHeaderNavigationClass(false);
        },

        /**
         * Addes/removes finance-overlay-open class to header
         */
        changeHeaderNavigationClass(lightMode) {
            if (lightMode) {
                jQuery('body').addClass('finance-overlay-open');
            } else {
                jQuery('body').removeClass('finance-overlay-open');
            }
        },

        /**
         * Closes More Filters Overlay
         * Opens Trade-in Modal
         */
        openTradeInModal() {
            if (this.$root.$refs.financeFilter.isPxRemoved) {
                window.EventsBus.$emit('PartExchange::resetPxData');
            }

            this.moreFiltersOverlay = false;
            this.$refs.tradeInModal.show = true;

            if (this.PartExchangeFilter && this.PartExchangeFilter.currentStep === 2
                || this.PartExchangeFilter && this.PartExchangeFilter.currentStep === 0 && !this.showPxInfoBlock) {
                window.EventsBus.$emit('PartExchange::triggerEventTrackerCheck');
            }
        },

        /**
         * Closes More Filters Overlay
         * Opens Trade-in Modal (saved Px)
         */
        openTradeInModalPxActive(isExpired) {
            this.openTradeInModal();
            EventsBus.$emit('FinanceFilter::setSavedPxData', isExpired);

            if (!isExpired) {
                window.EventsBus.$emit('PartExchangeFilter::hasSavedPx');
            }
        },

        /**
         * Closes Trade-in Modal
         * Opens More Filters Overlay (if opened from Filters Overlay)
         */
        flipModals() {
            this.$refs.tradeInModal.show = false;

            if (!this.openFromTradeInBlock) {
                this.moreFiltersOverlay = true;
            } else {
                this.openFromTradeInBlock = false;
            }
        },

        applyRemovedPx() {
            this.pxRemoved = true;
        },

        applyBlock(data) {
            if (data) {
                this.openFromTradeInBlock = true;
            }
        },

        removePX() {
            this.ajaxLoading = true;
            this.$http({
                url: this.resetUrl,
                method: 'POST',
                emulateJSON: true,
                data: {
                    px_id: this.peId
                }
            }).then(this.removePXSuccess, this.removePXFail);
        },

        removePXSuccess() {
            this.hidePxInfoBlock();
            window.EventsBus.$emit('PartExchange::resetPxData');
            this.$dispatch('CarFinder::updateFilters');
            this.ajaxLoading = false;
        },

        removePXFail(error) {
            this.ajaxLoading = false;
            console.error('My Current Cars:', error);
        },

        hidePxInfoBlock() {
            this.showPxInfoBlock = false;
        },

        /**
         * Unselect all filter options and dispatch event to refresh filters
         *
         * @param removedFilter
         */
        removeFilter(removedFilter) {
            this.carFilters.forEach(filter => {
                if (filter.code === removedFilter.code) {
                    filter.hasSelected = false;

                    filter.options.map(option => {
                        option.state = false;
                    });
                }
            });

            this.$dispatch('CarFinder::updateFilters');
        },

        closeTradeInModal() {
            this.$refs.tradeInModal.show = false;
        }
    },

    events: {
        'FinanceFilter::openTradeInModal'() {
            this.openTradeInModal();
        },

        'FinanceFilter::openTradeInModalPxActive'(isExpired) {
            this.openTradeInModalPxActive(isExpired);
        },

        'FinanceFilter::pif-change'() {
            this.payInFull = this.pifSlider.active;
            this.productGrid.financeParams[this.section].payinfull = this.pifSlider.active;
            this.areFinanceSlidersChanged = true;
        },

        'FinanceFilter::pif-update'() {
            if (this.areFinanceSlidersChanged && !this.showLoading) {
                this.acceptFilter();
            }
        },

        'FinanceFilter::mps-change'(data) {
            this.monthlyPay = data;
            this.productGrid.financeParams[this.section].monthlypay = data;
            this.areFinanceSlidersChanged = true;
            this.mpsSlider.changeActive(data);
        },

        'FinanceFilter::mps-update'(data) {
            if (this.areFinanceSlidersChanged && !this.showLoading) {
                this.acceptFilter();
            }
        },

        'FinanceFilterOverlay::continueWithPayment'(groupId, paymentData) {
            for (const [key, value] of Object.entries(paymentData)) {
                this.financeGroupsParams[this.section][key] = value;

                switch (key) {
                    case 'payinfull':
                        this.payInFull = value;
                        this.productGrid.financeParams[this.section].payinfull = value;
                        this.pifSlider.changeActive(value);
                        break;
                    case 'mileage':
                        this.annualMileage = value;
                        break;
                    case 'balloonPercentage':
                        this.balloonPercentage = value;
                        break;
                    case 'deposit':
                        this.initialPay = value;
                        break;
                    case 'term':
                        this.payOver = value;
                        break;
                    default:
                        break;
                }
            }

            this.acceptFilter();
            this.closeMoreFiltersOverlay();
        },

        'FinanceFilterOverlay::closeMoreFiltersOverlay'() {
            this.closeMoreFiltersOverlay();
        },

        'FinanceFilterOverlay::openMoreFiltersOverlay'() {
            this.openMoreFiltersOverlay();
        },

        'FinanceFilter::closeTradeInModal'() {
            this.closeTradeInModal();
        }
    },

    watch: {
        'section'(newValue) {
            this.updateFinanceFilters(newValue);

            if (this.payInFullPayment) {
                this.isPayInFull = false;
                this.payInFullPayment.forEach(payment => {
                    if (payment.group_id === this.section) {
                        this.isPayInFull = true;
                    }
                });
            }
        }
    },

    beforeCompile() {
        this.currentFinanceSteps = this.financeSteps[this.financeActivePayment.group_id];
        this.financeParams = this.financeGroupsParams[this.financeActivePayment.group_id];
        this.monthlyPay = parseInt(this.financeParams.monthlypay);
        this.initialPay = parseInt(this.financeParams.deposit);
        this.payOver = parseInt(this.financeParams.term);
        this.acceptedFinance = this.financeParams.acceptedFinance;
        this.annualMileage = parseInt(this.financeParams.mileage);
        this.depositMultiple = this.financeParams.depositMultiple;
        this.maintenance = this.financeParams.maintenance;
        this.payInFull = this.financeParams.payinfull;
        this.payInFullInputs = this.financeParams.payinfull;
    },

    ready() {
        this.validateParams();

        if (typeof this.financeParams.acceptedFinance === 'undefined') {
            this.resetAcceptedFinance();
        }

        EventsBus.$on('PartExchange::removedPx', () => {
            this.applyRemovedPx();
        });

        EventsBus.$on('FinanceFilter::applyFilter', () => {
            this.applyFilter();
        });

        EventsBus.$on('FinanceFilter::openFromTradeInBlock', (data) => {
            this.applyBlock(data);
        });

        EventsBus.$on('FinanceFilter::removePxFromTradeInBlock', () => {
            this.removePX();
        });

        EventsBus.$on('ProductGrid::productsUpdated', () => {
            this.$store.commit('setIsPayInFull', this.isPayInFullPaymentByGroupId(this.activePayment.group_id));
        });

        this.$nextTick(() => {
            this.areFinanceSlidersChanged = false;
        });

        // Set initial value as current active payment group_id
        this.$store.commit('setFinanceGroupId', this.financeActivePayment.group_id);
        this.$store.commit('setIsPayInFull', this.isPayInFullPaymentByGroupId(this.activePayment.group_id));

        // Events
        EventsBus.$on('FinanceFilter::resetFilters', this.resetFilters);

        const instalment = this.financeOptions.items.find(item => item.group_title.includes('Instalment Sale'));

        if (instalment) {
            this.instalmentID = instalment.group_id;
        }
        // for the AB test is the active value defaulted to max? if so hide the filter, set the hideABTest
        // broke it up into two statements otherwise it gets unreadable
        const monthlyPayValues = Array.isArray(this.currentFinanceSteps.monthlypay)
            ? this.currentFinanceSteps.monthlypay.map(obj => obj.id)
            : [];
        this.showMonthlyPaymentSlider = monthlyPayValues.reduce((x, y) => x > y ? x : y) !== this.financeParams.monthlypay;

        if (sessionStorage.getItem('trade_in_overlay') === 'true') {
            this.openTradeInModal();
            sessionStorage.removeItem('trade_in_overlay');
            this.$broadcast('PartExchange::moveToValuation');
        }
    }
});
</script>
