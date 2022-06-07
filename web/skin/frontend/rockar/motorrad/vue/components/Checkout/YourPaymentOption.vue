<template>
    <div id="choose-payment">
        <h2>{{ 'Payment Options' | translate }}</h2>
        <p>{{ 'Tailor your quote and monthly payments using the sliders below. If you want to change the type of finance use the tabs on the left.' | translate }}</p>
        <div class="payment-settings" >
            <div class="payment-settings-block">
                <div class="payment-settings-block-title">
                    <h4>{{ 'Deposit' | translate }}
                        <span>{{ newDeposit | numberFormat '0' false }}</span>
                    </h4>
                </div>
                <app-range-slider :slider-conditions="depositSteps" :show-steps="false" :slider-active="(defaultSteps.deposit !== 0) ? defaultSteps.deposit : depositSteps[0].id" custom-event="YourPaymentOptions::conditionChangeDeposit" custom-event-change="YourPaymentOptions::conditionChangeDepositAjax" ></app-range-slider>
            </div>
            <div class="payment-settings-block">
                <div class="payment-settings-block-title">
                    <h4>{{ 'Terms (Months)' | translate }} <span>{{ newTerm | numberFormat '0' false }}</span></h4>
                </div>
                <app-range-slider :slider-conditions="termSteps" :show-steps="false" :slider-active="(defaultSteps.term !== 0) ? defaultSteps.term : termSteps[0].id" custom-event="YourPaymentOptions::conditionChangeTerm" custom-event-change="YourPaymentOptions::conditionChangeTermAjax" ></app-range-slider>
            </div>
            <div class="payment-settings-block">
                <div class="payment-settings-block-title">
                    <h4>{{ 'Annual Mileage'| translate }}<span>{{ newMileage | numberFormat '0' false }}</span></h4>
                </div>
                <app-range-slider :slider-conditions="mileageSteps" :show-steps="false" :slider-active="(defaultSteps.mileage !== 0) ? defaultSteps.mileage : mileageSteps[0].id" custom-event="YourPaymentOptions::conditionChangeMileage" custom-event-change="YourPaymentOptions::conditionChangeMileageAjax" ></app-range-slider>
            </div>
        </div>
        <div class="px-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
        <div class="payment-methods">
            <div class="payment-methods-tabs">
                <div class="payment-methods-tabs-block" v-for="method in response.options" :class="['tabs-col-' + response.options.length, activeMethod == $index ? 'active' : '']">
                    <div class="payment-methods-tabs-block-body" @click="changeActiveMethod($index)">
                        <h5>{{ method.title }}</h5>
                        <p v-if="method.calc && method.pay_in_full == 0 && method.calc.min_deposit_validation.value == 0">
                            {{ 'More Deposit required' | translate }}
                        </p>
                        <div v-if="method.calc && method.calc.monthly_price && method.pay_in_full == 0 && method.calc.min_deposit_validation.value == 1">
                            <h4>{{ method.calc.monthly_price.value | numberFormat '0,0' true }}</h4>
                            <p>{{ 'a month' | translate }}</p>

                            <p>{{ 'plus' | translate }} {{ method.calc.cashback.value | numberFormat '0,0' true }} {{ 'cashback' | translate }}</p>
                        </div>
                        <div v-if="(!method.calc || !method.calc.monthly_price || method.calc.min_deposit_validation.value == 0) && method.pay_in_full == 0">
                            <h4>{{ sliderInvalidCombinationText | translate }}</h4>
                        </div>
                        <div v-if="method.calc && method.calc.product_price && method.pay_in_full == 1">
                            <h4>{{ method.calc.product_price.value | numberFormat '0,0' true }}</h4>
                            <p>{{ 'plus' | translate }} {{ method.calc.cashback.value | numberFormat '0,0' true }} {{ 'cashback' | translate }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="payment-methods-body">
                <div class="payment-methods-body-block" v-for="method in response.options" v-show="activeMethod == $index">
                    <div class="payment-methods-body-continue">
                        <button @click="openInModal('checkoutPaymentOptions')">{{ 'Select and Continue' | translate }}</button>
                    </div>
                    <div class="text-block">{{{ method.header }}}</div>
                    <table class="table table-responsive table-borderless">
                        <tbody>
                        <tr v-for="item in method.variables">
                            <td>{{ parseVariableTitleMethod(item.variable_title) }}</td>
                            <td v-if="item.value || item.value == 0">{{ item.value_formatted }} </td>
                            <td v-else>{{ sliderInvalidCombinationText | translate }} </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="text-block">{{{ method.video }}}</div>
                    <div class="text-block">{{{ method.footer }}}</div>
                </div>
            </div>
        </div>
    </div>
    <app-modal v-ref:checkout-payment-options class-name="simple-popup">
        <p slot="content">This is basic text modal</p>
    </app-modal>
</template>

<script>
    import appRangeSlider from 'core/components/Elements/RangeSlider';
    import appModal from 'core/components/Elements/Modal';

    export default Vue.extend({
        props: {
            termSteps: {
                required: true,
                type: Array
            },
            productId: {
                required: false,
                type: Number,
                default: -1
            },
            depositSteps: {
                required: true,
                type: Array
            },
            mileageSteps: {
                required: true,
                type: Array
            },
            activeDeposit: {
                required: false,
                type: Number,
                default: 0
            },
            activeMileage: {
                required: false,
                type: Number,
                default: 0
            },
            activeTerm: {
                required: false,
                type: Number,
                default: 0
            },
            newDeposit: {
                required: false,
                type: Number,
                default: 0
            },
            newMileage: {
                required: false,
                type: Number,
                default: 0
            },
            newTerm: {
                required: false,
                type: Number,
                default: 0
            },
            financeUrl: {
                required: true,
                type: String
            },
            defaultSteps: {
                required: true,
                type: Object
            }
        },

        data() {
            return {
                ajaxLoading: false,
                activeMethod: 0,
                response: {},
                sliderInvalidCombinationText: 'Slider combination invalid'
            }
        },

        methods: {
            changeActiveMethod(index) {
                this.activeMethod = index;
                // this.showSlider = parseInt(this.response.options[index].show_slider);
            },

            parseVariableTitleMethod(title) {
                return this.$interpolate(title);
            },

            getDataByProductId() {
                this.ajaxLoading = true;
                this.$http({
                    url: this.financeUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        productId: this.productId,
                        term: this.activeTerm,
                        annualMileage: this.activeMileage,
                        deposit: this.activeDeposit
                    }
                }).then(this.getDataByProductIdSuccess, this.getDataByProductIdFail);
            },

            getDataByProductIdSuccess(resp) {
                this.response = resp.data;
                this.changeActiveMethod(this.activeMethod);
                this.ajaxLoading = false;
            },

            getDataByProductIdFail(error) {
                this.ajaxLoading = false;
                alert(error.statusText);
            }
        },

        events: {
            'YourPaymentOptions::showFinanceOverlay'() {
                this.getDataByProductId();
            },

            'YourPaymentOptions::conditionChangeTerm'(id) {
                this.newTerm = id;
            },

            'YourPaymentOptions::conditionChangeDeposit'(id) {
                this.newDeposit = id;
            },

            'YourPaymentOptions::conditionChangeMileage'(id) {
                this.newMileage = id;
            },

            'YourPaymentOptions::conditionChangeTermAjax'(id) {
                if (id !== this.activeTerm) {
                    this.activeTerm = id;
                    this.getDataByProductId();
                }
            },

            'YourPaymentOptions::conditionChangeDepositAjax'(id) {
                if (id !== this.activeDeposit) {
                    this.activeDeposit = id;
                    this.getDataByProductId();
                }
            },

            'YourPaymentOptions::conditionChangeMileageAjax'(id) {
                if (id !== this.activeMileage) {
                    this.activeMileage = id;
                    this.getDataByProductId();
                }
            }
        },

        ready() {
            this.getDataByProductId();
            this.newDeposit = this.activeDeposit = (this.defaultSteps.deposit !== 0) ? this.defaultSteps.deposit : this.depositSteps[0].id;
            this.newTerm = this.activeTerm = (this.defaultSteps.term !== 0) ? this.defaultSteps.term : this.termSteps[0].id;
            this.newMileage = this.activeMileage = (this.defaultSteps.mileage !== 0) ? this.defaultSteps.mileage : this.mileageSteps[0].id;
        },

        components: {
            appRangeSlider,
            appModal
        }
    });
</script>
