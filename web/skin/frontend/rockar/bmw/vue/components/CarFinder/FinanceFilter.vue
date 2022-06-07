<template>
    <div id="finance-filter" v-show="isFinanceStep">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <div class="row">
            <div class="col-8 col-md-12">
                <app-finance-options
                        :finance-options="financeOptions"
                        :active-finance-group="defaultPayment.group_id"
                ></app-finance-options>
            </div>
            <div class="col-4 col-md-12 finance-filter-sliders" v-show="!isPayInFull">
                <div class="finance-filter-slider-wrapper deposit-filter" v-show="!isHirePaymentByGroupId(section)">
                    <div class="finance-filter-slider-wrapper">
                        <p v-show="abTestHelper" class="field-title">{{ 'Monthly Payment:' | translate }} </p>
                        <div v-show="abTestHelper" class="finance-filter-slider" data-id="monthlypay-filter">
                            <app-range-slider
                                :use-id="true"
                                :options="currentFinanceSteps.monthlypay"
                                :active="financeParams.monthlypay"
                                :active-on-slide="true"
                                custom-event-slide="FinanceFilter::mps-change"
                                v-ref:monthly-pay-slider>
                            </app-range-slider>
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                    <input
                                        type="text"
                                        v-model="monthlyPay | numberCurrencyFormat"
                                        class="keyboard-numbers"
                                        size="10"
                                        id="monthly-pay"
                                        maxlength="10"
                                        @change="updateFields($event, 'monthlypay', 'monthlyPay')"
                                    >
                                </div>
                            </div>
                        </div>
                        <p class="field-title">{{ 'Deposit:' | translate }}</p>
                        <div class="finance-filter-slider" data-id="deposit-filter">
                            <app-range-slider :use-id="true" :options="currentFinanceSteps.deposit" :active="financeParams.deposit" :active-on-slide="true" custom-event-slide="FinanceFilter::ip-change" v-ref:initial-pay-slider></app-range-slider>
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                    <input
                                        type="text"
                                        v-model="initialPay | numberCurrencyFormat"
                                        class="keyboard-numbers"
                                        size="10"
                                        id="initial-payment"
                                        maxlength="10"
                                        @change="updateFields($event, 'deposit', 'initialPay')"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="finance-filter-slider-wrapper deposit-multiple-filter" v-show="isHirePaymentByGroupId(section)">
                        <p class="field-title">{{ 'Deposit multiple:' | translate }}</p>
                        <div class="finance-filter-slider" data-id="deposit-multiple-filter">
                            <app-range-slider
                                :use-id="true"
                                :options="currentFinanceSteps.depositMultiple"
                                :active="financeParams.depositMultiple"
                                :active-on-slide="true"
                                custom-event-slide="FinanceFilter::dm-change"
                                v-ref:deposit-multiple-slider :display-steps="true"
                            ></app-range-slider>
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                     <div class="finance-filter-input">
                                        <input
                                            type="text"
                                            v-model="depositMultiple | numberDepositMilesFormat"
                                            class="keyboard-numbers"
                                            id="deposit-multiple"
                                            disabled
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <div class="finance-filter-slider-wrapper mileage-filter" v-show="!isInstalmentSaleID(section)">
                    <p class="field-title">{{ 'Contract End Mileage:' | translate }}</p>
                    <div class="finance-filter-slider " data-id="mileage-filter">
                        <app-range-slider
                            :use-id="true"
                            :options="currentFinanceSteps.mileage"
                            :active="financeParams.mileage"
                            :active-on-slide="true"
                            custom-event-slide="FinanceFilter::am-change2"
                            v-ref:mileage-slider>
                        </app-range-slider>
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                    <div class="finance-filter-input">
                                        <input
                                            id="end-mileage-value"
                                            type="text"
                                            v-model="annualMileage | numberKilometerFormat"
                                            class="keyboard-numbers"
                                            @change="updateFields($event, 'mileage', 'annualMileage')"
                                            disabled
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="finance-filter-slider-wrapper mileage-filter" v-show="isInstalmentSaleID(section)">
                        <p class="field-title">{{ 'Balloon Percentage:' | translate }}</p>
                        <div class="finance-filter-slider " data-id="percentage-filter">
                            <app-range-slider
                                :use-id="true"
                                :options="currentFinanceSteps.balloonPercentage"
                                :active="financeParams.balloonPercentage"
                                :active-on-slide="true"
                                custom-event-slide="FinanceFilter::bp-change"
                                v-ref:balloon-percentage-slider>
                            </app-range-slider>
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                    <div class="finance-filter-input">
                                        <input
                                            id="balloon-percentage-value"
                                            type="text"
                                            class="keyboard-numbers"
                                            v-model="balloonPercentage | numberPercentageFormat"
                                            disabled
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="finance-filter-slider-wrapper term-filter">
                        <p class="field-title">{{ 'Number of Instalments:' | translate }} </p>
                        <div class="finance-filter-slider" data-id="term-filter">
                            <app-range-slider
                                :use-id="true"
                                :options="currentFinanceSteps.term"
                                :active="financeParams.term"
                                :active-on-slide="true"
                                custom-event-slide="FinanceFilter::po-change"
                                v-ref:pay-over-slider
                            ></app-range-slider>
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                     <div class="finance-filter-input">
                                        <input
                                            type="text"
                                            v-model="payOver | numberMonthsFormat"
                                            class="keyboard-numbers"
                                            id="pay-over-month"
                                            @change="updateFields($event, 'term', 'payOver')"
                                            disabled
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="finance-filter-slider-wrapper maintenance-filter-wrapper" v-show="isHirePaymentByGroupId(section)">
                    <div class="col-6">
                        <input id="filter-maintenance" type="checkbox" v-model="maintenance" :true-value="1" :false-value="0" />
                        <label for="filter-maintenance"><span></span>{{ 'Add maintenance' | translate }}</label>
                    </div>
                </div>
            </div>

            <div class="col-4 col-md-12 finance-filter-mobile" v-show="!isPayInFull">
                <div class="finance-filter-dropdown-input-wrapper">
                    <label><p>{{ 'Monthly Payment:' | translate }}</p></label>
                    <div class="option-field" v-bind:class="{ selected: isSelected }">
                        <input
                            class="finance-options-input keyboard-numbers"
                            type="text"
                            v-model="monthlyPay | numberCurrencyFormat"
                            id="monthly-pay-mobile"
                            @change="updateFields($event, 'monthlypay', 'monthlyPay')"
                            @blur="makeInputSelected()"
                        >
                    </div>
                </div>
                <div class="finance-filter-dropdown-input-wrapper" v-show="!isHirePaymentByGroupId(section)">
                    <label><p>{{ 'Deposit:' | translate }}</p></label>
                    <div class="option-field" v-bind:class="{ selected: isSelected }">
                        <input
                            class="finance-options-input keyboard-numbers"
                            type="text"
                            v-model="initialPay | numberCurrencyFormat"
                            @change="updateFields($event, 'deposit', 'initialPay')"
                        >
                    </div>
                </div>
                <div class="finance-filter-dropdown-input-wrapper" v-show="isHirePaymentByGroupId(section)">
                    <label><p>{{ 'Deposit multiple:' | translate }}</p></label>
                    <app-select
                        @select="dropdownSelectDepositMultiple"
                        custom-event='FinanceFilter::selectDepositMultiple'
                        id='selectDepositMultiple'
                        :options="financeDepositMultipleOptionsMobile"
                        class="grey-dropdown"
                    ></app-select>
                </div>

                <div class="finance-filter-dropdown-input-wrapper" v-show="!isInstalmentSaleID(section)">
                    <label><p>{{ 'Contract End Mileage:' | translate }}</p></label>
                    <div class="option-field">
                        <app-select
                            @select="dropdownSelectAnnualMileage"
                            :options="annualMileageMultipleOptionsMobile"
                            :init-selected="annualMileageSelectIndex"
                            class="grey-dropdown"
                        ></app-select>
                    </div>
                </div>

                <div class="finance-filter-dropdown-input-wrapper" v-show="isInstalmentSaleID(section)">
                    <label><p>{{ 'Balloon Percentage:' | translate }}</p></label>
                    <div class="option-field">
                        <app-select
                            @select="dropdownSelectBalloonPercentage"
                            :options="financeBalloonPercentageOptions"
                            :init-selected="balloonPercentageSelectedIndex"
                            class="grey-dropdown"
                        ></app-select>
                    </div>
                </div>

                <div class="finance-filter-dropdown-input-wrapper">
                    <label><p>{{ 'Number of Instalments:' | translate }}</p></label>
                    <app-select
                        @select="dropdownSelectActiveOption"
                        :options="financeMonthsSliderOptionsMobile"
                        :init-selected="financeMonthsSliderActiveSelectIndex"
                        class="grey-dropdown"
                    ></app-select>
                </div>

                <div class="finance-filter-dropdown-input-wrapper maintenance-filter-wrapper" v-show="isHirePaymentByGroupId(section)">
                    <div class="col-12 align-center">
                        <input id="filter-maintenance-mobile" type="checkbox" v-model="maintenance" :true-value="1" :false-value="0" />
                        <label for="filter-maintenance-mobile"><span></span>{{ 'Add maintenance' | translate }}</label>
                    </div>
                </div>
            </div>

            <div class="col-4 col-md-12 finance-filter-mobile" v-show="isPayInFull">
                <div class="finance-filter-dropdown-input-wrapper">
                    <label><p>{{ 'Total Budget:' | translate }}</p></label>
                    <div class="finance-filter-slider">
                        <div class="finance-filter-slider-input input-one">
                            <div class="finance-filter-slider-input-wrapper">
                                <div class="option-field">
                                    <input class="finance-options-input"
                                           @change="areFinanceSlidersChanged = true"
                                           type="text"
                                           :value="payInFull[0] | numberInputFormat"
                                           v-model="payInFullInputs[0] | numberInputFormat"
                                           class="keyboard-numbers"
                                           @blur="changePayInFull()"
                                           @keyup.enter="changePayInFull()"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="finance-filter-slider-input input-two">
                            <div class="finance-filter-slider-input-wrapper">
                                <div class="option-field">
                                    <input class="finance-options-input"
                                           @change="areFinanceSlidersChanged = true"
                                           type="text" :value="payInFull[1] | numberInputFormat"
                                           v-model="payInFullInputs[1] | numberInputFormat"
                                           class="keyboard-numbers"
                                           @blur="changePayInFull()"
                                           @keyup.enter="changePayInFull()"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-4 col-md-12 finance-filter-sliders" v-show="isPayInFull">
                <div class="finance-filter-slider-wrapper">
                    <p class="field-title">{{ 'What is your total budget?' | translate }}</p>
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
                                v-ref:pay-in-full-slider
                        ></app-range-slider>
                    </div>
                    <div class="finance-filter-slider small three-action">
                        <div class="finance-filter-slider-input input-one">
                            <div class="finance-filter-slider-input-wrapper">
                                <input
                                    @change="areFinanceSlidersChanged = true"
                                    type="text"
                                    :value="payInFull[0] | numberInputFormat"
                                    v-model="payInFullInputs[0] | numberInputFormat"
                                    size="10"
                                    class="keyboard-numbers"
                                    maxlength="10"
                                    @blur="changePayInFull()"
                                    @keyup.enter="changePayInFull()"
                                >
                            </div>
                        </div>
                        <div class="finance-filter-slider-input input-two">
                            <div class="finance-filter-slider-input-wrapper">
                                <input
                                    @change="areFinanceSlidersChanged = true"
                                    type="text"
                                    :value="payInFull[1] | numberInputFormat"
                                    v-model="payInFullInputs[1] | numberInputFormat"
                                    size="10"
                                    class="keyboard-numbers"
                                    maxlength="10"
                                    @blur="changePayInFull()"
                                    @keyup.enter="changePayInFull()"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="finance-option-description">
            <strong class="finance-option-description-header" v-if="selectedFinanceGroupData">{{ selectedFinanceGroupData.group_title }}</strong>
            <p class="finance-option-description-text" v-if="selectedFinanceGroupData" v-html="selectedFinanceGroupData.group_description"></p>
        </div>

        <app-modal :show.sync="financeConfirmationPopup" :show-close="true" class-name="finance-confirmation-popup">
            <div slot="content">
                <p class="modal-header">
                    <span>{{ 'Are you sure you want to continue?' | translate }}</span>
                </p>
                <div class="main-content">
                    <slot name="finance_filter_confirmation_popup"></slot>
                </div>
                <div class="row align-right">
                    <button class="button button-narrow button-grey" @click="financeConfirmationPopup = !financeConfirmationPopup">{{ 'Go back' | translate }}</button>
                    <button class="button button-narrow popup-continue" @click="acceptFilter()">{{ 'Continue' | translate }}</button>
                </div>
            </div>
        </app-modal>

        <app-modal :show.sync="helpOverlay" v-if="helpOverlay" width="80%">
            <div slot="content">
                {{{ helpOverlayContent | cmsBlock }}}
            </div>
        </app-modal>
    </div>
</template>

<script>
    import appFinanceFilter from 'core/components/CarFinder/FinanceFilter';
    import appFinanceOptions from 'bmw/components/CarFinder/FinanceOptions';
    import numeral from 'numeral';

    export default appFinanceFilter.extend({
        components: {
            appFinanceOptions
        },

        data: () => ({
            balloonPercentage: 0,
            isPayInFull: false,
            instalmentID: 0,
            showMonthlyPaymentSlider: false,
            previousSettings: '',
            previousPayInFullSettings: ''
        }),

        filters: {
            numberKilometerFormat: {
                read(number) {
                    return `${numeral(Math.floor(number)).format('0,0')} ${this.translateString('km')}`;
                },
                write(number) {
                    return Math.floor(numeral(number).value());
                }
            },

            numberPercentageFormat: {
                read(number) {
                    return `${numeral(Math.floor(number)).format('0')} ${this.translateString('%')}`;
                },
                write(number) {
                    return Math.floor(numeral(number).value());
                }
            }
        },

        computed: {
            /**
             * this serves as a reference to financeParams
             * @return {Boolean}
             */
            acceptedFinance() {
                return this.financeParams.acceptedFinance;
            },

            bpSlider() {
                return this.$refs.balloonPercentageSlider;
            },

            storeBalloonPercentage() {
                return this.$store.state.general.balloonPercentage;
            },

            initialPaymentAmount() {
                return this.isPayInFull ? 0 : this.initialPay;
            },

            /**
             * get the balloon percentage from the store state
             *
             * @return {(String|undefined)}
             */
            deepLinkBalloonPercentage() {
                return this.$store.state.general.deepLinkRequestParams.balloonPercentage;
            },

            /**
             * Finance Balloon Percentage options for dropdowns
             * @return {Object[]} [{ value, title}, ...]
             */
            financeBalloonPercentageOptions() {
                return this.currentFinanceSteps.balloonPercentage.map(option => ({
                    // FIXME: No value is passed through, only an id which happens to correspond
                    // to the percentage value. A value for the id should be passed through
                    // explicitly.
                    // For now id will be used for value.
                    value: option.id,
                    title: numeral(Math.floor(option.id) / 100).format('0%')
                }));
            },

            /**
             * Get the index of the balloon percentage option that is selected
             * @return {Number} index of that balloon percentage option, -1 if not found
             */
            balloonPercentageSelectedIndex() {
                return this.financeBalloonPercentageOptions.findIndex(option =>
                    option.value === this.financeParams.balloonPercentage
                );
            },

            annualMileageMultipleOptionsMobile() {
                const optionsSelect = [];
                this.currentFinanceSteps.mileage.forEach(option => {
                    const number = Math.floor(option.id);
                    optionsSelect.push({
                        title: numeral(number).format('0,0') + this.translateString(' KM'),
                        value: option.id
                    });
                });

                return optionsSelect;
            },

            /**
             * property hides/shows the container for the monthly payment slider.
             * @return {String}, the inline styling of the container.
             */
            abTestHelper() {
                return this.showMonthlyPaymentSlider;
            }
        },

        methods: {
            /**
             * This takes in the active group ID and checks whether
             * it is the same as the instalment sale ID, if it is the same a true value is returned
             * @param {Integer} groupId - The ID given to each finance option
             * @return {Boolean}
             */
            isInstalmentSaleID(groupId) {
                return parseInt(this.instalmentID) === parseInt(groupId);
            },

            updateAccordionTitle() {
                // Check for parent before continuing
                // FIXME: Don't directly reference the parent
                if (!this.$parent) {
                    return;
                }

                // FIXME: Don't directly grab a different components data
                let title = this.$parent.title;

                if (this.payInFullPayment.find(
                    payment => payment.group_id === this.activePayment.group_id
                )) {
                    title = `I’m Paying cash up to ${currencySymbol}${numeral(this.payInFull[1]).format('0,0')}`;
                } else if (this.isHirePaymentByGroupId(this.activePayment.group_id)) {
                    title = `Your Personal Quote based on: ${
                            this.depositMultiple
                        }x monthly payment; ${
                            numeral(this.annualMileage).format('0,0')
                        } km per annum; ${this.payOver} months`;
                } else {
                    title = `Your Personal Quote based on: ${
                            currencySymbol}${numeral(this.initialPay).format('0,0')
                        } initial payment; ${
                            numeral(this.annualMileage).format('0,0')
                        } km per annum; ${this.payOver} months`;
                }

                // FIXME: Don't directly edit the parent data
                this.$parent.title = title;
            },

            setupFilterCollection() {
                // FIXME: this.section is required twice in this object
                // under the keys `method` and `group_id`
                const filterData = {
                    method: this.section,
                    group_id: this.section,
                    monthlypay: this.monthlyPay,
                    deposit: this.initialPay,
                    term: this.payOver,
                    balloonPercentage: this.balloonPercentage,
                    mileage: this.annualMileage,
                    payinfull: this.payInFull,
                    depositMultiple: this.depositMultiple,
                    maintenance: this.maintenance,
                    acceptedFinance: this.acceptedFinance
                };

                // FIXME: Don't directly read another components data
                if (this.productGrid && this.productGrid.CarFilter && this.productGrid.CarFilter.financeFilters) {
                    // FIXME: Don't directly edit another components data
                    this.productGrid.CarFilter.financeFilters = filterData;
                }

                // Ensure we return the final data here so that this method can be overwritten in another component
                return filterData;
            },

            updateFinanceFilters(groupId) {
                if (!this.isHirePaymentByGroupId(groupId)) {
                    this.maintenance = 0;
                }

                this.currentFinanceSteps = this.financeSteps[groupId];
                this.financeParams = JSON.parse(JSON.stringify(this.financeGroupsParams[groupId]));

                this.poSlider.changeSteps(this.currentFinanceSteps.term);
                const term = parseInt(this.financeParams.term);
                this.poSlider.changeActive(term);

                // FIXME: Don't use another component to emit an event
                this.$parent.$broadcast('FinanceFilterMenu::po-reload', term);
                this.payOver = term;

                this.amSlider.changeSteps(this.currentFinanceSteps.mileage);
                const mileage = parseInt(this.financeParams.mileage);
                this.amSlider.changeActive(mileage);

                // FIXME: Don't use another component to emit an event
                this.$parent.$broadcast('FinanceFilterMenu::am-reload', mileage);
                this.annualMileage = mileage;

                this.mpsSlider.changeSteps(this.currentFinanceSteps.monthlypay);
                const monthlypay = parseInt(this.financeParams.monthlypay);
                this.mpsSlider.changeActive(monthlypay);

                // FIXME: Don't use another component to emit an event
                this.$parent.$broadcast('FinanceFilterMenu::mps-reload', monthlypay);
                this.monthlyPay = monthlypay;

                this.ipSlider.changeSteps(this.currentFinanceSteps.deposit);
                const deposit = parseInt(this.financeParams.deposit);
                this.ipSlider.changeActive(deposit);

                // FIXME: Don't use another component to emit an event
                this.$parent.$broadcast('FinanceFilterMenu::ip-reload', deposit);
                this.initialPay = deposit;

                this.productGrid.financeParams = this.financeGroupsParams;
                this.bpSlider.changeSteps(this.currentFinanceSteps.balloonPercentage);
                const balloonPercentage = parseInt(this.financeParams.balloonPercentage);
                this.bpSlider.changeActive(balloonPercentage);
                this.balloonPercentage = balloonPercentage;
            },

            updateFields(event, param, valueElem) {
                this.$super(appFinanceFilter, 'updateFields', event, param, valueElem);

                this.bpSlider.changeActive(this.balloonPercentage);
                this.financeGroupsParams[this.section].balloonPercentage = this.balloonPercentage;

                this.areFinanceSlidersChanged = true;
            },

            /**
             * Method to handle selecting an option on the balloon
             * percentage dropdown list
             * @param {Object} option
             */
            dropdownSelectBalloonPercentage(option) {
                this.selectBalloonPercentage({
                    id: option.value
                });
                this.$refs.balloonPercentageSlider.changeActive(option);
            },

            /**
             * Method to handle selecting an option for balloon percentage
             * @param {Object} option
             */
            selectBalloonPercentage(option) {
                this.$dispatch('FinanceFilter::bp-change', option.id);
            },

            /**
             * Override default2 changePayInFull method.
             * We need to change steps on slider in order to pull in correct min, max, step for slider
             */
            changePayInFull() {
                this.$super(appFinanceFilter, 'changePayInFull');
                this.$nextTick(() => this.pifSlider.changeSteps(this.currentFinanceSteps.payinfull.step));
            },

            /**
             * Get a string that represents the current finance settings
             *
             * @returns {string}
             */
            getSettingsString() {
                return [
                    this.payOver.toString(),
                    this.annualMileage.toString(),
                    this.initialPay.toString(),
                    this.monthlyPay.toString(),
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
                    this.$super(appFinanceFilter, 'applyFilter');
                } else {
                    this.$dispatch('Main::closeFilter');
                }
            },

            /**
             * Clear previous settings so next time applyFilter is
             * called, it will reload the filters
             */
            clearPreviousSettings() {
                this.previousSettings = '';
                this.previousPayInFullSettings = '';
            }
        },

        events: {
            'FinanceFilter::bp-change'(data) {
                this.balloonPercentage = data;
                this.productGrid.financeParams[this.section].balloonPercentage = data;
                this.areFinanceSlidersChanged = true;
                this.bpSlider.changeActive(data);
                this.updateFields(null, 'balloonPercentage', 'balloonPercentage');
                this.$store.commit('setBalloonPercentage', data);
            }
        },

        watch: {
            'section'(newValue) {
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
            this.balloonPercentage = this.financeParams.balloonPercentage;
        },

        ready() {
            if (this.storeBalloonPercentage || this.deepLinkBalloonPercentage) {
                const balloonPercentage = parseInt(this.storeBalloonPercentage || this.deepLinkBalloonPercentage);

                this.balloonPercentage = balloonPercentage;
                this.financeParams.balloonPercentage = balloonPercentage;

                this.bpSlider.changeActive(this.storeBalloonPercentage || this.deepLinkBalloonPercentage);

                // need to run this in order to update the finances correctly further in the journey when deep linking
                this.applyFilter();
            }

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
        }
    });
</script>
