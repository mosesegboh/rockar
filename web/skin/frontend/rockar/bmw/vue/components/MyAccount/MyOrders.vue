<template>
    <div class="my-orders">
        <app-modal testibility-class="summary-close" :show.sync="showFinance" v-if="showFinance" width="80%" >
            <div slot="content">
                <app-finance-overlay
                    :product-id='productId'
                    :finance-url='financeUrl'
                    :finance-params-origin='financeParams'
                    :finance-slider-steps='financeSliderSteps'
                    :pay-in-full-payment='payInFullPayment'
                    :hire-payments='hirePayments'
                    :active-payment='activePayment'
                    :payment-save-url='paymentSaveUrl'
                    :product-url='carPdpUrl'
                    :update-finance-quote='true'
                    :calc-type='calcType'
                    :use-short-version='true'
                    :show-only-active='true'
                    :finance-saved-data='financeSavedData'
                    :skip-ready='true'
                ></app-finance-overlay>
            </div>
        </app-modal>

        <div class="my-orders-wrapper">
            <div class="px-preloader" v-show="ajaxLoading">
                <div class="show-loading"></div>
            </div>
            <div class="my-orders-block car-details">
                <!-- Order details block -->
                <div class="my-orders-col">
                    <div class="order-images-head">
                        <div class="order-images-cover">
                            <img :src="car.exImg" :alt="'Exterior' | translate" v-show="!selectInterior">
                            <img :src="car.inImg" :alt="'Interior' | translate" v-show="selectInterior">
                        </div>
                        <div class="order-images-actions">
                            <button
                                class="button button-narrow button-switcher"
                                :class="[!selectInterior ? 'button-gray-light' : 'button-empty-light']"
                                @click="selectInterior = false"
                            >
                                {{ 'Exterior' | translate }}
                            </button>
                            <button
                                class="button button-narrow button-switcher"
                                :class="[selectInterior ? 'button-gray-light' : 'button-empty-light']"
                                @click="selectInterior = true"
                            >
                                {{ 'Interior' | translate }}
                            </button>
                        </div>
                    </div>
                    <div class="license-plate-wrapper" v-show="car.license_plate">
                        <div class="car-plate" >{{ car.license_plate }}</div>
                    </div>
                </div>
                <div class="my-orders-col-2">
                    <h4 class="my-orders-heading title">{{ car.title }}</h4>
                    <table class="no-margin table-borderless">
                        <tbody>
                            <tr>
                                <td class="order-label">{{ 'Order Date:' | translate }}</td>
                                <td class="align-right">{{ order.date }}</td>
                            </tr>
                            <tr>
                                <td class="order-label">{{ 'Order Number:' | translate }}</td>
                                <td class="align-right">{{ order.increment_id }}</td>
                            </tr>
                            <tr class="delivery">
                                <td class="order-label">{{ 'Collection:' | translate }}</td>
                                <td class="align-right">
                                    <div v-for="(key, item) in order.delivery">
                                        <p v-if="key !== 'code'">{{ item }}</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!order.confirmed_delivery_date">
                                <td class="order-label">{{ 'Collection Date:' | translate }}</td>
                                <td class="align-right">{{ order.delivery_date }}</td>
                            </tr>
                            <tr v-if="order.confirmed_delivery_date && order.confirmed_delivery_date.length > 0">
                                <td class="order-label">{{ 'Collection Date:' | translate }}</td>
                                <td class="align-right">{{ order.confirmed_delivery_date }}</td>
                            </tr>
                            <tr v-if="order.confirmed_time && order.confirmed_time.length > 0">
                                <td class="order-label">{{ 'Confirmed Collection Time:' | translate }}</td>
                                <td class="align-right">{{ order.confirmed_time }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="my-orders-clear"></div>
                </div>

                <div class="my-orders-col-3">
                    <div class="my-orders-tabs-section">
                        <div class="my-orders-tabs-list">
                            <div
                                class="my-orders-tab-title"
                                :class="{ 'active' : switchSection === 0 }"
                                @click="switchSection = 0"
                                v-if="carOptions.options"
                            >
                                {{ 'Car Options' | translate }}
                            </div>
                            <div
                                class="my-orders-tab-title"
                                :class="{ 'active' : switchSection === 1 }"
                                @click="switchSection = 1"
                                v-if="hasPartExchange"
                            >
                                {{ 'Trade-In' | translate }}
                            </div>
                            <div
                                class="my-orders-tab-title"
                                :class="{ 'active' : switchSection === 2 }"
                                @click="switchSection = 2"
                            >
                                {{ 'Payment Details' | translate }}
                            </div>
                        </div>

                        <div class="my-orders-tabs-content car-extras table-zebra" v-show="switchSection == 0">
                            <div class="row">
                                <div class="vehicle-options" v-if="carOptions.options">
                                    <p class="my-orders-bolded">{{ 'Options Added' | translate }}:</p>

                                    <table class="table-borderless no-margin" v-for="options in carOptions.options">
                                        <thead v-if="options.items && options.items.length">
                                            <tr>
                                                <th colspan="2">{{ options.group }}</th>
                                            </tr>
                                        </thead>
                                        <tbody v-if="options.items && options.items.length">
                                            <tr v-for="option in options.items">
                                                <td class="option-label"><span>{{ option.label | convertNCR }}</span></td>
                                                <td class="align-right option-price">{{ option.price | numberFormat '0,0' true }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="my-orders-tabs-content part-exchange table-zebra" v-show="switchSection == 1">
                            <div class="row" v-if="hasPartExchange">
                                <div class="col-6">
                                    <table class="table-borderless no-margin">
                                        <tr v-if="partExchange.license_plate">
                                            <td colspan="2" class="no-padding">
                                                <div class="car-plate no-margin">{{ partExchange.license_plate }}</div>
                                            </td>
                                        </tr>
                                        <tr v-if="partExchange.car_mileage">
                                            <td class="order-label">{{ 'Total Mileage:' | translate }}</td>
                                            <td class="align-right">{{ partExchange.car_mileage | numberFormat '0,0' false }}</td>
                                        </tr>
                                        <tr v-if="partExchange.part_exchange_value">
                                            <td class="order-label">{{ 'Trade-In Value:' | translate }}</td>
                                            <td class="align-right">{{ partExchange.part_exchange_value | numberFormat '0,0.00' true }}</td>
                                        </tr>
                                        <tr v-if="partExchange.outstanding_finance">
                                            <td class="order-label">{{ 'Outstanding Finance:' | translate }}</td>
                                            <td class="align-right">{{ partExchange.outstanding_finance | numberFormat '0,0.00' true }}</td>
                                        </tr>
                                        <tr v-if="partExchange.condition">
                                            <td class="order-label">{{ 'Condition:' | translate }}</td>
                                            <td class="align-right">{{ partExchange.condition }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-6">
                                    <template v-if="hasPartExchangeCheckboxes">
                                        <div v-for="checkbox in partExchange.checkboxes" class="px-additional-info-checkbox">
                                            <div class="checkbox left"></div>
                                            <div class="text right">
                                                {{ checkbox.title }}
                                                <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                                                    <span class="action-badge info-small" slot="tooltipElement"></span>
                                                    <div slot="tooltipContent">
                                                        <p>{{ checkbox.hint }}</p>
                                                    </div>
                                                </app-tooltip>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="my-orders-tabs-content" v-show="switchSection == 2">
                            <table class="table-borderless no-margin" v-if="payment.payInFull === 1">
                                <tbody>
                                    <tr>
                                        <td class="order-label">{{ 'Offer Price' | translate }}:</td>
                                        <td class="align-right">{{ payment.rockar_price | numberFormat '0,0' true }}</td>
                                    </tr>
                                    <tr>
                                        <td class="order-label">{{ 'Total Cash Price' | translate }}:</td>
                                        <td class="align-right">{{ payment.total_amount_payable | numberFormat '0,0.00' true }}</td>
                                    </tr>
                                    <tr>
                                        <td class="order-label">{{ 'Customer deposit' | translate }}:</td>
                                        <td class="align-right">{{ payment.customer_deposit | numberFormat '0,0.00' true }}</td>
                                    </tr>
                                    <tr v-if="payment.balance_to_finance >= 0">
                                        <td class="order-label">{{ 'Balance to pay' | translate }}:</td>
                                        <td class="align-right">{{ payment.balance_to_finance | numberFormat '0,0.00' true }}</td>
                                    </tr>
                                    <tr v-else>
                                        <td class="order-label">{{ 'Cashback to you' | translate }}:</td>
                                        <td class="align-right">{{ payment.balance_to_finance * -1 | numberFormat '0,0.00' true }}</td>
                                    </tr>
                                    <tr v-if="financeParams">
                                        <td colspan="2" class="align-right no-padding">
                                            <a
                                                href="javascript:void(0);"
                                                class="summary-link"
                                                @click.prevent="showFinance = true"
                                            >
                                                {{ 'View Full Summary' | translate }}
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table-borderless no-margin" v-else>
                                <tbody>
                                <tr>
                                    <td class="order-label" v-if="payment.isHire === 1">{{ 'Initial Payment' | translate }}:</td>
                                    <td class="order-label" v-else>{{ 'Deposit' | translate }}:</td>
                                    <td class="align-right">{{ payment.deposit | numberFormat '0,0.00' true }}</td>
                                </tr>
                                <tr>
                                    <td class="order-label">{{ 'Monthly Term' | translate }}:</td>
                                    <td class="align-right">{{ payment.monthlyTerm | numberFormat '0,0' false }}</td>
                                </tr>
                                <tr>
                                    <td class="order-label">{{ 'Monthly Payment' | translate }}:</td>
                                    <td class="align-right">{{ payment.monthlyRepayment | numberFormat '0,0.00' true }}</td>
                                </tr>
                                <tr v-if="financeParams">
                                    <td colspan="2" class="align-right no-padding">
                                        <a
                                            href="javascript:void(0);"
                                            class="summary-link"
                                            @click.prevent="showFinance = true">{{ 'View Full Summary' | translate }}
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="my-orders-clear"></div>
            </div>
            <div class="my-orders-block my-orders-bordered" v-show="!isSectionHidden('order-statuses')">
                <h4 class="my-orders-heading h-common">{{ 'Order Status' | translate }}</h4>
                <div class="row">
                    <div class="col-7">
                        <div class="my-orders-status">
                            <div class="my-orders-progress-bar-block">
                                <ul class="my-orders-progress-bar-list">
                                    <li
                                        v-for="status in statuses.list"
                                        class="my-orders-progress-point"
                                        :class="['col-md-up-' + statuses.list.length, !order.is_cancelled && status.active ? 'active' : '' ]"
                                    >
                                        <div class="my-orders-progress-point-square"></div>
                                        <div class="my-orders-progress-text">
                                            <p>{{ status.title }}</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="my-orders-status-text">
                            <p class="my-orders-medium">{{ order.is_cancelled ? 'Cancelled' : statuses.active.title | translate }}</p>
                            <p class="my-orders-p">{{ order.is_cancelled ? 'This order is cancelled' : statuses.active.description | translate }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-orders-block" v-show="nextSteps.steps.length && !isSectionHidden('next-steps')">
                <div class="my-orders-table col-12">
                    <h4 class="my-orders-heading h-common">{{ 'Your Next Steps' | translate }}</h4>
                    <table class="table table-responsive table-borderless table-zebra">
                        <tbody class="my-orders-steps-block">
                            <tr v-for="step in nextSteps.steps">
                                <td>
                                    <span class="nextstep-title">{{ step.title }}</span>
                                    <a v-if="step.download_url && !isInStore" :href="step.download_url" class="attachment-download"><i class="attachment-icon"></i></a>
                                    <span v-show="step.description_tooltip">
                                        <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                                            <span class="action-badge info-small" slot="tooltipElement"></span>
                                            <span slot="tooltipContent">
                                                <p>{{ step.description_tooltip }}</p>
                                            </span>
                                        </app-tooltip>
                                    </span>
                                </td>
                                <td class="nextstep-action">
                                    <span class="nextstep-checkbox right" :class="{ checked: (step.checked == 0 ? 0 : 1) }" @click="submitStep($index)"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="my-orders-block order-amend-actions" v-if="order.is_amendment">
                <div class="my-orders-table col-12">
                    <h4 class="my-orders-heading h-common">{{ 'Order status' | translate }}</h4>
                    <div class="row">
                        <div class="col-12">
                            <input type="checkbox" id="c1" v-model="accept" v-validate:terms="['required']" initial="off">
                            <label for="c1"><span></span><p class="accept-terms-statement">{{ 'I accept the' | translate }}</p>
                                <a
                                   href="javascript:void(0);"
                                   @click.prevent="this.$root.openInModal('termsConditionsModal')"
                                   class="terms-conditions">{{'Terms & Conditions' | translate}}
                                </a>
                            </label>
                            <div class="validation-advice" v-if="!$summary.terms.valid">{{ 'You should accept Terms & Conditions' | translate }}</div>
                        </div>
                    </div>
                    <div class="actions">
                        <button
                            class="confirm button"
                            @click="resolveAmendment(amendmentActions.accept)"
                        >
                            {{ 'Confirm changes' | translate }}
                        </button>
                        <button
                            class="reject button button-empty"
                            @click="resolveAmendment(amendmentActions.reject)"
                        >
                            {{ 'Keep my original order' | translate }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="my-orders-block my-orders-document" v-if="order.document && !order.is_amendment">
                <div class="row">
                    <div class="col-3">
                        <h4 class="my-orders-heading h-common">Document Name</h4>
                    </div>
                    <div class="col-3">
                        <h4 class="my-orders-heading h-common">File Name</h4>
                    </div>
                    <div class="col-3">
                        <h4 class="my-orders-heading h-common">Date Uploaded</h4>
                    </div>
                </div>
                <!--This is a place holder for the dynamic data to come from the backend-->
                <div class="row">
                    <div class="col-3">
                        <p class="my-orders-document-label">{{ order.document.document_name }}</p>
                    </div>
                    <div class="col-3">
                        <p class="my-orders-document-label">{{ order.document.file_name }}</p>
                    </div>
                    <div class="col-3">
                        <p class="my-orders-document-label">{{ order.document.creation }}</p>
                    </div>
                    <div class="col-3">
                        <button :disabled="!order.document.link" @click="downloadDocument(order.document.link)">Download</button>
                    </div>
                </div>
            </div>

            <div class="my-orders-block" v-show="messages.length">
                <div class="my-orders-table col-12">
                    <h4 class="my-orders-heading h-common">{{ 'Messages' | translate }}</h4>
                    <table class="table table-responsive table-borderless">
                        <tbody class="my-orders-messages-block">
                            <tr v-for="message in messages">
                                <td class="my-orders-table-checkbox">
                                    <input
                                        type="checkbox" id="mark-as-read-{{ $index }}"
                                        v-model="messageChecked"
                                        :value="$index" :checked="(message.mark_read == 1 ? true : false)"
                                        :disabled="(message.mark_read == 1 ? true : false)"
                                    />
                                    <label for="mark-as-read-{{ $index }}"><span></span></label>
                                </td>
                                <td class="my-orders-table-date" :class="{bold:(!message.mark_read == 1 ? true : false)}">{{ message.date }}</td>
                                <td class="my-orders-table-text" :class="{bold:(!message.mark_read == 1 ? true : false)}">{{{ message.body }}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="my-orders-clear"></div>
        </div>

        <div class="my-orders-mobile">
            <div class="px-preloader" v-show="ajaxLoading">
                <div class="show-loading"></div>
            </div>
            <div class="hero-section">
                <div class="order-images-head">
                    <div class="order-images-cover">
                        <img :src="car.exImg" :alt="'Exterior' | translate" v-show="!selectInterior">
                        <img :src="car.inImg" :alt="'Interior' | translate" v-show="selectInterior">
                    </div>
                    <div class="order-images-actions">
                        <button
                            class="button button-narrow button-switcher"
                            :class="[!selectInterior ? 'button-gray-light' : 'button-empty-light']"
                            @click="selectInterior = false"
                        >
                            {{ 'Exterior' | translate }}
                        </button>
                        <button
                            class="button button-narrow button-switcher"
                            :class="[selectInterior ? 'button-gray-light' : 'button-empty-light']" @click="selectInterior = true"
                        >
                            {{ 'Interior' | translate }}
                        </button>
                    </div>
                </div>
                <div class="hero-info">
                    <div class="hero-padding">
                        <div class="hero-title">
                            {{ car.title }}
                        </div>

                        <div class="hero-order">
                            <div class="hero-order-block upper">
                                {{ 'Order Date:' | translate }}
                            </div>
                            <div class="hero-order-block">
                                {{ order.date }}
                            </div>
                            <div class="hero-order-block upper">
                                {{ 'Order Number:' | translate }}
                            </div>
                            <div class="hero-order-block">
                                {{ order.increment_id }}
                            </div>
                            <div class="hero-order-block upper">
                                {{ 'Collection:' | translate }}
                            </div>
                            <div class="hero-order-block">
                                <template v-for="(key, item) in order.delivery">
                                    <div v-if="key !== 'code'">{{ item }}</div>
                                </template>
                            </div>
                            <template v-if="!order.confirmed_delivery_date">
                                <div class="hero-order-block upper">
                                    {{ 'Collection Date:' | translate }}
                                </div>
                                <div class="hero-order-block">
                                    {{ order.delivery_date }}
                                </div>
                            </template>
                            <template v-if="order.confirmed_delivery_date && order.confirmed_delivery_date.length > 0">
                                <div class="hero-order-block upper">
                                    {{ 'Collection Date:' | translate }}
                                </div>
                                <div class="hero-order-block">
                                    {{ order.confirmed_delivery_date }}
                                </div>
                            </template>
                            <template v-if="order.confirmed_time && order.confirmed_time.length > 0">
                                <div class="hero-order-block upper">
                                    {{ 'Confirmed Collection Time:' | translate }}
                                </div>
                                <div class="hero-order-block">
                                    {{ order.confirmed_time }}
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="hero-tabs">
                        <div class="hero-tabs-list">
                            <div
                                class="hero-tab"
                                :class="{ 'active' : switchSection == 0 }"
                                @click="switchSection = 0"
                                v-if="carOptions.options"
                            >
                                {{ 'Car Options' | translate }}
                            </div>
                            <div
                                class="hero-tab"
                                :class="{ 'active' : switchSection == 1 }"
                                @click="switchSection = 1"
                                v-if="hasPartExchange"
                            >
                                {{ 'Trade-In' | translate }}
                            </div>
                            <div
                                class="hero-tab"
                                :class="{ 'active' : switchSection == 2 }"
                                @click="switchSection = 2"
                            >
                                {{ 'Payment Details' | translate }}
                            </div>
                        </div>

                        <div class="hero-content" v-show="switchSection == 0">
                            <div class="row" v-if="carOptions.options">
                                <p class="my-orders-bolded">{{ 'Options Added' | translate }}:</p>
                                <div class="col-6">
                                    <template v-for="options in carOptions.options">
                                        <template v-if="options.items && options.items.length">
                                            <p class="option-group-title">{{ options.group }}:</p>
                                            <table class="table-borderless no-margin">
                                                <tr v-for="option in options.items">
                                                    <td>{{ option.label | convertNCR }}</td>
                                                    <td class="align-right option-price">{{ option.price | numberFormat '0,0' true }}</td>
                                                </tr>
                                            </table>
                                        </template>
                                    </template>
                                </div>
                            </div>
                            <p v-if="!carOptions">{{ 'There are no options' | translate }}</p>
                        </div>

                        <div class="hero-content" v-show="switchSection == 1">
                            <div class="hero-payment" v-if="hasPartExchange">
                                <div class="hero-payment-section">
                                    <div v-if="partExchange.license_plate" class="hero-payment-block">
                                        <p class="payment-amount">{{ partExchange.license_plate }}</p>
                                        <p class="payment-label">{{ 'VRM' | translate }}</p>
                                    </div>
                                    <div v-if="partExchange.car_mileage" class="hero-payment-block">
                                        <p class="payment-amount">{{ partExchange.car_mileage | numberFormat '0,0' false }}</p>
                                        <p class="payment-label">{{ 'Mileage' | translate }}</p>
                                    </div>
                                    <div v-if="partExchange.part_exchange_value" class="hero-payment-block">
                                        <p class="payment-amount">{{ partExchange.part_exchange_value | numberFormat '0,0.00' true }}</p>
                                        <p class="payment-label">{{ 'Value' | translate }}</p>
                                    </div>
                                    <div v-if="partExchange.condition" class="hero-payment-block">
                                        <p class="payment-amount">{{ partExchange.condition }}</p>
                                        <p class="payment-label">{{ 'Car Condition' | translate }}</p>
                                    </div>
                                </div>
                                <div class="px-additional-info-block">
                                    <template v-if="hasPartExchangeCheckboxes">
                                        <div v-for="checkbox in partExchange.checkboxes" class="px-additional-info-checkbox">
                                            <div class="checkbox left"></div>
                                            <div class="text right">
                                                {{ checkbox.title }}
                                                <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                                                    <span class="action-badge info-small" slot="tooltipElement"></span>
                                                    <div slot="tooltipContent">
                                                        <p>{{ checkbox.hint }}</p>
                                                    </div>
                                                </app-tooltip>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="hero-content" v-show="switchSection == 2">
                            <div class="hero-payment">
                                <div class="hero-payment-section" v-if="payment.payInFull === 1">
                                    <div class="hero-payment-block">
                                        <p class="payment-amount">{{ payment.rockar_price | numberFormat '0,0' true }}</p>
                                        <p class="payment-label">{{ 'Offer Cash Price' | translate }}</p>
                                    </div>
                                    <div class="hero-payment-block">
                                        <p class="payment-amount">{{ payment.total_amount_payable | numberFormat '0,0.00' true }}</p>
                                        <p class="payment-label">{{ 'Total Cash Price' | translate }}</p>
                                    </div>
                                    <div class="hero-payment-block">
                                        <p class="payment-amount">{{ payment.customer_deposit | numberFormat '0,0.00' true }}</p>
                                        <p class="payment-label">{{ 'Customer Deposit' | translate }}</p>
                                    </div>
                                    <div class="hero-payment-block" v-if="payment.balance_to_finance >= 0">
                                        <p class="payment-amount">{{ payment.balance_to_finance | numberFormat '0,0.00' true }}</p>
                                        <p class="payment-label">{{ 'Balance to pay' | translate }}</p>
                                    </div>
                                    <div class="hero-payment-block" v-else>
                                        <p class="payment-amount">{{ payment.balance_to_finance * -1 | numberFormat '0,0.00' true }}</p>
                                        <p class="payment-label">{{ 'Cashback to you' | translate }}</p>
                                    </div>
                                </div>
                                <div class="hero-payment-section" v-else>
                                    <div class="hero-payment-block">
                                        <p class="payment-amount">{{ payment.deposit | numberFormat '0,0.00' true }}</p>
                                        <p class="payment-label" v-if="payment.isHire === 1">{{ 'Initial Payment' | translate }}</p>
                                        <p class="payment-label" v-else>{{ 'Deposit' | translate }}</p>
                                    </div>
                                    <div class="hero-payment-block">
                                        <p class="payment-amount">{{ payment.monthlyTerm }}</p>
                                        <p class="payment-label">{{ 'Term' | translate }}</p>
                                    </div>
                                    <div class="hero-payment-block">
                                        <p class="payment-amount">{{ payment.monthlyRepayment | numberFormat '0,0.00' true }}</p>
                                        <p class="payment-label">{{ 'Repayment' | translate }}</p>
                                    </div>
                                    <div class="hero-payment-block">
                                        <p class="payment-amount">{{ payment.mileage | numberFormat '0,0' false }}</p>
                                        <p class="payment-label">{{ 'Mileage' | translate }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-orders-block my-orders-bordered" v-show="!isSectionHidden('order-statuses')">
                <h4 class="my-orders-heading">{{ statuses.active.title }}</h4>
                <h4 class="my-orders-subheading">{{ statuses.active.description }}</h4>
                <div class="row">
                    <div class="col-7 col-md-12">
                        <div class="my-orders-status">
                            <div class="my-orders-progress-bar-block">
                                <ul class="my-orders-progress-bar-list" :class="[ 'steps-' + statuses.list.length ]">
                                    <li v-for="status in statuses.list" class="my-orders-progress-point col-md-up-6" :class="{ active: status.active }">
                                        <div class="my-orders-progress-point-square"></div>
                                        <div class="my-orders-progress-text">
                                            <p>{{ status.title }}</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-orders-table my-orders-next-steps" v-show="nextSteps.steps.length && !isSectionHidden('next-steps')">
                <h4 class="my-orders-heading">{{ 'Your Next Steps' | translate }}</h4>
                <table class="table table-borderless table-zebra">
                    <tbody class="my-orders-steps-block">
                        <tr v-for="step in nextSteps.steps">
                            <td :class="{ checked: (step.checked == 0 ? 0 : 1) }">
                                <span class="nextstep-title">{{ step.title }}</span>
                                <a v-if="step.download_url && !isInStore" :href="step.download_url" class="attachment-download"><i class="attachment-icon"></i></a>
                            </td>
                            <td class="nextstep-action">
                                <span class="nextstep-checkbox right" :class="{ checked: (step.checked == 0 ? 0 : 1) }" @click="submitStep($index)"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="my-orders-block order-amend-actions" v-if="order.is_amendment">
                <div class="my-orders-table col-12">
                    <h4 class="my-orders-heading">{{ 'Order status' | translate }}</h4>
                    <div class="actions">
                        <div class="row">
                            <div class="col-12">
                                <input type="checkbox" id="c2" v-model="accept" v-validate:terms="['required']" initial="off">
                                <label for="c2"><span></span><p class="accept-terms-statement">{{ 'I accept the' | translate }}</p>
                                    <a href="javascript:void(0);"
                                       @click.prevent="this.$root.openInModal('termsConditionsModal')"
                                       class="terms-conditions">{{'Terms & Conditions' | translate}}
                                    </a>
                                </label>
                                <div class="validation-advice" v-if="!$summary.terms.valid">{{ 'You should accept Terms & Conditions' | translate }}</div>
                            </div>
                        </div>
                        <button
                            class="confirm button"
                            @click="resolveAmendment(amendmentActions.accept)"
                        >
                            {{ 'Confirm changes' | translate }}
                        </button>
                        <button
                            class="reject button button-empty"
                            @click="resolveAmendment(amendmentActions.reject)"
                        >
                            {{ 'Keep my original order' | translate }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="my-orders-block my-orders-document" v-if="order.document">
                <div class="row">
                    <div class="col-3">
                        <h4 class="my-orders-heading h-common">Document Name</h4>
                    </div>
                    <div class="col-3">
                        <p class="my-orders-document-label">{{ order.document.document_name }}</p>
                    </div>
                    <div class="col-3">
                        <h4 class="my-orders-heading h-common">File Name</h4>
                    </div>
                    <div class="col-3">
                        <p class="my-orders-document-label">{{ order.document.file_name }}</p>
                    </div>
                    <div class="col-3">
                        <h4 class="my-orders-heading h-common">Date Uploaded</h4>
                    </div>
                    <div class="col-3">
                        <p class="my-orders-document-label">{{ order.document.creation }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <button :disabled="!order.document.link" @click="downloadDocument(order.document.link)">Download</button>
                    </div>
                </div>
            </div>

            <div class="my-orders-table my-orders-messages" v-show="messages.length">
                <h4 class="my-orders-heading">{{ 'Messages' | translate }}</h4>
                <table class="table table-borderless">
                    <tbody class="my-orders-messages-block">
                        <tr v-for="message in messages">
                            <td class="my-orders-table-checkbox">
                                <input
                                    type="checkbox" id="mark-as-read-{{ $index }}"
                                    v-model="messageChecked"
                                    :value="$index" :checked="(message.mark_read == 1 ? true : false)"
                                    :disabled="(message.mark_read == 1 ? true : false)"
                                />
                                <label for="mark-as-read-{{ $index }}"><span></span></label>
                            </td>
                            <td class="my-orders-table-date" :class="{bold:(!message.mark_read == 1 ? true : false)}">{{ message.date }}</td>
                            <td class="my-orders-table-text" :class="{bold:(!message.mark_read == 1 ? true : false)}">{{{ message.body }}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!--Success place order modal-->
            <app-modal :show.sync="placeOrder" :show-close="true" width="80%">
                <div slot="content">
                    <p class="order-confirm-subtitle">{{ 'Your order is complete.' | translate }}</p>

                    <div class="row">
                        <img :src="car.exImg" :alt="'Exterior' | translate" v-if="!selectInterior">
                        <img :src="car.inImg" :alt="'Interior' | translate" v-else="">
                    </div>
                    <p class="order-confirm-subtitle">{{ 'What happens now?' | translate }}</p>
                    <div class="order-confirmation-body">
                        <p class="no-margin">{{ 'Estimated collection date' | translate }}:
                            <strong>{{ order.delivery_date }}</strong>
                            <span v-if="order.deliveryStore">({{ order.deliveryStore }})</span>
                        </p>
                        <p>{{ 'We will contact you closer to this date to confirm exact details' | translate }}</p>
                        <p>{{ 'Your Order Number' | translate }}: <strong>{{ order.increment_id }}</strong></p>
                        <p>{{ 'We will contact you regarding your order details at any time within My Profile.' | translate }}</p>
                        <a class="button button-empty">{{ 'Continue' | translate }}</a>
                    </div>
                </div>
            </app-modal>

            <!--Keep changes modal-->
            <app-modal :show.sync="keepChanges" :show-close="true" width="80%">
                <div slot="content">
                    <p class="order-confirm-subtitle">{{ 'Your order is on hold.' | translate }}</p>

                    <div class="order-confirmation-body">
                        <p>{{ 'Please visit your preferred Retailer to amend your order status.' | translate }}</p>
                        <a class="button button-empty">{{ 'Continue' | translate }}</a>
                    </div>
                </div>
            </app-modal>
        </div>
    </div>
</template>

<script>
    import coreAppMyOrders from 'core/components/MyAccount/MyOrders';
    import appFinanceOverlay from 'bmw/components/FinanceOverlay';

    export default coreAppMyOrders.extend({
        props: {
            payInFullPayment: {
                required: true,
                type: Array
            }
        },

        data() {
            return {
                placeOrder: false,
                keepChanges: false
            }
        },
        methods: {
            downloadDocument(url) {
                window.open(url, '_blank')
            }
        },

        components: {
            appFinanceOverlay
        }
    });
</script>
