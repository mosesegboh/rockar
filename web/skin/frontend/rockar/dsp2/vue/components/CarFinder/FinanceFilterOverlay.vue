<template>
    <div slot="content">
        <app-modal
            :show.sync="show"
            :blur-background="false"
            v-bind:class-name="'finance-filters-overlay' + overlayVideoClass">
            <div slot="content">
                <div class="header-mobile" @click="closeFinanceFilterOverlay()">
                    {{ MobileHeaderText | translate }}
                </div>
                <div class="select">
                    <app-select
                        @select="selectFinance"
                        :options="financeGroupOptions"
                        :init-selected="groupId"
                        :label="'Choose Payment Option'">
                    </app-select>
                </div>
                <div class="tabs">
                    <section v-for="item in financeOptions.items" v-bind:class="{'active': parseInt(item.group_id) === parseInt(groupId)}"
                          @click="setOptions(item.group_id, item.group_title)">
                            {{ item.group_full_title }}
                    </section>
                </div>
                <div class="content-container">
                    <div class="content" v-for="item in financeOptions.items" v-show="parseInt(item.group_id) === parseInt(groupId)">
                        <div v-html="item.header" class="ffo-header"></div>
                        <div class="video-container">
                            <div v-if="parseInt(item.group_id) === parseInt(groupId)" v-html="item.video"></div>
                        </div>
                        <div class="ffo-video" v-if="item.video" @click="videoPopup(true)">
                            <span>{{ getVideButtonText }}</span>
                        </div>
                    </div>
                    <div class="options-container">
                        <div class="pay-in-full" v-show="isPayInFull" v-if="isPayInFull">
                            <div class="finance-filter-slider-wrapper">
                                <p class="field-title">{{ 'Your total budget' | translate }}</p>
                                <div data-id="pay-in-full-filter">
                                    <app-range-slider
                                        :v-if="isPayInFull"
                                        :use-id="false"
                                        :active="[params[groupId].payinfull[0], params[groupId].payinfull[1]]"
                                        :active-on-slide="true"
                                        :range="true"
                                        :min="currentFinanceSteps.payinfull.min"
                                        :max="currentFinanceSteps.payinfull.max"
                                        :step="currentFinanceSteps.payinfull.step"
                                        custom-event-slide="FinanceFilter::pif-change-popup"
                                        v-ref:pay-in-full-slider-popup
                                    ></app-range-slider>
                                </div>
                                <div class="finance-filter-slider small three-action">
                                    <div class="finance-filter-slider-input input-one">
                                        <div>
                                            <input
                                                @change="areFinanceSlidersChanged = true"
                                                type="text"
                                                :value="params[groupId].payinfull[0] | numberInputFormat"
                                                v-model="params[groupId].payinfull[0] | numberInputFormat"
                                                size="10"
                                                class="keyboard-numbers"
                                                @blur="changePayInFullPopup()"
                                                @keyup.enter="changePayInFullPopup()"
                                                maxlength="14"
                                            >
                                        </div>
                                    </div>
                                    <div class="finance-filter-slider-input input-two">
                                        <div>
                                            <input
                                                @change="areFinanceSlidersChanged = true"
                                                type="text"
                                                :value="params[groupId].payinfull[1] | numberInputFormat"
                                                v-model="params[groupId].payinfull[1] | numberInputFormat"
                                                size="10"
                                                class="keyboard-numbers"
                                                maxlength="14"
                                                @blur="changePayInFullPopup()"
                                                @keyup.enter="changePayInFullPopup()"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-show="!isPayInFull" v-if="!isPayInFull">
                            <div class="finance-filter-slider-wrapper deposit-filter" v-show="showDeposit">
                                <p class="field-title">{{ 'Deposit' | translate }}</p>
                                <div class="finance-filter-slider" data-id="deposit-filter">
                                    <app-range-slider
                                        :use-id="true"
                                        :options="currentFinanceSteps.deposit"
                                        :active="params[groupId].deposit"
                                        :active-on-slide="true"
                                        custom-event-slide="FinanceFilter::ip-change"
                                        v-ref:initial-pay-slider></app-range-slider>
                                    <div class="finance-filter-slider-input">
                                        <div class="finance-filter-slider-input-wrapper">
                                            <input
                                                type="text"
                                                v-model="params[groupId].deposit | numberCurrencyFormat"
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
                            <div class="finance-filter-slider-wrapper mileage-filter" v-show="showBalloonPercentage" v-if="showBalloonPercentage">
                                <p class="field-title">{{ 'Balloon Percentage' | translate }}</p>
                                <div class="finance-filter-slider" data-id="percentage-filter">
                                    <app-range-slider
                                        :use-id="true"
                                        :options="currentFinanceSteps.balloonPercentage"
                                        :active="params[groupId].balloonPercentage"
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
                                                    v-model="params[groupId].balloonPercentage | numberPercentageFormat"
                                                    readonly
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="finance-filter-slider-wrapper term-filter" v-show="showInstalments" v-if="showInstalments">
                                <p class="field-title">{{ 'Number of Instalments' | translate }} </p>
                                <div class="finance-filter-slider" data-id="term-filter">
                                    <app-range-slider
                                        :use-id="true"
                                        :options="currentFinanceSteps.term"
                                        :active="params[groupId].term"
                                        :active-on-slide="true"
                                        custom-event-slide="FinanceFilter::po-change"
                                        v-ref:pay-over-slider
                                    ></app-range-slider>
                                    <div class="finance-filter-slider-input">
                                        <div class="finance-filter-slider-input-wrapper">
                                            <div class="finance-filter-input">
                                                <input
                                                    type="text"
                                                    v-model="params[groupId].term | numberMonthsFormat"
                                                    class="keyboard-numbers"
                                                    id="pay-over-month"
                                                    @change="updateFields($event, 'term', 'payOver')"
                                                    readonly
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="finance-filter-slider-wrapper mileage-filter" v-show="showMileage" v-if="showMileage">
                                <p class="field-title">{{ ContractMileageTitle | translate }}</p>
                                <div class="finance-filter-slider " data-id="mileage-filter">
                                    <app-range-slider
                                        :use-id="true"
                                        :options="currentFinanceSteps.mileage"
                                        :active="params[groupId].mileage"
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
                                                    v-model="params[groupId].mileage | numberKilometerFormat"
                                                    class="keyboard-numbers"
                                                    @change="updateFields($event, 'mileage', 'annualMileage')"
                                                    readonly
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="continue-container">
                            <button type="button" name="button" class="button dsp2-money" @click="moreFiltersOverlayContinue()">
                                {{ getCtaText }}
                            </button>
                        </div>
                        <app-part-exchange-block-content
                            @remove="removePX"
                            @open="openTradeInModalPxActive"
                            :has-saved-px="hasSavedPx"
                            :product-title="productTitle"
                            :saved-px-value="savedPxValue"
                            :est-value-disclaimer="estValueDisclaimer"
                        ></app-part-exchange-block-content>
                        <template v-if="!hasSavedPx">
                            <button class="button dsp2-outline" @click="openTradeInModal()">
                                {{ getTradeInButtonText }}
                            </button>
                        </template>
                    </div>
                </div>
                <div class="ffo-footer"
                     v-for="item in financeOptions.items"
                     v-show="(parseInt(item.group_id) === parseInt(groupId)) && item.footer !== null"
                     v-html="item.footer"></div>
            </div>
        </app-modal>
        <app-modal
            :class="'finance-filter-overlay-video'"
            v-ref:finance-filter-overlay-video
            @close-popup="videoPopup(false)">
            <div slot="content">
                <div v-for="item in financeOptions.items" v-show="parseInt(item.group_id) === parseInt(groupId)">
                    <div v-if="$refs.financeFilterOverlayVideo.show" v-html="item.video"></div>
                </div>
            </div>
        </app-modal>
    </div>
</template>

<script>
import appModal from 'dsp2/components/Elements/Modal';
import appRangeSlider from 'core/components/Elements/RangeSlider';
import numeral from 'numeral';
import translateString from 'core/filters/Translate';
import appSelect from 'dsp2/components/Elements/Select';
import appPartExchangeBlockContent from 'dsp2/components/PartExchange/PartExchangeBlock/PartExchangeBlockContent';
import EventTrackerFinanceOverlay from 'dsp2/mixins/EventTrackerFinanceOverlay';

export default Vue.extend({
    mixins: [EventTrackerFinanceOverlay],

    components: {
        appModal,
        appRangeSlider,
        appSelect,
        appPartExchangeBlockContent
    },

    props: {
        financeOptions: {
            required: true,
            type: Object
        },

        show: {
            required: false,
            type: Boolean,
            default: false
        },

        selectedFinanceGroupData: {
            required: true,
            type: Object
        },

        financeSteps: {
            required: true,
            type: Object
        },

        financeGroupsParams: {
            required: true,
            type: Object
        },

        estValueDisclaimer: {
            required: true,
            type: String
        },

        pxRemoved: {
            required: false,
            type: Boolean,
            default: false
        }
    },

    data: () => ({
        annualMileage: 0,
        ajaxLoading: false,
        balloonPercentage: 0,
        groupId: null,
        initialPay: 0,
        isPayInFull: false,
        overlayVideoClass: '',
        openPxModal: false,
        params: {},
        payOver: 0,
        showDeposit: false,
        showInstalments: false,
        showMileage: false,
        showBalloonPercentage: false,
        showPxInfoBlock: true
    }),

    created() {
        this.groupId = this.selectedFinanceGroupData.group_id;
        this.params = jQuery.extend(this.params, this.financeGroupsParams);
    },

    ready() {
        this.setOptions(this.groupId);
        jQuery(this.$el.querySelectorAll('.ffo-header h3')).on('click', () => {
            jQuery(this.$el.querySelectorAll('.ffo-header')).toggleClass('show');
        });

        if (this.pxRemoved
            || !this.showPxInfoBlock
            || !this.FinanceFilter.showPxInfoBlock
            || !this.hasVrm
        ) {
            this.hidePxInfoBlock();
        }
    },

    events: {
        'FinanceFilter::pif-change-popup'() {
            this.params[this.groupId].payinfull = this.$refs.payInFullSliderPopup.active;
        },
        'FinanceFilter::ip-change'(data) {
            this.params[this.groupId].deposit = data;
        },
        'FinanceFilter::po-change'(data) {
            this.params[this.groupId].term = data;
        },
        'FinanceFilter::am-change2'(data) {
            this.params[this.groupId].mileage = data;
        },
        'FinanceFilter::bp-change'(data) {
            this.params[this.groupId].balloonPercentage = data;
        },
        'ConfirmationModal::cancel'() {
            this.openTradeInModal();
        },
        'ConfirmationModal::confirm'() {
            this.openTradeInModal();
        }
    },

    computed: {
        financeGroupOptions() {
            const groups = this.financeOptions.items;

            const groupedGroups = [];

            Object.keys(groups).forEach((key) => {
                groupedGroups.push({
                    title: groups[key].group_full_title,
                    value: groups[key].group_id
                });
            });

            return groupedGroups;
        },

        hasSavedPx() {
            return this.showPxInfoBlock && this.FinanceFilter.showPxInfoBlock && this.hasVrm;
        },

        productTitle() {
            return this.$root.$refs.carFinder.savedPx.cap_extended.product_name;
        },

        savedPxValue() {
            return this.$root.$refs.carFinder.savedPx.part_exchange_value;
        },

        hasVrm() {
            if (this.PartExchange.tempPx
                && this.PartExchange.savedPx
                && this.PartExchangeBlock.tempPx
                && this.PartExchangeBlock.savedPx
            ) {
                return !!this.PartExchange.tempPx.vrm
                    && !!this.PartExchange.savedPx.vrm
                    && !!this.PartExchangeBlock.tempPx.vrm
                    && !!this.PartExchangeBlock.savedPx.vrm;
            }

            return false
        },

        isExpired() {
            return this.PartExchangeFilter.isExpired;
        },

        currentFinanceSteps() {
            return this.financeSteps[this.groupId];
        },

        expireDate() {
            return this.FinanceFilter.expireDate;
        },

        PartExchangeFilter() {
            return this.PartExchange.$refs.partExchangeFilter;
        },

        getVideButtonText() {
            return this.translateString('Watch Video');
        },

        getTradeInButtonText() {
            return this.translateString('Add a trade-in');
        },

        getCtaText() {
            return this.translateString('Continue with payment option');
        },

        FinanceFilter() {
            return this.$root.$refs.financeFilter;
        },

        PartExchange() {
            return this.$root.$refs.partExchange;
        },

        PartExchangeBlock() {
            return this.$root.$refs.carFilter.$refs.partExchangeBlock;
        },

        MobileHeaderText() {
            return this.translateString('Edit Payment Option');
        },

        ContractMileageTitle() {
            return this.translateString('Contract End Mileage');
        }
    },

    watch: {
        'groupId'(newValue) {
            this.isPayInFull = false;
            this.showDeposit = false;
            this.showInstalments = false;
            this.showMileage = false;
            this.showBalloonPercentage = false;

            this.financeOptions.items.forEach((item) => {
                if (item.group_id === newValue) {
                    if (item.balloon_default_step !== null) {
                        this.showBalloonPercentage = true;
                    }

                    if (item.deposit_default_step !== null) {
                        this.showDeposit = true;
                    }

                    if (item.mileage_default_step !== null) {
                        this.showMileage = true;
                    }

                    if (parseInt(item.method_type) === 1) {
                        this.isPayInFull = true;
                    }

                    if (item.term_default_step !== null) {
                        this.showInstalments = true;
                    }
                }
            });
        }
    },

    methods: {
        closeFinanceFilterOverlay() {
            this.$dispatch('FinanceFilterOverlay::closeMoreFiltersOverlay');
        },

        openFinanceFilterOverlay() {
            this.$dispatch('FinanceFilterOverlay::openMoreFiltersOverlay');
        },

        openTradeInModal() {
            this.$dispatch('FinanceFilter::openTradeInModal');
        },

        openTradeInModalPxActive(isExpired) {
            this.$dispatch('FinanceFilter::openTradeInModalPxActive', isExpired);
        },

        selectFinance(data) {
            this.setOptions(data.value);
        },

        videoPopup(open) {
            this.overlayVideoClass = this.overlayVideoClass ? '' : '-show-video';
            this.$refs.financeFilterOverlayVideo.show = open;
        },

        setOptions(groupId) {
            this.groupId = groupId;

            if (this.$refs.initialPaySlider) {
                this.$refs.initialPaySlider.changeSteps(this.currentFinanceSteps.deposit);
                this.$refs.initialPaySlider.changeActive(this.params[this.groupId].deposit);
            }

            if (this.$refs.payOverSlider) {
                this.$refs.payOverSlider.changeSteps(this.currentFinanceSteps.term);
                this.$refs.payOverSlider.changeActive(this.params[this.groupId].term);
            }

            this.$nextTick(() => {
                if (this.$refs.payInFullSliderPopup) {
                    this.$refs.payInFullSliderPopup.changeSteps(this.currentFinanceSteps.payinfull.step);
                    this.$refs.payInFullSliderPopup.changeActive(this.params[this.groupId].payinfull);
                }

                this.setVideoListener();
                this.fireEvent(this.groupId);
            });
        },

        translateString,

        changePayInFullPopup() {
            const minVal = this.currentFinanceSteps.payinfull.min;
            const maxVal = this.currentFinanceSteps.payinfull.max;

            if (this.params[this.groupId].payinfull[0] > this.params[this.groupId].payinfull[1]) {
                this.params[this.groupId].payinfull.$set(0, this.params[this.groupId].payinfull[1]);
            }

            if (this.params[this.groupId].payinfull[0] < minVal) {
                this.params[this.groupId].payinfull.$set(0, minVal);
            }

            if (this.params[this.groupId].payinfull[1] > maxVal) {
                this.params[this.groupId].payinfull.$set(1, maxVal);
            }

            this.areFinanceSlidersChanged = true;
            this.$refs.payInFullSliderPopup.changeActive(this.params[this.groupId].payinfull);
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

            if (typeof this.$refs.initialPaySlider !== 'undefined') {
                this.$refs.initialPaySlider.changeActive(this.params[this.groupId].deposit);
            }

            if (typeof this.$refs.mileageSlider !== 'undefined') {
                this.$refs.mileageSlider.changeActive(this.annualMileage);
            }

            if (typeof this.$refs.payOverSlider !== 'undefined') {
                this.$refs.payOverSlider.changeActive(this.params[this.groupId].term);
            }
        },

        moreFiltersOverlayContinue() {
            const paymentData = {};

            if (this.isPayInFull) {
                paymentData.payinfull = this.$refs.payInFullSliderPopup.active
            }

            if (!this.isPayInFull) {
                if (this.showBalloonPercentage) {
                    paymentData.balloonPercentage = this.params[this.groupId].balloonPercentage;
                }

                if (this.showMileage) {
                    paymentData.mileage = this.params[this.groupId].mileage;
                }

                paymentData.deposit = this.params[this.groupId].deposit;
                paymentData.term = this.params[this.groupId].term;
            }

            this.$store.commit('setFinanceGroupId', this.groupId);
            this.$dispatch('FinanceFilterOverlay::continueWithPayment', this.groupId, paymentData);
        },

        removePX() {
            window.EventsBus.$emit('FinanceFilter::removePxFromTradeInBlock');
        },

        hidePxInfoBlock() {
            this.showPxInfoBlock = false;
        },

        setVideoListener() {
            this.$root.toggleVideoPlayButton();
        },

        /**
         * Fire event for tracking purposes on selected finance choice on Finance Overlay
         */
        fireEvent(financeGroupId) {
            this.setFinanceGroup(financeGroupId);
        }
    }
});
</script>
