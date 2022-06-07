<template>
    <div id="choose-payment">
        <div class="header-mobile">
            {{ myAccount ? 'Payment Summary' : 'Payment Options' | translate }}
        </div>
        <template v-if="!sessionError">
            <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

            <div class="wrapper" v-show="dataReceived">
                <div class="tabs" v-if="!myAccount">
                    <template v-for="option in options">
                        <section :class="{'active': parseInt(option.group_id) === parseInt(groupId)}"
                              @click="changeActiveMethod($index, option.group_id)">
                            <div class="group-wrapper">
                                <p class="group-title">{{ option.group_full_title | translate }}</p>
                                <template v-if="option.calc && option.calc.monthly_price && option.pay_in_full === 0 && option.calc.min_deposit_validation.value">
                                    <div class="group-finance-wrapper">
                                        <p class="group-finance">{{ option.calc.monthly_price.value | numberFormat '0,0' true true }}</p>
                                        <span>{{ 'p/m' | translate }}</span>
                                    </div>
                                    <template v-if="isHirePayment">
                                        <p>{{ option.calc.cash_deposit.value | numberFormat '0,0' true true }}</p>
                                        <p>{{ 'initial payment' | translate }}</p>
                                    </template>
                                </template>
                                <template v-if="sliderCombinationInvalid(option)">
                                    <div class="invalid">
                                        <span class="icon-info-grey"></span>
                                        <p>{{ sliderInvalidCombinationText }}</p>
                                    </div>
                                </template>
                                <template v-if="option.calc && option.calc.product_price && option.pay_in_full === 1">
                                    <p class="group-finance">{{ option.calc.product_price.value | numberFormat '0,0' true }}</p>
                                </template>
                            </div>
                        </section>
                    </template>
                </div>
                <div class="content-container first">
                    <div class="image mobile" v-if="myAccount">
                        <img :src="image" :alt="getImageTitle | translate" v-show="!selectInterior">
                    </div>
                    <div class="content options-container" v-if="!myAccount">
                        <img :src="image" :alt="getImageTitle"/>
                        <div class="select">
                            <app-select
                                @select="selectFinance"
                                :options="financeGroupOptions"
                                :init-selected="groupId.toString()"
                                :label="'Choose Payment Option'"
                            >
                            </app-select>
                        </div>
                        <p class="quote-tailor" v-if="showSlider">{{ 'Tailor your quote' | translate }}</p>
                        <div v-show="showSlider && !useShortVersion && !isStatic">
                            <div class="finance-filter-slider-wrapper deposit-filter" v-show="!isHirePayment">
                                <p class="field-title">{{ 'Deposit' | translate }}</p>
                                <div class="finance-filter-slider">
                                    <app-range-slider
                                        :use-id="true"
                                        :active-on-slide="true"
                                        :active="financeParams.deposit"
                                        :options="currentFinanceSteps.deposit"
                                        custom-event="FinanceOverlay::conditionChanged"
                                        custom-event-slide="FinanceOverlay::conditionChangeDeposit"
                                        v-ref:deposit-slider
                                    ></app-range-slider>
                                    <div class="finance-filter-slider-input">
                                        <input
                                            type="text"
                                            v-model="financeParams.deposit | numberCurrencyFormat"
                                            @keyup.enter="changeValue('deposit')"
                                            @blur="changeValue('deposit')"
                                            size="10"
                                            class="keyboard-numbers"
                                            maxlength="10"
                                        >
                                    </div>
                                </div>
                            </div>
                            <p class="more-deposit"
                               v-if="option.calc && option.pay_in_full === 0 && !option.calc.min_deposit_validation.value"
                            >
                                {{ 'More Deposit required' | translate }}
                            </p>
                            <div v-show="isHirePayment">
                                <div>
                                    <div>
                                        {{ 'Deposit multiple' | translate }}
                                        <span>{{ financeParams.depositMultiple | numberFormat '0' false }}</span>
                                    </div>
                                </div>
                                <app-range-slider
                                    :use-id="true"
                                    :active-on-slide="true"
                                    :active="financeParams.depositMultiple"
                                    :options="currentFinanceSteps.depositMultiple"
                                    custom-event="FinanceOverlay::conditionChanged"
                                    custom-event-slide="FinanceOverlay::conditionChangeDepositMultiple"
                                    :display-steps="true"
                                    v-ref:depositMultiple-slider
                                ></app-range-slider>
                                <div>
                                    <input id="finance-maintenance"
                                       type="checkbox"
                                       v-model="financeParams.maintenance"
                                       :true-value="1"
                                       :false-value="0"
                                    >
                                    <label for="finance-maintenance"><span></span>{{ 'Add maintenance' }}</label>
                                </div>
                            </div>

                            <div class="finance-filter-slider-wrapper">
                                <p class="field-title">{{ 'Number of Instalments' | translate }}</p>
                                <div class="finance-filter-slider">
                                    <app-range-slider
                                        :use-id="true"
                                        :active-on-slide="true"
                                        :active="financeParams.term"
                                        :options="currentFinanceSteps.term"
                                        custom-event="FinanceOverlay::conditionChanged"
                                        custom-event-slide="FinanceOverlay::conditionChangeTerm"
                                        v-ref:term-slider
                                    ></app-range-slider>
                                    <div class="finance-filter-slider-input">
                                        <div class="finance-filter-slider-input-wrapper">
                                            <input
                                                type="text"
                                                :value="financeParams.term | numberMonthsFormat"
                                                class="keyboard-numbers"
                                                size="10"
                                                maxlength="14"
                                                readonly
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="finance-filter-slider-wrapper" v-show="!isInstalmentSaleID(activeGroup)">
                                <p class="field-title">{{ 'Contract End Mileage' | translate }}</p>
                                <div class="finance-filter-slider">
                                    <app-range-slider
                                        :use-id="true"
                                        :active-on-slide="true"
                                        :active="financeParams.mileage"
                                        :options="currentFinanceSteps.mileage"
                                        custom-event="FinanceOverlay::conditionChanged"
                                        custom-event-slide="FinanceOverlay::conditionChangeMileage"
                                        v-ref:mileage-slider
                                    ></app-range-slider>
                                    <div class="finance-filter-slider-input">
                                        <div class="finance-filter-slider-input-wrapper">
                                            <input
                                                type="text"
                                                v-model="financeParams.mileage | numberKilometerFormat"
                                                class="keyboard-numbers"
                                                readonly
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="finance-filter-slider-wrapper mileage-filter"
                                 v-show="isInstalmentSaleID(activeGroup)"
                            >
                                <p class="field-title">{{ 'Balloon Percentage' | translate }}</p>
                                <div class="finance-filter-slider">
                                    <app-range-slider
                                        :use-id="true"
                                        :active-on-slide="true"
                                        :active="financeParams.balloonPercentage"
                                        :options="currentFinanceSteps.balloonPercentage"
                                        custom-event="FinanceOverlay::conditionChanged"
                                        custom-event-slide="FinanceOverlay::bp-update"
                                        v-ref:balloon-percentage-slider
                                    ></app-range-slider>
                                    <div class="finance-filter-slider-input">
                                        <div class="finance-filter-slider-input-wrapper">
                                            <div class="finance-filter-input">
                                                <input
                                                    id="balloon-percentage-value"
                                                    type="text"
                                                    class="keyboard-numbers"
                                                    v-model="financeParams.balloonPercentage | numberPercentageFormat"
                                                    readonly
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <template v-for="option in options">
                            <div class="continue-container"
                                 :class="{ active: option.pay_in_full }"
                                 v-if="parseInt(this.activeGroup) === parseInt(option.group_id) && option.calc"
                            >
                                <div class="container-table">
                                    <table>
                                        <tbody>
                                        <tr>
                                            <template v-if="!option.pay_in_full">
                                                <td class="label">{{ 'Per Month' | translate }}</td>
                                                <td class="value"
                                                    v-if="option.calc.monthly_price.value || option.calc.monthly_price.value === 0"
                                                >
                                                    <h4>{{ option.calc.monthly_price.value | numberCurrencyFormat }}</h4>
                                                </td>
                                            </template>
                                        </tr>
                                        <tr>
                                            <td class="label"
                                                :class="{ active: option.pay_in_full }"
                                            >
                                                {{ 'Offer Price' | translate }}
                                            </td>
                                            <td class="value"
                                                :class="{ active: option.pay_in_full }"
                                                v-if="option.calc.product_price.value || option.calc.product_price.value === 0"
                                            >
                                                <h4>{{ option.calc.product_price.value | numberCurrencyFormat }}</h4>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button
                                    @click="isOptionAvailable(this.option) ? selectAndContinue() : ''"
                                    :class="[!isOptionAvailable(this.option) ? 'button dsp2-money disabled' : 'button dsp2-money']"
                                    :disabled="!isOptionAvailable(this.option)"
                                >
                                    {{ 'Update and continue' | translate }}
                                </button>
                            </div>
                        </template>
                    </div>
                    <div class="content-table" v-el:container>
                        <h5>{{ 'Your Quote' | translate }}</h5>
                        <template v-for="option in options">
                            <table v-if="parseInt(this.activeGroup) === parseInt(option.group_id)"
                               :class="{ active:option.pay_in_full }"
                            >
                                <tbody v-if="!parseInt(option.is_static)">
                                <tr style="display:none;">
                                    <td>{{ setTempVariables(option) }}</td>
                                </tr>

                                <tr v-for="vars in option.variables"
                                    :class="[
                                        getVariableClassName(vars),
                                        vars.variable === 'part_exchange' ? 'trade-in' : ''
                                    ]"
                                >
                                    <td class="label">{{ parseVariableTitleMethod(vars.variable_title) }}</td>
                                    <td class="value" v-if="vars.value || vars.value === 0">
                                        <p>{{ vars.value_formatted }}</p>
                                    </td>
                                    <td class="value" v-else>
                                        <div class="invalid">
                                            <p>{{ sliderInvalidCombinationText }}</p>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </template>
                        <div v-if="!isStatic && !myAccount">
                            <button
                                @click="isOptionAvailable(this.option) ? selectAndContinue() : ''"
                                :class="[!isOptionAvailable(this.option) ? 'button dsp2-money disabled' : 'button dsp2-money']"
                                :disabled="!isOptionAvailable(this.option)"
                            >
                                {{ 'Update and Continue' | translate }}
                            </button>
                        </div>
                    </div>
                </div>

                <template v-for="option in options">
                    <div class="content-container list"
                         :class="{ 'padding':option.footer === null }"
                         v-show="parseInt(activeGroup) === parseInt(option.group_id)"
                         :data-id="option.group_title.toLowerCase()"
                    >
                        <div class="content">
                            <div class="ffo-header"
                                 :class="{ show: showMobileList }"
                                 v-html="option.header"
                                 @click="toggleClass"
                                 v-if="!myAccount"
                            ></div>
                            <div v-else class="ffo-header"> {{ option.group_title }} </div>
                            <div v-if="myAccount" class="image">
                                <img :src="image" :alt="getImageTitle | translate" v-show="!selectInterior">
                            </div>
                            <div class="video-container" v-if="!myAccount">
                                <div v-if="parseInt(activeGroup) === parseInt(option.group_id)"
                                     v-html="option.video"
                                ></div>
                            </div>
                            <div class="ffo-video" v-if="option.video && !myAccount" @click="videoPopup(true)">
                                <span>{{ 'Watch Video' | translate }}</span>
                            </div>
                            <div class="ffo-footer" v-show="option.footer !== null" v-html="option.footer" v-if="myAccount"></div>
                        </div>
                    </div>
                </template>

                <div class="ffo-footer" v-show="option.footer !== null" v-html="option.footer" v-if="!myAccount"></div>
            </div>
        </template>
        <template v-if="sessionError">
            <div class="wrapper">
                <h4>{{ 'Session expired' | translate }}</h4>
                <p>
                    {{ 'Sorry, but your session expired, please refresh the page to continue' | translate }}
                </p>
            </div>
        </template>
        <app-modal
            :class="'finance-filter-overlay-video'"
            v-ref:finance-filter-overlay-video
            @close-popup="videoPopup(false)">
            <div slot="content">
                <div v-for="option in options" v-show="parseInt(activeGroup) === parseInt(option.group_id)">
                    <div v-if="$refs.financeFilterOverlayVideo.show" v-html="option.video"></div>
                </div>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import appModal from 'core/components/Elements/Modal';
    import coreFinanceOverlay from 'core/components/FinanceOverlay';
    import FinanceOverlay from 'dsp2/components/Shared/FinanceOverlay';
    import translateString from 'core/filters/Translate';
    import Constants from 'dsp2/components/Shared/Constants';
    import appSelect from 'dsp2/components/Elements/Select';
    import EventTrackerFinanceOverlay from 'dsp2/mixins/EventTrackerFinanceOverlay';

    export default coreFinanceOverlay.extend({
        mixins: [Constants, FinanceOverlay, EventTrackerFinanceOverlay],

        data() {
            return {
                tradeinShortfallOnecashPayment: 2, // Settle trade-in shortfall in a single lump sum payment.
                groupId: null,
                option: {},
                allowAjaxUpdate: true,
                overlayVideoClass: '',
                showMobileList: false
            }
        },

        methods: {
            translateString,

            getDataForAjaxRequest() {
                const data = {
                    productId: this.productId,
                    calc_type: this.calcType,
                    slider_data: {
                        term: this.financeParams.term,
                        mileage: this.financeParams.mileage,
                        deposit: this.financeParams.deposit,
                        depositMultiple: this.financeParams.depositMultiple,
                        maintenance: this.financeParams.maintenance,
                        balloonPercentage: this.financeParams.balloonPercentage,
                        method: this.activeGroup
                    }
                };

                if (this.calcType === 'pdp') {
                    data.productId = this.configurator.selectedCar;
                }

                return data;
            },

            sliderCombinationInvalid(option) {
                return ((!option.calc || !option.calc.monthly_price || !option.calc.min_deposit_validation) && option.pay_in_full === 0) ||
                    (option.calc && option.pay_in_full === 0 && !option.calc.min_deposit_validation.value);
            },

            cashPaymentSettlement() {
                this.$store.commit('setPXOutstandingFinanceSettlement', this.tradeinShortfallOnecashPayment);
                this.$store.commit('setPXValuationOutstandingFinance', this.tradeinShortfallOnecashPayment);
                EventsBus.$emit('PartExchangeFilter::outstandingFinanceSettlement', this.tradeinShortfallOnecashPayment);
            },

            selectAndContinue() {
                this.ajaxLoading = true;
                const data = {
                    group_id: this.activeGroup,
                    payment_type: this.activeMethodType,
                    method: this.activeGroup,
                    term: this.financeParams.term,
                    mileage: this.financeParams.mileage,
                    deposit: this.financeParams.deposit,
                    depositMultiple: this.financeParams.depositMultiple,
                    balloonPercentage: this.financeParams.balloonPercentage,
                    maintenance: this.isHirePaymentByGroupId(this.activeGroup) ? this.financeParams.maintenance : 0,
                    product_id: this.productId,
                    calc_type: this.calcType
                };

                if (this.calcType === 'pdp') {
                    data.product_id = this.configurator.product.id;
                } else if (this.calcType === 'quote') {
                    data.product_id = this.FinanceQuote.product_id;
                }

                this.financeParamsOrigin[this.activeGroup].term = this.financeParams.term;
                this.financeParamsOrigin[this.activeGroup].mileage = this.financeParams.mileage;
                this.financeParamsOrigin[this.activeGroup].deposit = this.financeParams.deposit;
                this.financeParamsOrigin[this.activeGroup].depositMultiple = this.financeParams.depositMultiple;
                this.financeParamsOrigin[this.activeGroup].balloonPercentage = this.financeParams.balloonPercentage;
                this.financeParamsOrigin[this.activeGroup].maintenance = this.financeParams.maintenance;
                this.activePayment.group_id = parseInt(this.activeGroup);
                this.activePayment.payment_type = this.activeMethodType;

                this.$http({
                    url: this.paymentSaveUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data
                }).then(this.selectAndContinueSuccess, this.selectAndContinueFail);
            },

            selectAndContinueSuccess(data) {
                EventsBus.$emit('ChooseVehicle::ChooseCar');
                EventsBus.$emit('FinanceQuote::updateUrlAfterClosingPopup', this.OVERLAYSEARCHPARAMS.FINANCE);

                if (this.updateFinanceQuote) {
                    this.ajaxLoading = false;
                    this.FinanceQuote.updateFinanceQuote(data);
                    this.$dispatch('Main::paymentUpdated');
                    this.$store.commit('setFinanceGroupId', this.activeGroup);
                    this.$parent.closeFinancePopup();
                } else {
                    const productUrl = this.productUrl;

                    if (productUrl) {
                        window.location.href = productUrl;
                    }
                }

                this.dataReceived = true;
                this.$dispatch('Main::updateCompareData');
            },

            setBalloonPercentage() {
                if (isNaN(this.$store.state.general.balloonPercentage) && this.instalmentGroupId) {
                    this.$store.commit('setBalloonPercentage', this.financeParamsOrigin[this.instalmentGroupId].balloonPercentage);
                }
            },

            setOptions(groupId) {
                this.groupId = groupId;
            },

            setVideoListener() {
                if (this.showMobileList) {
                    this.$nextTick(() => {
                        this.$root.toggleVideoPlayButton();
                    });
                }
            },

            changeActiveMethod(index, groupId) {
                this.groupId = groupId;

                this.activeMethod = index;
                this.activeMethodType = this.options[index].type;
                this.activeGroup = parseInt(this.options[index].group_id);
                this.showSlider = parseInt(this.options[index].pay_in_full) === 0;
                this.isHirePayment = parseInt(this.options[index].is_hire_payment) === 1;

                if (this.useShortVersion) {
                    return;
                }

                this.allowAjaxUpdate = false;
                this.currentSliderSteps = this.financeSliderSteps[this.activeGroup];

                if (this.currentSliderSteps && !parseInt(this.options[index].is_static)) {
                    const depositSteps = this.currentSliderSteps.deposit;
                    const deposit = this.findClosestValue(depositSteps, parseInt(this.financeParams.deposit));
                    this.depositSliderRef.changeSteps(this.currentSliderSteps.deposit);
                    this.depositSliderRef.changeActive(deposit);
                    this.financeParams.deposit = deposit;

                    const termSteps = this.currentSliderSteps.term;
                    const term = this.findClosestValue(termSteps, parseInt(this.financeParams.term));
                    this.termSliderRef.changeSteps(termSteps);
                    this.termSliderRef.changeActive(term);
                    this.financeParams.term = term;

                    const mileageSteps = this.currentSliderSteps.mileage;
                    const mileage = this.findClosestValue(mileageSteps, parseInt(this.financeParams.mileage));
                    this.mileageSliderRef.changeSteps(mileageSteps);
                    this.mileageSliderRef.changeActive(mileage);
                    this.financeParams.mileage = mileage;

                    this.allowAjaxUpdate = true;
                    this.getDataByProductId();
                }

                this.options.forEach((option) => {
                    if (parseInt(this.activeGroup) === parseInt(option.group_id)) {
                        this.option = option;
                    }
                });

                this.setVideoListener();
                this.fireEvent(this.activeGroup);
            },

            videoPopup(open) {
                this.overlayVideoClass = this.overlayVideoClass ? '' : '-show-video';
                this.$refs.financeFilterOverlayVideo.show = open;
            },

            closeFinanceOverlay() {
                this.$dispatch('FinanceOverlay::closeFinanceOverlay');
            },

            selectFinance(data) {
                this.changeActiveMethod(parseInt(data.index), data.value)
            },

            toggleClass(data) {
                if (data.target.localName === 'h3') {
                    this.showMobileList = !this.showMobileList;

                    this.setVideoListener();
                }
            },

            /**
             * Fire event for tracking purposes on selected finance choice on Finance Overlay
             */
            fireEvent(financeGroupId) {
                this.setFinanceGroup(financeGroupId);
            }
        },

        computed: {
            configurator() {
                return this.$root.$refs.configurator;
            },

            bpSlider() {
                return this.$refs.balloonPercentageSlider;
            },

            isPayInFullGroup() {
                return this.payInFullPayment.find(payment => payment.group_id === this.activeGroup) !== undefined;
            },

            FinanceFilter() {
                try {
                    return this.$root.$refs.FinanceFilter;
                } catch (e) {
                    // FinanceFilter is available only in car-finder
                }

                return false;
            },

            financeGroupOptions() {
                const groups = this.options;

                const groupedGroups = [];

                Object.keys(groups).forEach((key) => {
                    groupedGroups.push({
                        title: groups[key].group_full_title,
                        value: groups[key].group_id,
                        index: key
                    });
                });

                return groupedGroups;
            },

            getImageTitle() {
                return `${this.title} ${this.subtitle}`;
            },

            sliderInvalidCombinationText() {
                return this.translateString('Slider combination is invalid');
            }
        },

        events: {
            'FinanceOverlay::bp-update'(data) {
                this.$store.commit('setBalloonPercentage', data);
                this.financeParams.balloonPercentage = data;
                this.bpSlider.changeActive(data);
            }
        },

        props: {
            payInFullPayment: {
                required: true,
                type: Array
            },
            instalmentGroupId: {
                required: false,
                type: Number
            },
            image: {
                required: false,
                type: String,
                default: ''
            },
            title: {
                required: false,
                type: String,
                default: ''
            },
            subtitle: {
                required: false,
                type: String,
                default: ''
            },
            myAccount: {
                required: false,
                type: Boolean,
                default: false
            }
        },

        ready() {
            this.setBalloonPercentage();

            if (this.FinanceFilter) {
                this.financeParams.balloonPercentage = this.FinanceFilter.balloonPercentage;
            }

            if (!isNaN(this.financeParams.balloonPercentage) && this.instalmentGroupId) {
                this.bpSlider.changeSteps(this.financeSliderSteps[this.instalmentGroupId].balloonPercentage);
                this.bpSlider.changeActive(this.financeParams.balloonPercentage);
            }
        },

        created() {
            this.groupId = this.activePayment.group_id;
        },

        components: {
            appModal,
            appSelect
        }
    });
</script>
