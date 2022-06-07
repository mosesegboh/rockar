<template>
    <div>
        <div class="car-filter-item finance-filter">
            <input
                type="radio"
                name="car-filter-open"
                class="car-filter-open"
                id="input-finance"
                :checked="openFilter === 'finance-filter'"
                value="finance-filter"
                @click="toggleEditYourBudgetBox"
            />
            <label for="input-finance" class="car-filter-title">
                <div>{{ 'Edit Your Budget' | translate }}</div>
            </label>
            <div class="car-filter-blocks">
                <div class="finance-header">{{ 'Edit Your Budget' | translate }}</div>
                <div class="select-wrapper" :class="{ selected: activeFinanceGroupId > 0 }">
                    <!-- Need to force init-selected value as string, otherwise select won't properly select initial value -->
                    <app-select
                        @select="selectFinance"
                        :options="financeGroupsMobile"
                        :init-selected="activeFinanceGroupId.toString()"
                    ></app-select>
                </div>
                <div class="finance-filters-wrapper" v-show="!isPayInFull">
                    <div class="finance-filter-slider-wrapper">
                        <p>{{ 'Monthly Payment:' | translate }}</p>
                        <div class="finance-filter-slider" data-id="monthlypay-filter">
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                    <input
                                        type="text"
                                        v-model="FinanceFilter.monthlyPay | numberFormat"
                                        size="7"
                                        class="keyboard-numbers"
                                        maxlength="7"
                                        @change="updateFinanceValue($event, 'monthlypay', 'monthlyPay')"
                                    />
                                </div>
                            </div>
                            <app-range-slider
                                v-ref:monthly-pay-slider
                                :use-id="true"
                                :options="FinanceFilter.currentFinanceSteps.monthlypay"
                                :active="FinanceFilter.financeParams.monthlypay"
                                :active-on-slide="true"
                                custom-event="FinanceFilterMenu::mps-change"
                                custom-event-slide="FinanceFilterMenu::mps-update"
                            ></app-range-slider>
                        </div>
                    </div>

                    <div class="finance-filter-slider-wrapper" v-show="!isHirePaymentByGroupId">
                        <p>{{ 'Initial Deposit:' | translate }}</p>
                        <div class="finance-filter-slider" data-id="deposit-filter">
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                    <input
                                        type="text"
                                        v-model="FinanceFilter.initialPay | numberFormat"
                                        class="keyboard-numbers"
                                        @change="updateFinanceValue($event, 'deposit', 'initialPay')"
                                    />
                                </div>
                            </div>
                            <app-range-slider
                                v-ref:initial-pay-slider
                                :use-id="true"
                                :options="FinanceFilter.currentFinanceSteps.deposit"
                                :active="FinanceFilter.financeParams.deposit"
                                :active-on-slide="true"
                                custom-event="FinanceFilterMenu::ip-change"
                                custom-event-slide="FinanceFilterMenu::ip-update"
                            ></app-range-slider>
                        </div>
                    </div>

                    <div class="finance-filter-slider-wrapper" v-show="isHirePaymentByGroupId">
                        <p>{{ 'Deposit multiple:' | translate }}</p>
                        <div class="finance-filter-slider" data-id="deposit-multiple-filter">
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                    <input
                                        type="text"
                                        v-model="FinanceFilter.depositMultiple"
                                        size="7"
                                        class="keyboard-numbers"
                                        maxlength="7"
                                        @change="updateFinanceValue($event, 'deposit', 'initialPay')"
                                        disabled
                                    />
                                </div>
                            </div>
                            <app-range-slider
                                :use-id="true"
                                :options="FinanceFilter.currentFinanceSteps.depositMultiple"
                                :active="FinanceFilter.financeParams.depositMultiple"
                                :active-on-slide="true"
                                custom-event="FinanceFilterMenu::dm-change"
                                custom-event-slide="FinanceFilterMenu::dm-update"
                                :display-steps="true"
                                v-ref:deposit-multiple-slider
                            ></app-range-slider>
                        </div>
                    </div>

                    <div class="finance-filter-slider-wrapper" v-show="!isInstalmentSaleID(activeFinanceGroupId)">
                        <p>{{ 'Contract End Mileage:' | translate }}</p>
                        <div class="finance-filter-slider" data-id="mileage-filter">
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                    <input
                                        type="text"
                                        v-model="FinanceFilter.annualMileage | numberMilesFormat"
                                        size="7"
                                        class="keyboard-numbers"
                                        maxlength="7"
                                        disabled
                                    />
                                </div>
                            </div>
                            <app-range-slider
                                v-ref:mileage-slider
                                :use-id="true"
                                :options="FinanceFilter.currentFinanceSteps.mileage"
                                :active="FinanceFilter.financeParams.mileage"
                                :active-on-slide="true"
                                custom-event="FinanceFilterMenu::am-change"
                                custom-event-slide="FinanceFilterMenu::am-update"
                            ></app-range-slider>
                        </div>
                    </div>

                    <div class="finance-filter-slider-wrapper" v-show="isInstalmentSaleID(activeFinanceGroupId)">
                        <p>{{ 'Balloon Percentage:' | translate }}</p>
                        <div class="finance-filter-slider" data-id="percentage-filter">
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                    <input
                                        type="text"
                                        v-model="FinanceFilter.financeParams.balloonPercentage | numberPercentageFormat"
                                        value="FinanceFilter.balloonPercentage"
                                        size="7"
                                        maxlength="7"
                                        class="keyboard-numbers"
                                        disabled
                                    />
                                </div>
                            </div>
                            <app-range-slider
                                v-ref:balloon-percentage-slider
                                :use-id="true"
                                :options="FinanceFilter.currentFinanceSteps.balloonPercentage"
                                :active="FinanceFilter.financeParams.balloonPercentage"
                                :active-on-slide="true"
                                :car-finder-results="true"
                                custom-event="FinanceFilter::bp-change"
                                custom-event-slide="FinanceFilterMenu::bp-update"
                            ></app-range-slider>
                        </div>
                    </div>

                    <div class="finance-filter-slider-wrapper">
                        <p>{{ "Number of Instalments:" | translate }}</p>
                        <div class="finance-filter-slider" data-id="term-filter">
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                    <input
                                        type="text"
                                        v-model="FinanceFilter.payOver | numberMonthsFormat"
                                        size="7"
                                        class="keyboard-numbers"
                                        maxlength="7"
                                        disabled
                                    />
                                </div>
                            </div>
                            <app-range-slider
                                v-ref:pay-over-slider
                                :use-id="true"
                                :options="FinanceFilter.currentFinanceSteps.term"
                                :active="FinanceFilter.financeParams.term"
                                :active-on-slide="true"
                                :car-finder-results="true"
                                custom-event="FinanceFilterMenu::po-change"
                                custom-event-slide="FinanceFilterMenu::po-update"
                            ></app-range-slider>
                        </div>
                    </div>
                    <div
                        class="finance-filter-slider-wrapper maintenance-filter-wrapper"
                        v-show="isHirePaymentByGroupId"
                    >
                        <input
                            id="filter-maintenance"
                            type="checkbox"
                            v-model="maintenance"
                            :true-value="1"
                            :false-value="0"
                        />
                        <label for="filter-maintenance">
                            <span>{{ 'Add maintenance' | translate }}</span>
                        </label>
                    </div>
                </div>
                <div class="finance-filters-wrapper" v-show="isPayInFull">
                    <div class="finance-filter-slider-wrapper">
                        <p>{{ 'What is your total budget?' | translate }}</p>
                        <div class="finance-filter-slider with-two-inputs" data-id="pay-in-full-filter">
                            <app-range-slider
                                :use-id="false"
                                v-show="typeof FinanceFilter.currentFinanceSteps.payinfull !== 'undefined'"
                                :active="[FinanceFilter.payInFull[0], FinanceFilter.payInFull[1]]"
                                :active-on-slide="true"
                                :range="true"
                                :min="FinanceFilter.currentFinanceSteps.payinfull.min"
                                :max="FinanceFilter.currentFinanceSteps.payinfull.max"
                                :step="FinanceFilter.currentFinanceSteps.payinfull.step"
                                custom-event="FinanceFilterMenu::pif-change"
                                custom-event-slide="FinanceFilterMenu::pif-update"
                                v-ref:pay-in-full-slider
                            ></app-range-slider>
                            <div class="finance-filter-slider-input">
                                <div class="finance-filter-slider-input-wrapper">
                                    <input
                                        type="text"
                                        :value="FinanceFilter.payInFull[0] | numberFormat"
                                        v-model="FinanceFilter.payInFullInputs[0] | numberFormat"
                                        size="8"
                                        class="keyboard-numbers"
                                        maxlength="10"
                                        @blur="changePayInFull()"
                                        @keyup.enter="changePayInFull()"
                                    />
                                    <input
                                        type="text"
                                        :value="FinanceFilter.payInFull[1] | numberFormat"
                                        v-model="FinanceFilter.payInFullInputs[1] | numberFormat"
                                        size="8"
                                        class="keyboard-numbers"
                                        maxlength="10"
                                        @blur="changePayInFull()"
                                        @keyup.enter="changePayInFull()"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button type="button" @click="resetFinanceFilter()" class="clear-button">
                        {{ 'Clear' | translate }}
                    </button>
                    <button type="button" @click="closeFinanceFilterPopup()" class="go-button">
                        {{ 'Go' | translate }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import coreFinanceFilterMenu from 'core/components/CarFinder/FinanceFilterMenu';
    import translateString from 'core/filters/Translate';
    import numeral from 'numeral';

    export default coreFinanceFilterMenu.extend({
        props: {
            openFilter: {
                required: false,
                type: String,
                default: ''
            }
        },

        data() {
            return {
                /**
                 * @var {number} instalmentID Instalment Sale Group id
                 */
                instalmentID: 0
            };
        },

        computed: {
            bpSlider() {
                return this.$refs.balloonPercentageSlider;
            },

            isPayInFull() {
                return this.payInFullPayment.some(payment => payment.group_id === this.activeFinanceGroupId);
            },

            /**
             * Target the payInFullSlider in the component
             */
            payInFullSlider() {
                return this.$refs.payInFullSlider;
            }
        },

        filters: {
            numberMilesFormat: {
                read(number) {
                    return numeral(Math.floor(number)).format('0,0') + this.translateString(' KM');
                },

                write(number) {
                    return Math.floor(numeral(number).value());
                }
            },

            numberPercentageFormat: {
                read(number) {
                    return numeral(Math.floor(number)).format('0') + this.translateString(' %');
                },

                write(number) {
                    return Math.floor(numeral(number).value());
                }
            }
        },

        methods: {
            /**
             * Override of default2 selectFinance.
             * When finance filter is opened rerun method to pull in correct min, max, step
             */
            selectFinance(data) {
                this.$super(coreFinanceFilterMenu, 'selectFinance', data);
                this.$nextTick(() => this.changePayInFull());
            },

            /**
             * Returns the $store.state.balloonPercentage or FinanceFilter.financeParams.balloonPercentage
             */
            storeBalloonPercentage() {
                return this.$store.state.general.balloonPercentage !== undefined
                    ? this.$store.state.general.balloonPercentage
                    : this.FinanceFilter.financeParams.balloonPercentage;
            },

            /**
             * Updates balloon range slider data
             *
             * @param {number} data - value of selected balloon range slider position
             */
            bpSliderUpdateData(data) {
                if (this.activeFinanceGroupId === parseInt(this.instalmentID)) {
                    this.$store.commit('setBalloonPercentage', data);
                    this.FinanceFilter.balloonPercentage = data;
                    this.FinanceFilter.financeParams.balloonPercentage = data;
                    this.bpSlider.changeActive(data);
                    this.$dispatch('CarFinder::balloonPercentageChange');
                }
            },

            /**
             * Toggles (show/hides) the Edit Your Budget finance filters box and updates balloon range slider data
             */
            toggleEditYourBudgetBox() {
                this.$emit('toggle');
                this.bpSliderUpdateData(this.storeBalloonPercentage());
            },

            resetFinanceFilter() {
                const defaultValues = this.FinanceFilter.financeDefaultParams[
                    (parseInt(this.FinanceFilter.defaultPayment.group_id) || this.$store.state.finance.financeGroupId)
                ];

                this.FinanceFilter.monthlyPay = defaultValues.monthlypay;
                this.FinanceFilter.initialPay = defaultValues.deposit;
                this.FinanceFilter.annualMileage = defaultValues.mileage;
                this.FinanceFilter.payOver = defaultValues.term;
                this.FinanceFilter.depositMultiple = defaultValues.depositMultiple;
                this.FinanceFilter.payInFull = defaultValues.payinfull;
                this.FinanceFilter.maintenance = defaultValues.maintenance;
                this.FinanceFilter.balloonPercentage = defaultValues.balloonPercentage;

                this.$refs.monthlyPaySlider.changeActive(defaultValues.monthlypay);
                this.$refs.initialPaySlider.changeActive(defaultValues.deposit);
                this.$refs.mileageSlider.changeActive(defaultValues.mileage);
                this.$refs.payOverSlider.changeActive(defaultValues.term);
                this.$refs.depositMultipleSlider.changeActive(defaultValues.depositMultiple);
                this.payInFullSlider.changeActive(defaultValues.payinfull);
                this.$refs.balloonPercentageSlider.changeActive(defaultValues.balloonPercentage);

                // Resetting finance data in various places
                EventsBus.$emit('FinanceFilter::resetFilters');
                EventsBus.$emit('CarFinder::update');
            },

            updateFinanceValue($event, param, valueElem) {
                this.FinanceFilter.updateFields($event, param, valueElem);

                if (this.$refs.monthlyPaySlider) {
                    this.$refs.monthlyPaySlider.changeActive(this.FinanceFilter.monthlyPay);
                }

                if (this.$refs.initialPaySlider) {
                    this.$refs.initialPaySlider.changeActive(this.FinanceFilter.initialPay);
                }

                if (this.$refs.mileageSlider) {
                    this.$refs.mileageSlider.changeActive(this.FinanceFilter.annualMileage);
                }

                if (this.$refs.payOverSlider) {
                    this.$refs.payOverSlider.changeActive(this.FinanceFilter.payOver);
                }

                if (this.$refs.depositMultipleSlider) {
                    this.$refs.depositMultipleSlider.changeActive(this.FinanceFilter.depositMultiple);
                }

                if (this.payInFullSlider) {
                    this.payInFullSlider.changeActive(this.FinanceFilter.payInFull);
                }

                if (this.$refs.balloonPercentageSlider) {
                    this.$refs.balloonPercentageSlider.changeActive(this.FinanceFilter.balloonPercentage);
                }
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

            /**
             * Override default2 changePayInFull method.
             * When input boxes are changed this method will change slider
             */
            changePayInFull() {
                const payInFullDetails = this.FinanceFilter.currentFinanceSteps.payinfull;
                const minVal = payInFullDetails.min;
                const maxVal = payInFullDetails.max;
                const payInFullInputs = this.FinanceFilter.payInFullInputs;

                if (payInFullInputs[0] > payInFullInputs[1]) {
                    payInFullInputs.$set(0, payInFullInputs[1]);
                }

                if (payInFullInputs[0] < minVal) {
                    payInFullInputs.$set(0, minVal);
                }

                if (payInFullInputs[1] > maxVal) {
                    payInFullInputs.$set(1, maxVal);
                }

                this.FinanceFilter.payInFull = payInFullInputs;
                this.ProductGrid.financeParams[this.FinanceFilter.section].payinfull = payInFullInputs;
                this.payInFullSlider.changeActive(payInFullInputs);
                this.payInFullSlider.changeSteps(payInFullDetails.step);
            }
        },

        events: {
            'FinanceFilterMenu::bp-update'(data) {
                this.bpSliderUpdateData(data);
            }
        },

        watch: {
            /**
             * Updates bpSliderData (balloon range slider) on Instalment Sale section
             *
             * @param {number} groupId of selected Finance Group Method
             */
            activeFinanceGroupId(groupId) {
                if (groupId === parseInt(this.instalmentID)) {
                    this.$nextTick(() => {
                        this.bpSliderUpdateData(this.storeBalloonPercentage());
                    })
                }

                this.isPayInFull = false;

                if (this.payInFullPayment) {
                    // Re-run method to pull in correct min, max and step for the slider
                    this.$nextTick(() => this.changePayInFull());
                }
            }
        },

        ready() {
            this.bpSliderUpdateData(this.storeBalloonPercentage());

            // Get instalment sale group ID
            const instalment = this.FinanceFilter.financeOptions.items.find(item => item.group_title.includes('Instalment Sale'));

            if (instalment) {
                this.instalmentID = instalment.group_id;
            }
        }
    });
</script>
