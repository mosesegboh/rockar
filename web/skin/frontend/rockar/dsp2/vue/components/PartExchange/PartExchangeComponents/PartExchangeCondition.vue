<template>
    <div>
        <div class="condition-wrapper" :class="{ active: pxAccordion }">
            <div class="row">
                <div class="col-6 col-md-6 select-condition">
                    <template v-if="pxAccordion">
                        <div class="px-mileage">
                            <div class="input-label-wrapper">
                                <input
                                    type="text"
                                    @focus="selectMileage"
                                    @blur="deselectMileage"
                                    id="td-mileage-cond"
                                    v-model="mileage | numberKilometerFormatPx"
                                    :value="savedMileage"
                                >
                                <label
                                    class="input-label"
                                    for="td-mileage-cond"
                                >
                                    {{ 'Current Mileage (Km)*' | translate }}
                                </label>
                            </div>
                        </div>
                    </template>
                    <p class="dsp2-label">{{ 'Your vehicle condition' | translate }}</p>
                    <div class="part-exchange-conditions">
                        <app-range-slider
                            v-if="activeSliderCondition"
                            :use-id="true"
                            :active-on-slide="true"
                            :options="carConditions"
                            :display-steps="true"
                            :active="activeSliderCondition"
                            custom-event="PartExchange::changeCondition"
                            custom-event-change="PartExchange::changeCondition"
                            v-ref:condition-slider
                        ></app-range-slider>

                        <ul class="conditions-labels" :class="['conditions-' + this.carConditions.length]">
                            <li v-for="condition in carConditions">
                                <span>{{ condition.title | capitalize }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="condition-info">
                        <template v-for="condition in carConditions">
                            <div class="item" v-show="activeCondition === condition.id">
                                <p class="title" slot="tooltipElement">{{ condition.title | capitalize }}</p>
                                <p class="description" v-html="condition.body"></p>
                                <app-more-info :disable="false" class="condition-more-info">
                                    <p slot="content">{{{ htmlEntityDecode(condition.hint) }}}</p>
                                </app-more-info>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="col-6 col-md-5">
                    <div class="px-additional-info-block">
                        <div class="px-additional-info-checkbox" v-for="info in additionalInfo">
                            <div class="px-additional-info-list">
                                <input
                                    type="checkbox"
                                    :id="`info-checkbox${info.id}`"
                                    v-model="info.checked"
                                >
                                <label :for="`info-checkbox${info.id}`" ><span></span></label>

                                <div class="info-label-wrap">
                                    <label :for="`info-checkbox${info.id}`">
                                        {{ info.title }}
                                        <span v-text="info.error.length ? '*' : ''"></span>
                                    </label>
                                </div>
                            </div>
                            <p class="dsp2-caption red-2" v-if="info.error.length > 0 && !info.checked">
                                {{ info.error }}
                            </p>
                        </div>

                        <template v-if="!valuationResult && isExpired && pxAccordion">
                            {{{ valuationText }}}
                        </template>

                        <app-part-exchange-condition-buttons
                            @save="saveValuation"
                            @next="nextStep(val)"
                            :is-expired="isExpired"
                            :valuation-result="valuationResult"
                            :part-exchange-is-valid="partExchangeIsValid"
                            :part-exchange-second-step="partExchangeSecondStep"
                            :px-class="`col-12`"
                            v-if="(pxAccordion && !valuationResult) || (isExpired && valuationResult && pxAccordion)"
                        >
                        </app-part-exchange-condition-buttons>
                    </div>

                    <template v-if="!valuationResult && isExpired && !pxAccordion">
                        {{{ valuationText }}}
                    </template>

                </div>
            </div>
        </div>

        <template v-if="!pxAccordion">
            <hr/>
            <app-part-exchange-condition-buttons
                @save="saveValuation"
                @next="nextStep(val)"
                :is-expired="isExpired"
                :valuation-result="valuationResult"
                :part-exchange-is-valid="partExchangeIsValid"
                :part-exchange-second-step="partExchangeSecondStep"
                :px-class="`col-6 col-md-12`"
            >
            </app-part-exchange-condition-buttons>
        </template>
    </div>
</template>

<script>
    import appMoreInfo from 'dsp2/components/Elements/MoreInfo';
    import appRangeSlider from 'core/components/Elements/RangeSlider';
    import translateString from 'core/filters/Translate';
    import appPartExchangeConditionButtons from 'dsp2/components/PartExchange/PartExchangeComponents/PartExchangeConditionButtons';

    export default Vue.extend({
        data: () => ({
            mileage: 0
        }),

        props: {
            carConditions: {
                type: Array,
                required: false,
                default: []
            },

            additionalInfo: {
                required: true,
                type: Array
            },

            activeCondition: {
                required: true,
                type: Number
            },

            valuationResult: {
                required: true,
                type: Boolean
            },

            isExpired: {
                required: false,
                type: Boolean,
                default: false
            },

            partExchangeIsValid: {
                required: true,
                type: Boolean
            },

            partExchangeSecondStep: {
                required: true,
                type: Boolean
            },

            pxAccordion: {
                required: false,
                type: Boolean,
                default: false
            },

            savedMileage: {
                required: false,
                type: Number,
                default: 0
            },

            hasNegativeEquity: {
                required: false,
                type: [Boolean, Number],
                default: false
            },

            isPayInFull: {
                required: false,
                type: Boolean,
                default: false
            }
        },

        methods: {
            translateString,

            htmlEntityDecode(encodedHtml) {
                return jQuery('<textarea />').html(encodedHtml).text();
            },

            saveValuation() {
                this.$emit('save');
            },

            nextStep(val) {
                this.$emit('next', val);
            },

            selectMileage() {
                this.$emit('select');
            },

            deselectMileage() {
                this.$emit('deselect');
            },

            setValuationResult() {
                this.valuationResult = false;
            },

            skipPXWithOutPX() {
                this.$emit('remove');
            }
        },

        computed: {
            activeSliderCondition() {
                let activeCondition;

                if (!this.activeCondition) {
                    this.carConditions.forEach((condition, index) => {
                        if (condition.is_default) {
                            activeCondition = condition.id;

                            return index;
                        }
                    });
                } else {
                    activeCondition = this.activeCondition;
                }

                this.activeCondition = activeCondition;

                return activeCondition;
            },

            valuationText() {
                return `<p class="red-2">
                        ${this.translateString('The value of your car has changed. Update your valuation.')}
                        </p>`;
            }
        },

        watch: {
            mileage(newVal, oldVal) {
                if (oldVal) {
                    this.setValuationResult();
                }
            },

            'additionalInfo': {
                handler(newVal) {
                    if (this.pxAccordion && newVal) {
                        this.setValuationResult();
                    }
                },
                deep: true
            },

            'valuationResult': {
                handler() {
                    if (this.pxAccordion) {
                        this.partExchangeSecondStep = true;
                    }
                }
            }
        },

        components: {
            appMoreInfo,
            appRangeSlider,
            appPartExchangeConditionButtons
        }
    })
</script>
