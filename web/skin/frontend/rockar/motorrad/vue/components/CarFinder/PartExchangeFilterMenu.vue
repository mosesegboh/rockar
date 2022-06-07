<template>
    <div>
        <div class="car-filter-item px-filter px-filter-edit"
             v-show="showPxEdit">
            <input type="radio"
                   name="car-filter-open"
                   class="car-filter-open"
                   id="input-px"
                   :checked="openFilter === 'px-filter' && showPxEdit"
                   value="px-filter"
                   @click="$emit('toggle')"
            >
            <label for="input-px" class="car-filter-title">
                <div>{{ 'Edit Trade in' | translate }}</div>
            </label>
            <div class="car-filter-blocks">
                <div class="finance-header">{{ 'Edit Trade in' | translate }}</div>
                <div class="px-car-info">
                    <p>{{ 'Ok, so your current car\'s a' | translate }}:<br></p>
                    <p class="px-car-name">{{ PartExchangeVRM.carInfo.title }}</p>
                </div>
                <div class="px-filter-wrapper">
                    <p>{{ 'Current Registration:' | translate }} </p>
                    <div class="px-filter-input">
                        <div class="px-filter-input-wrapper">
                            <input type="text"
                                   v-model="PartExchangeVRM.carInfo.vrmInput"
                                   class="keyboard-numbers"
                                   id="px-filter-current-registration"
                                   disabled
                            >
                        </div>
                    </div>
                </div>

                <div class="px-filter-wrapper">
                    <p>{{ 'Year of first registration:' | translate }} </p>
                    <div class="px-filter-input">
                        <div class="px-filter-input-wrapper">
                            <input
                                type="text"
                                class="keyboard-numbers"
                                id="px-filter-first-registration"
                                placeholder="Year"
                                v-model="PartExchangeVRM.carInfo.registrationYear | regYearFormat"
                                value="PartExchangeVRM.carInfo.registrationYear"
                                :disabled="PartExchangeValuation.valuationResult"
                            >
                        </div>
                    </div>
                </div>

                <div class="px-filter-wrapper">
                    <p>{{ 'Current Mileage:' | translate }} </p>
                    <div class="px-filter-input">
                        <div class="px-filter-input-wrapper">
                            <input type="text"
                                   v-model="PartExchangeComponent.mileage | numberMilesFormat"
                                   class="keyboard-numbers"
                                   @focus="selectMileage"
                                   @blur="deselectMileage"
                                   placeholder="0"
                                   id="px-filter-current-mileage"
                            >
                        </div>
                    </div>
                </div>

                <div class="px-filter-wrapper">
                    <p>{{ 'Outstanding Finance:' | translate }} </p>
                    <div class="px-filter-input">
                        <div class="px-filter-input-wrapper">
                            <input type="text"
                                   v-model="PartExchangeValuation.outstandingFinance | numberCurrencyFormat"
                                   class="keyboard-numbers"
                                   @focus="selectOutstandingFinance"
                                   @blur="deselectOutstandingFinance"
                                   placeholder="0"
                                   id="px-filter-current-outstanding-finance"
                            >
                        </div>
                    </div>
                </div>

                <div class="px-filter-wrapper select-wrapper">
                    <p>{{ 'Condition:' | translate }} </p>
                    <div class="px-filter-input">
                        <div class="px-filter-input-wrapper">
                            <app-select
                                @select="changeCarCondition"
                                :options="PartExchangeFilter.carConditions"
                                :init-selected="initialPxCondition"
                                :item-height="30"
                                v-ref:car-conditions-dropdown
                            ></app-select>
                        </div>
                    </div>
                </div>
                <div class="px-filter-wrapper select-wrapper" v-if="hasNegativeEquity && !isPayInFullGroup">
                    <p>{{ 'Trade-In Shortfall:' | translate }} </p>
                    <div class="px-filter-input">
                        <div class="px-filter-input-wrapper">
                            <app-select
                                :init-selected="getPxOutstandingFinanceSettlement() - 1"
                                :item-height="45"
                                :options="PartExchangeComponent.outstandingFinanceSettlementOptions"
                                @select="changeNegativeEquityPayment"
                                v-ref:outstanding-finance-settlement
                            ></app-select>
                        </div>
                    </div>
                </div>

                <div class="px-filter-wrapper final-value"
                     v-show="isPXExists && isPXValuated && PartExchangeValuation.valuationResult">
                    <p>{{ 'Current Value:' | translate }}</p>
                    <div class="px-filter-input">
                        <div class="px-filter-final-value">
                            <span class="px-final-value">
                                {{ PartExchangeValuation.partExchangeValue | finalPXValueFormat }}
                            </span>
                            <button @click="updatePXData()"
                                    :disabled="!isOutStandingValid || !isMileageValid">
                                {{'Refresh' | translate }}
                            </button>
                        </div>
                    </div>
                </div>

                <a v-if="isPXExists && isPXValuated && getPartExchangeValuationResult"
                   @click="removePX()"
                   class="removed-px">
                    {{ 'Remove Trade-In' | translate }}
                    (+{{PartExchangeValuation.partExchangeValue | finalPXValueFormat }})
                </a>
                <button v-else @click="coninuePX()" class="continue-px">
                    {{ 'Continue to Trade-In' | translate }}
                </button>
            </div>
        </div>

        <div class="car-filter-item px-filter-add px-filter"
             v-show="showPxAdd">
            <input type="radio"
                   name="car-filter-open"
                   class="car-filter-open"
                   id="input-px-add"
                   :checked="openFilter === 'px-filter' && showPxAdd"
                   value="px-filter"
                   @click="$emit('toggle')"
            >
            <label for="input-px-add" class="car-filter-title">
                <div>{{ 'Edit Your Trade-In' | translate }}</div>
            </label>
            <div class="car-filter-blocks">
                <div class="px-filter-wrapper" v-show="showPxMainData">
                    <p>{{ 'Current Registration:' | translate }} </p>
                    <div class="px-filter-input">
                        <div class="px-filter-input-wrapper">
                            <input type="text"
                                   v-model="PartExchangeVRM.carInfo.vrmInput"
                                   class="keyboard-numbers"
                                   id="px-filter-current-registration-add"
                                   disabled>
                        </div>
                    </div>
                </div>

                <div class="px-filter-wrapper" v-show="!isPXValuated && PartExchange.mileage">
                    <p>{{ 'Current Mileage:' | translate }} </p>
                    <div class="px-filter-input">
                        <div class="px-filter-input-wrapper">
                            <input type="text"
                                   v-model="PartExchange.mileage | numberMilesFormat"
                                   class="keyboard-numbers"
                                   id="px-filter-current-mileage-add"
                                   disabled>
                        </div>
                    </div>
                </div>

                <button @click="coninuePX()">{{ 'Continue to Trade-In' | translate }}</button>
            </div>
        </div>
    </div>
</template>

<script>
    import corePartExchangeFilterMenu from 'core/components/CarFinder/PartExchangeFilterMenu';
    import appConfiguratorPartExchange from 'motorrad/components/Configurator/PartExchange';
    import translateString from 'core/filters/Translate';
    import numeral from 'numeral';
    import appSelect from 'motorrad/components/Elements/Select';

    export default corePartExchangeFilterMenu.extend({
        data() {
            return {
                currentYear: new Date().getFullYear(),
                minYear: 1885
            }
        },

        computed: {
            FinanceFilter() {
                return this.$root.$refs.financeFilter;
            },

            isOutStandingValid() {
                return true;
            },

            hasNegativeEquity() {
                return this.$store.state.general.PX.Valuation.outstandingFinance > (
                    this.$store.state.general.PX.Valuation.partExchangeValue +
                    this.FinanceFilter.initialPaymentAmount
                );
            },

            financeActivePaymentGroupId() {
                return this.$store.state.finance.financeGroupId;
            },

            payInFullPayment() {
                return this.$root.$refs.productGrid !== null ? this.$root.$refs.productGrid.payInFullPayment : [];
            },

            isPayInFullGroup() {
                return this.payInFullPayment.find(payment => payment.group_id === this.financeActivePaymentGroupId)
                    !== undefined;
            },

            outstandingFinanceSettlementOptions() {
                return [
                    {
                        id: 1,
                        title: 'Add to monthly payment'
                    },
                    {
                        id: 2,
                        title: 'Once-off payment'
                    }
                ];
            }
        },

        filters: {
            numberCurrencyFormat: {
                read(number) {
                    if (isNaN(number)) {
                        number = 0;
                    }

                    if (this.hasOutstandingFinanceFocus) {
                        return number !== 0 ? numeral(number).format('0,0.00') : '';
                    } else {
                        return currencySymbol + numeral(number).format('0,0.00');
                    }
                },
                write(number) {
                    return numeral(number).value().toFixed(2);
                }
            },

            numberMilesFormat: {
                read(number) {
                    number = Math.floor(number);
                    if (isNaN(number)) {
                        number = 0;
                    }
                    if (this.hasMileageFocus) {
                        return number !== 0 ? numeral(number).format('0,0') : '';
                    } else {
                        return numeral(number).format('0,0') + this.translateString(' KM');
                    }
                },
                write(number) {
                    return Math.floor(numeral(number).value());
                }
            },

            regYearFormat: {
                read(number) {
                    return numeral(number).value();
                },

                write(number) {
                    number = numeral(number.toString().replace(/\D/g, '')).value();

                    if (number < this.minYear) {
                        number = this.minYear;
                    } else if (number > this.currentYear) {
                        number = this.currentYear;
                    }

                    return number;
                }
            },

            finalPXValueFormat: {
                read(number) {
                    return currencySymbol + numeral(number).format('0,0.00');
                },
                write(number) {
                    if (number >= this.PartExchangeValuation.valuationResult) {
                        return this.PartExchangeValuation.valuationResult - 1;
                    } else if (number < 0) {
                        return 0;
                    } else {
                        return number;
                    }
                }
            }
        },

        events: {
            'PartExchange::changeCarCondition'(newValue) {
                this.$refs.carConditionsDropdown.selected = newValue - 1;
            },

            'PartExchangeFilter::outstandingFinanceSettlement'(newValue) {
                if (this.$refs.outstandingFinanceSettlement) {
                    this.$refs.outstandingFinanceSettlement.initSelected = newValue - 1;
                }
            },

            'PartExchangeFilterMenu::updateSelectedCondition'() {
                const carCondition = this.PartExchangeFilter.carConditions[this.initialPxCondition];

                if (typeof carCondition !== 'undefined') {
                    this.selectedConditionsId = carCondition.id;
                }
            }
        },

        methods: {
            getPxOutstandingFinanceSettlement() {
                const general = this.$store.state.general;

                return (general && general.PX && general.PX.Valuation
                    && general.PX.Valuation.outstandingFinanceSettlement)
                    || this.PartExchangeComponent.outstandingFinanceSettlement;
            },

            updatePXData(deletePx = true) {
                this.ajaxLoading = true;
                this.ProductGrid.ajaxGlobalLoading = true;
                if (deletePx) {
                    this.$store.commit('setPXMileage', this.PartExchangeComponent.mileage);
                    this.$store.commit('setPXRegistrationYear', this.PartExchangeVRM.carInfo.registrationYear);
                }
                this.$dispatch('Main::syncPXWithVuex');
                this.$dispatch('CarFinder::changeCarCondition', this.selectedConditionsId);
                this.$broadcast('Select::updateSelected', this.initialPxCondition);

                if (this.$parent.$parent.partExchangeIsValid) {
                    this.PartExchangeFilter.getValuation().then(() => {
                        this.$store.commit('setPXOutstandingFinanceSettlement', this.PartExchangeComponent.activeFinanceSettlementCondition);
                        this.PartExchangeValuationComponent.saveValuation().then(() => {
                            this.ProductGrid.ajaxGlobalLoading = false;
                        });
                    }, () => {
                        this.ProductGrid.ajaxGlobalLoading = false;
                    });
                } else {
                    this.ProductGrid.ajaxGlobalLoading = false;
                }
            },

            removePX() {
                this.PartExchangeComponent.resetFilters();

                this.updatePXData();

                if (this.$parent.$parent.partExchangeIsValid) {
                    this.PartExchangeValuationComponent.continueWithout();
                }
            },

            changeNegativeEquityPayment(data) {
                this.PartExchangeComponent.activeFinanceSettlementCondition = data.id;
            }
        },

        created() {
            EventsBus.$on('PartExchangeFilter::outstandingFinanceSettlement', (val) => {
                this.$dispatch('PartExchangeFilter::outstandingFinanceSettlement', val);
            });
        },

        ready() {
            if (!this.PartExchangeComponent.activeFinanceSettlementCondition) {
                this.PartExchangeComponent.activeFinanceSettlementCondition =
                    this.PartExchangeComponent.outstandingFinanceSettlement;
            }

            this.selectedConditionsId = this.initialPxCondition + 1;
        },

        components: {
            appConfiguratorPartExchange,
            appSelect
        }
    });
</script>
