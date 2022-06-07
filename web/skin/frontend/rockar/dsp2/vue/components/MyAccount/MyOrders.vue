<template>
    <div class="my-orders desktop">
        <div class="my-orders-wrapper">
            <div class="general-preloader" v-show="ajaxLoading">
                <div class="show-loading"></div>
            </div>
            <div class="my-orders-block car-details">
                <!-- Order details block -->
                <div class="my-order-details">
                    <div class="my-orders-col">
                        <div class="order-images-head">
                            <div class="image-type-switcher">
                                <div class="actions-wrapper">
                                    <div
                                        class="switcher exterior-switch"
                                        :class="{ 'active': !selectInterior }"
                                        @click="selectInterior = false"
                                    ></div>
                                    <div
                                        class="switcher interior-switch"
                                        :class="{ 'active': selectInterior }"
                                        @click="selectInterior = true"
                                    ></div>
                                </div>
                            </div>
                            <div class="order-images-cover">
                                <img :src="car.images.ex" :alt="'Exterior' | translate" v-show="!selectInterior">
                                <img :src="car.images.in" :alt="'Interior' | translate" v-show="selectInterior">
                            </div>
                        </div>
                        <div class="license-plate-wrapper" v-show="car.license_plate">
                            <div class="car-plate" >{{ car.license_plate }}</div>
                        </div>
                    </div>
                    <div class="my-orders-col-2">
                        <div class="order-info-boxes">
                            <div class="order-date order-info-box">
                                <div class="order-label">
                                    {{ 'Order Date' | translate }}
                                </div>
                                <div class="order-info-content">
                                    {{ order.date }}
                                </div>
                            </div>
                            <div class="order-number order-info-box">
                                <div class="order-label">
                                    {{ 'Order Number' | translate }}
                                </div>
                                <div class="order-info-content">
                                    {{ order.increment_id }}
                                </div>
                            </div>
                        </div>

                        <div class="order-info-boxes">
                            <div class="order-collection order-info-box">
                                <div class="order-label">
                                    {{ 'Collection' | translate }}
                                </div>
                                <div class="order-info-content">
                                    {{ order.delivery.store }}
                                </div>
                            </div>
                            <div class="order-delivery order-info-box" v-if="!order.confirmed_delivery_date">
                                <div class="order-label">
                                    {{ order.delivery.type === 'Home Delivery' ? 'Delivery Date' : 'Collection Date' | translate }}
                                </div>
                                <div class="order-info-content">{{ order.delivery_date }}</div>
                            </div>
                            <div class="order-delivery order-info-box" v-if="order.confirmed_delivery_date && order.confirmed_delivery_date.length > 0">
                                <div class="order-label">
                                    {{ order.delivery.type === 'Home Delivery' ? 'Delivery Date' : 'Collection Date' | translate }}
                                </div>
                                <div class="order-info-content">{{ order.confirmed_delivery_date }}</div>
                            </div>
                            <div class="order-delivery order-info-box" v-if="order.confirmed_time && order.confirmed_time.length > 0">
                                <div class="order-label">
                                    {{ order.delivery.type === 'Home Delivery' ? 'Delivery Date' : 'Collection Date' | translate }}
                                </div>
                                <div class="order-info-content">{{ order.confirmed_time }}</div>
                            </div>
                        </div>
                        <div class="my-orders-clear"></div>
                    </div>
                </div>

                <div class="my-orders-col-3">
                    <div class="my-orders-header">
                        <h1 class="my-orders-heading title">{{ carTitle }}</h1>
                        <h4 class="my-orders-heading subtitle">{{ car.bodystyle }}</h4>
                    </div>

                    <div class="my-orders-tabs-section">
                        <div class="my-orders-tabs-list">
                            <div
                                class="my-orders-tab-title"
                                :class="{ 'active' : switchSection === 0 }"
                                @click="switchSection = 0"
                                v-if="carOptions.options"
                            >
                                <p>
                                    {{ 'Car Options' | translate }}
                                </p>
                            </div>
                            <div
                                class="my-orders-tab-title"
                                :class="{ 'active' : switchSection === 1 }"
                                @click="switchSection = 1"
                                v-if="hasPartExchange"
                            >
                                <p>
                                    {{ 'Trade-in' | translate }}
                                </p>
                            </div>
                            <div
                                class="my-orders-tab-title"
                                :class="{ 'active' : switchSection === 2 }"
                                @click="switchSection = 2"
                            >
                                <p>
                                    {{ 'Payment \n Details' | translate }}
                                </p>
                            </div>
                        </div>

                        <div class="my-orders-tabs-content car-extras table-zebra" v-show="switchSection == 0">
                            <div class="row">
                                <template v-if="carOptions.options">
                                    <div class="vehicle-options" v-for="options in carOptions.options">
                                        <div class="header" v-if="options.items && options.items.length">
                                            {{ options.group }}
                                        </div>
                                        <div class="content-box" v-if="options.items && options.items.length">
                                            <div class="content" v-for="option in options.items">
                                                <div class="option-label">{{ option.label | convertNCR }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                </div>
                            </div>
                        </div>

                        <div class="my-orders-tabs-content part-exchange table-zebra" v-show="switchSection == 1">
                            <div class="trade-in" v-if="hasPartExchange">
                                <div class="order-info-box header" v-if="partExchange.car_model">
                                    <div class="car-model">{{ partExchange.car_model }}</div>
                                </div>
                                <div class="order-info-box" v-if="partExchange.license_plate">
                                    <div class="order-label">{{ 'Registration No' | translate }}</div>
                                    <div class="align-right">{{ partExchange.license_plate }}</div>
                                </div>
                                <div class="order-info-box" v-if="partExchange.car_mileage">
                                    <div class="order-label">{{ 'Mileage' | translate }}</div>
                                    <div class="align-right">{{ partExchange.car_mileage | numberKilometerFormat }}</div>
                                </div>
                                <div class="order-info-box" v-if="partExchange.outstanding_finance">
                                    <div class="order-label">{{ 'Outstanding Finance' | translate }}</div>
                                    <div class="align-right">{{ partExchange.outstanding_finance | numberFormat '0,0.00' true }}</div>
                                </div>
                                <div class="order-info-box" v-if="partExchange.part_exchange_value">
                                    <div class="order-label estimated-value">
                                        <span>{{ 'Estimated Value' | translate }}</span>
                                        <app-tooltip tooltip-position="bottom" v-if="estValueDisclaimer">
                                            <p class="icon-info tooltip-valuation" slot="tooltipElement"></p>
                                            <div class="valuation-side-block" slot="tooltipContent" v-html="estValueDisclaimer"></div>
                                        </app-tooltip>
                                    </div>
                                    <div class="align-right estimated-value">{{ partExchange.part_exchange_value | numberFormat '0,0.00' true }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="my-orders-tabs-content" v-show="switchSection == 2">
                            <div class="payment-details" v-if="payment.payInFull === 1">
                                <div class="payment-info-box">
                                    <div class="order-label">{{ 'Offer Price' | translate }}</div>
                                    <div class="align-right">{{ payment.rockar_price | numberFormat '0,0' true }}</div>
                                </div>
                                <div class="payment-info-box">
                                    <div class="order-label">{{ 'Total Cash Price' | translate }}</div>
                                    <div class="align-right">{{ payment.total_amount_payable | numberFormat '0,0.00' true }}</div>
                                </div>
                                <div class="payment-info-box">
                                    <div class="order-label">{{ 'Customer deposit' | translate }}</div>
                                    <div class="align-right">{{ payment.customer_deposit | numberFormat '0,0.00' true }}</div>
                                </div>
                                <div class="payment-info-box summary" v-if="financeParams">
                                    <div class="order-price" v-if="payment.balance_to_finance >= 0">
                                        <div class="order-label">{{ 'Balance to pay' | translate }}</div>
                                        <div class="content">
                                            <div class="align-right">{{ payment.balance_to_finance | numberFormat '0,0.00' true }}</div>
                                        </div>
                                    </div>
                                    <div class="order-price" v-else>
                                        <div class="order-label">{{ 'Cashback to you' | translate }}</div>
                                        <div class="content">
                                            <div class="align-right">{{ payment.balance_to_finance * -1 | numberFormat '0,0.00' true }}</div>
                                        </div>
                                    </div>
                                    <div class="content">
                                        <div class="summary">
                                            <a
                                                href="javascript:void(0);"
                                                class="summary-link"
                                                @click.prevent="showFinance = true"
                                            >
                                                {{ 'Payment Summary' | translate }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="payment-details" v-else>
                                <div class="payment-info-box">
                                    <div class="order-label" v-if="payment.isHire === 1">{{ 'Initial Payment' | translate }}</div>
                                    <div class="order-label" v-else>{{ 'Deposit' | translate }}</div>
                                    <div class="align-right">{{ payment.deposit | numberFormat '0,0.00' true }}</div>
                                </div>
                                <div class="payment-info-box">
                                    <div class="order-label">{{ 'Term' | translate }}</div>
                                    <div class="align-right">{{ payment.monthlyTerm | numberFormat '0,0' false }} {{ 'Months' | translate }}</div>
                                </div>
                                <div class="payment-info-box summary">
                                    <div class="order-price">
                                        <div class="order-label">{{ 'Per Month' | translate }}</div>
                                        <div class="content">
                                            <div class="align-right">{{ payment.monthlyRepayment | numberFormat '0,0' true }}</div>
                                        </div>
                                    </div>
                                    <div class="content">
                                        <div class="summary">
                                            <a
                                                href="javascript:void(0);"
                                                class="summary-link"
                                                @click.prevent="showFinance = true">{{ 'Payment Summary' | translate }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="my-orders-clear"></div>
            </div>
            <div class="my-orders-block my-orders-bordered order-status" v-show="!isSectionHidden('order-statuses')">
                <div class="content">
                    <h4 class="my-orders-heading h-common">{{ 'Order Updates' | translate }}</h4>
                    <div class="row">
                        <div class="col-8">
                            <div class="my-orders-status">
                                <div class="my-orders-progress-bar-block">
                                    <ul class="my-orders-progress-bar-list">
                                        <li
                                            v-for="(index, status) in statuses.list"
                                            class="my-orders-progress-point"
                                            :class="[`col-md-up-${statuses.list.length}`,
                                            !order.is_cancelled && status.active
                                            ? status.id === statuses.active.id ? 'progress' : 'active' : '' ]"
                                        >
                                            <div class="my-orders-progress-point-square">
                                                {{ status.active ?
                                                    status.id === statuses.active.id ? getStatusIndex(status.id) + 1
                                                    : '' : getStatusIndex(status.id) + 1 }}
                                            </div>
                                            <div class="my-orders-progress-text">
                                                <p>{{ status.title }}</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="my-orders-status-text">
                                <p class="my-orders-medium">{{ order.is_cancelled ? 'Cancelled' : statuses.active.title | translate }}</p>
                                <p class="my-orders-p">{{ order.is_cancelled ? 'This order is cancelled' : statuses.active.description | translate }}</p>
                            </div>
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
                <div class="header">
                    <h4 class="my-orders-heading h-common">{{ 'Order Updates' | translate }}</h4>
                </div>
                <div class="my-orders-table col-12">
                    <div class="content">
                        <div class="row">
                            <div class="col-12">
                                <input type="checkbox" id="c1" v-model="accept">
                                <label for="c1"><span></span><p class="accept-terms-statement">{{ 'I accept the' | translate }}</p>
                                    <a
                                        href="javascript:void(0);"
                                        @click.prevent="this.$root.openInModal('termsConditionsModal')"
                                        class="terms-conditions">{{ 'Terms & Conditions' | translate }}
                                    </a>
                                </label>
                                <div class="validation-advice" v-if="!$summary.terms.valid">{{ 'You should accept Terms & Conditions' | translate }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="actions">
                        <button
                            class="button dsp2-money"
                            :class="{ 'disabled': disableAcceptButton }"
                            @click="resolveAmendment(amendmentActions.accept)"
                            :disabled="disableAcceptButton"
                        >
                            {{ 'Confirm changes' | translate }}
                        </button>
                        <button
                            class="button dsp2-outline"
                            :class="{ 'disabled': disableAcceptButton }"
                            @click="resolveAmendment(amendmentActions.reject)"
                            :disabled="disableAcceptButton"
                        >
                            {{ 'Keep my original order' | translate }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="my-orders-block my-orders-document" v-if="order.document && !order.is_amendment">
                <div class="content">
                    <div class="row">
                        <div class="col-3">
                            <h4 class="my-orders-heading h-common">{{ 'Document Name' | translate }}</h4>
                            <p class="my-orders-document-label">{{ order.document.document_name }}</p>
                        </div>
                        <div class="col-3">
                            <h4 class="my-orders-heading h-common">{{ 'File Name' | translate}}</h4>
                            <p class="my-orders-document-label">{{ order.document.file_name }}</p>
                        </div>
                        <div class="col-3">
                            <h4 class="my-orders-heading h-common">{{ 'Date Uploaded' | translate }}</h4>
                            <p class="my-orders-document-label">{{ order.document.creation }}</p>
                        </div>
                        <div class="col-3">
                            <button class="dsp2-outline" :disabled="!order.document.link" @click="downloadDocument(order.document.link)">{{ 'Download' | translate }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-orders-block messages-desktop" v-show="messages.length">
                <div class="my-orders-table col-12">
                    <h4 class="my-orders-heading h-common">{{ 'Messages' | translate }}</h4>
                    <table class="table table-responsive table-borderless">
                        <tbody class="my-orders-messages-block">
                            <tr v-for="message in messages">
                                <td class="my-orders-table-checkbox">
                                    <input
                                        type="checkbox" id="mark-as-read-{{ order.id }}-{{ $index }}"
                                        v-model="messageChecked"
                                        :value="$index"
                                        :checked="message.mark_read === 1"
                                        :disabled="message.mark_read === 1"
                                    />
                                    <label for="mark-as-read-{{ order.id }}-{{ $index }}"><span></span></label>
                                </td>
                                <td class="my-orders-table-content" :class="{ bold:!message.mark_read }">
                                    <div class="content">
                                        {{ message.body }}
                                    </div>
                                    <div class="date">
                                        {{ message.date }}
                                    </div>
                                </td>
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
                <div class="my-orders-header my-orders-block">
                    <div class="header">
                        <h1 class="my-orders-heading title">{{ car.short_description }}</h1>
                        <h4 class="my-orders-heading subtitle">{{ car.bodystyle }}</h4>
                    </div>
                    <div class="image-type-switcher">
                        <div class="actions-wrapper">
                            <div
                                class="switcher exterior-switch"
                                :class="{ 'active': !selectInterior }"
                                @click="selectInterior = false"
                            ></div>
                            <div
                                class="switcher interior-switch"
                                :class="{ 'active': selectInterior }"
                                @click="selectInterior = true"
                            ></div>
                        </div>
                    </div>
                </div>
                <div class="my-orders-block car-details">
                    <div class="my-order-details">
                        <div class="my-orders-col">
                            <div class="order-images-head">
                                <div class="order-images-cover">
                                    <img :src="car.images.ex" :alt="'Exterior' | translate" v-show="!selectInterior">
                                    <img :src="car.images.in" :alt="'Interior' | translate" v-show="selectInterior">
                                </div>
                            </div>
                            <div class="license-plate-wrapper" v-show="car.license_plate">
                                <div class="car-plate" >{{ car.license_plate }}</div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="hero-info">
                    <div class="hero-padding">
                        <div class="hero-order">
                            <div class="order-info-boxes">
                                <div class="order-date order-info-box">
                                    <div class="order-label">
                                        {{ 'Order Date' | translate }}
                                    </div>
                                    <div class="order-info-content">
                                        {{ order.date }}
                                    </div>
                                </div>
                                <div class="order-number order-info-box">
                                    <div class="order-label">
                                        {{ 'Order Number' | translate }}
                                    </div>
                                    <div class="order-info-content">
                                        {{ order.increment_id }}
                                    </div>
                                </div>
                                <div class="order-collection order-info-box">
                                    <div class="order-label">
                                        {{ 'Collection' | translate }}
                                    </div>
                                    <div class="order-info-content">
                                        {{ order.delivery.store }}
                                    </div>
                                </div>
                                <div class="order-delivery order-info-box" v-if="!order.confirmed_delivery_date">
                                    <div class="order-label">
                                        {{ order.delivery.type === 'Home Delivery' ? 'Delivery Date' : 'Collection Date' | translate }}
                                    </div>
                                    <div class="order-info-content">{{ order.delivery_date }}</div>
                                </div>
                                <div class="order-delivery order-info-box" v-if="order.confirmed_delivery_date && order.confirmed_delivery_date.length > 0">
                                    <div class="order-label">
                                        {{ order.delivery.type === 'Home Delivery' ? 'Delivery Date' : 'Collection Date' | translate }}
                                    </div>
                                    <div class="order-info-content">{{ order.confirmed_delivery_date }}</div>
                                </div>
                                <div class="order-delivery order-info-box" v-if="order.confirmed_time && order.confirmed_time.length > 0">
                                    <div class="order-label">
                                        {{ order.delivery.type === 'Home Delivery' ? 'Delivery Date' : 'Collection Date' | translate }}
                                    </div>
                                    <div class="order-info-content">{{ order.confirmed_time }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="my-orders-block my-orders-bordered order-status" v-show="!isSectionHidden('order-statuses')">
                        <div class="content">
                            <h4 class="my-orders-heading h-common">
                                {{ `Order Status: Step ${parseInt(getStatusIndex(statuses.active.id) + 1)} out of ` | translate }}
                                {{ statuses.list.length }}
                            </h4>
                            <div class="row">
                                <div class="col-7">
                                    <div class="my-orders-status">
                                        <div class="my-orders-progress-bar-block">
                                            <ul class="my-orders-progress-bar-list">
                                                <li
                                                    v-for="(index, status) in statusListMobile"
                                                    class="my-orders-progress-point"
                                                    :class="[`col-md-up-${statusListMobile.length}`,
                                                    !order.is_cancelled && status.active
                                                    ? status.id === statuses.active.id ? 'progress' : 'active' : '' ]"
                                                >
                                                    <div class="my-orders-progress-point-square">
                                                        {{ status.active ?
                                                            status.id === statuses.active.id ? getStatusIndex(status.id) + 1
                                                            : '' : getStatusIndex(status.id) + 1 }}
                                                    </div>
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
                    </div>
                    <div class="accordion-list">
                        <app-accordion
                            title="Order updates"
                            class-name="accordion-light"
                            type="right-down"
                            :scroll-on-show="false"
                            v-if="(order.document && !order.is_amendment) || messages.length || order.is_amendment"
                        >
                            <div class="my-orders-block my-orders-document" v-if="order.document && !order.is_amendment">
                                <div class="header">
                                    {{ 'Files' | translate }}
                                </div>
                                <div class="content">
                                    <div class="row">
                                        <div class="col-3">
                                            <h4 class="my-orders-heading h-common">{{ 'Document Name' | translate }}</h4>
                                            <p class="my-orders-document-label">{{ order.document.document_name }}</p>
                                        </div>
                                        <div class="col-3">
                                            <h4 class="my-orders-heading h-common">{{ 'File Name' | translate }}</h4>
                                            <p class="my-orders-document-label">{{ order.document.file_name }}</p>
                                        </div>
                                        <div class="col-3">
                                            <h4 class="my-orders-heading h-common">{{ 'Date Uploaded' | translate }}</h4>
                                            <p class="my-orders-document-label">{{ order.document.creation }}</p>
                                        </div>
                                        <div class="col-3">
                                            <button class="dsp2-outline" :disabled="!order.document.link"
                                                    @click="downloadDocument(order.document.link)">
                                                {{ 'Download' | translate }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="my-orders-block order-amend-actions" v-if="order.is_amendment">
                                <div class="my-orders-table col-12">
                                    <div class="actions">
                                        <div class="row">
                                            <div class="col-12">
                                                <input type="checkbox" id="c2" v-model="accept">
                                                <label for="c2"><span></span><p class="accept-terms-statement">{{ 'I accept the' | translate }}</p>
                                                    <a href="javascript:void(0);"
                                                       @click.prevent="this.$root.openInModal('termsConditionsModal')"
                                                       class="terms-conditions">{{ 'Terms & Conditions' | translate }}
                                                    </a>
                                                </label>
                                                <div class="validation-advice" v-if="!$summary.terms.valid">
                                                    {{ 'You should accept Terms & Conditions' | translate }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="button-group">
                                            <button
                                                class="button dsp2-money"
                                                :class="{ 'disabled': disableAcceptButton }"
                                                @click="resolveAmendment(amendmentActions.accept)"
                                                :disabled="disableAcceptButton"
                                            >
                                                {{ 'Confirm changes' | translate }}
                                            </button>
                                            <button
                                                class="button dsp2-outline"
                                                :class="{ 'disabled': disableAcceptButton }"
                                                @click="resolveAmendment(amendmentActions.reject)"
                                                :disabled="disableAcceptButton"
                                            >
                                                {{ 'Keep my original order' | translate }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="my-orders-block messages-mobile" v-if="messages.length">
                                <div class="my-orders-table col-12">
                                    <h4 class="my-orders-heading h-common">{{ 'Messages' | translate }}</h4>
                                    <table class="table table-responsive table-borderless">
                                        <tbody class="my-orders-messages-block">
                                        <tr v-for="message in messages">
                                            <td class="my-orders-table-checkbox">
                                                <input
                                                    type="checkbox" id="mark-as-read-{{ order.id }}-{{ $index }}"
                                                    v-model="messageChecked"
                                                    :value="$index"
                                                    :checked="message.mark_read === 1"
                                                    :disabled="message.mark_read === 1"
                                                />
                                                <label for="mark-as-read-{{ order.id }}-{{ $index }}"><span></span></label>
                                            </td>
                                            <td class="my-orders-table-content" :class="{ bold:!message.mark_read }">
                                                <div class="content">
                                                    {{ message.body }}
                                                </div>
                                                <div class="date">
                                                    {{ message.date }}
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </app-accordion>

                        <app-accordion
                            title="Extra features"
                            class-name="accordion-light"
                            type="right-down"
                            :scroll-on-show="false"
                        >
                            <div class="my-orders-tabs-content car-extras">
                                <div class="row">
                                    <template v-if="carOptions.options">
                                        <div class="vehicle-options" v-for="options in carOptions.options">
                                            <div class="header" v-if="options.items && options.items.length">
                                                {{ options.group }}
                                            </div>
                                            <div class="content-box" v-if="options.items && options.items.length">
                                                <div class="content" v-for="option in options.items">
                                                    <div class="option-label">{{ option.label | convertNCR }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </app-accordion>

                        <app-accordion
                            title="Trade-in Details"
                            class-name="accordion-light"
                            type="right-down"
                            :scroll-on-show="false"
                            v-if="hasPartExchange"
                        >
                            <div class="my-orders-tabs-content part-exchange">
                                <div class="trade-in" v-if="hasPartExchange">
                                    <div class="order-info-box header" v-if="partExchange.car_model">
                                        <div class="car-model">{{ partExchange.car_model }}</div>
                                    </div>
                                    <div class="order-info-box" v-if="partExchange.license_plate">
                                        <div class="order-label">{{ 'Registration No' | translate }}</div>
                                        <div class="align-right">{{ partExchange.license_plate }}</div>
                                    </div>
                                    <div class="order-info-box" v-if="partExchange.car_mileage">
                                        <div class="order-label">{{ 'Mileage' | translate }}</div>
                                        <div class="align-right">{{ partExchange.car_mileage | numberKilometerFormat }}</div>
                                    </div>
                                    <div class="order-info-box" v-if="partExchange.outstanding_finance">
                                        <div class="order-label">{{ 'Outstanding Finance' | translate }}</div>
                                        <div class="align-right">{{ partExchange.outstanding_finance | numberFormat '0,0.00' true }}</div>
                                    </div>
                                    <div class="order-info-box" v-if="partExchange.part_exchange_value">
                                        <div class="order-label estimated-value">
                                            <span>{{ 'Estimated Value' | translate }}</span>
                                            <app-tooltip tooltip-position="bottom" v-if="estValueDisclaimer">
                                                <p class="icon-info tooltip-valuation" slot="tooltipElement"></p>
                                                <div class="valuation-side-block" slot="tooltipContent" v-html="estValueDisclaimer"></div>
                                            </app-tooltip>
                                        </div>
                                        <div class="align-right estimated-value">{{ partExchange.part_exchange_value | numberFormat '0,0.00' true }}</div>
                                    </div>
                                </div>
                            </div>
                        </app-accordion>

                        <app-accordion
                            title="Payment Details"
                            class-name="accordion-light"
                            type="right-down"
                            :scroll-on-show="false"
                        >
                            <div class="my-orders-tabs-content">
                                <div class="payment-details" v-if="payment.payInFull === 1">
                                    <div class="payment-info-box">
                                        <div class="order-label">{{ 'Offer Price' | translate }}</div>
                                        <div class="align-right">{{ payment.rockar_price | numberFormat '0,0' true }}</div>
                                    </div>
                                    <div class="payment-info-box">
                                        <div class="order-label">{{ 'Total Cash Price' | translate }}</div>
                                        <div class="align-right">{{ payment.total_amount_payable | numberFormat '0,0.00' true }}</div>
                                    </div>
                                    <div class="payment-info-box">
                                        <div class="order-label">{{ 'Customer deposit' | translate }}</div>
                                        <div class="align-right">{{ payment.customer_deposit | numberFormat '0,0.00' true }}</div>
                                    </div>
                                    <div class="payment-info-box" v-if="payment.balance_to_finance >= 0">
                                        <div class="order-label">{{ 'Balance to pay' | translate }}</div>
                                        <div class="align-right">{{ payment.balance_to_finance | numberFormat '0,0.00' true }}</div>
                                    </div>
                                    <div class="payment-info-box" v-else>
                                        <div class="order-label">{{ 'Cashback to you' | translate }}</div>
                                        <div class="align-right">{{ payment.balance_to_finance * -1 | numberFormat '0,0.00' true }}</div>
                                    </div>
                                    <div class="payment-info-box summary" v-if="financeParams">
                                        <div class="order-price" v-if="payment.balance_to_finance >= 0">
                                            <div class="order-label">{{ 'Balance to pay' | translate }}</div>
                                            <div class="content">
                                                <div class="align-right">{{ payment.balance_to_finance | numberFormat '0,0.00' true }}</div>
                                            </div>
                                        </div>
                                        <div class="order-price" v-else>
                                            <div class="order-label">{{ 'Cashback to you' | translate }}</div>
                                            <div class="content">
                                                <div class="align-right">{{ payment.balance_to_finance * -1 | numberFormat '0,0.00' true }}</div>
                                            </div>
                                        </div>
                                        <div class="content">
                                            <div class="summary">
                                                <a
                                                    href="javascript:void(0);"
                                                    class="summary-link"
                                                    @click.prevent="showFinance = true"
                                                >
                                                    {{ 'Payment Summary' | translate }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="payment-details" v-else>
                                    <div class="payment-info-box">
                                        <div class="order-label" v-if="payment.isHire === 1">{{ 'Initial Payment' | translate }}</div>
                                        <div class="order-label" v-else>{{ 'Deposit' | translate }}</div>
                                        <div class="align-right">{{ payment.deposit | numberFormat '0,0.00' true }}</div>
                                    </div>
                                    <div class="payment-info-box">
                                        <div class="order-label">{{ 'Term' | translate }}</div>
                                        <div class="align-right">{{ payment.monthlyTerm | numberFormat '0,0' false }} {{ 'Months' | translate }}</div>
                                    </div>
                                    <div class="payment-info-box summary">
                                        <div class="order-price">
                                            <div class="order-label">{{ 'Per Month' | translate }}</div>
                                            <div class="content">
                                                <div class="align-right">{{ payment.monthlyRepayment | numberFormat '0,0.00' true }}</div>
                                            </div>
                                        </div>
                                        <div class="content">
                                            <div class="summary">
                                                <a
                                                    href="javascript:void(0);"
                                                    class="summary-link"
                                                    @click.prevent="showFinance = true">{{ 'Payment Summary' | translate }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </app-accordion>
                    </div>
                </div>
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

    <app-modal
        testibility-class="summary-close"
        :show.sync="showFinance"
        v-if="showFinance"
        class="payment-details-popup"
        width="80%">
        <div slot="content">
            <app-finance-overlay
                :my-account=true
                :product-id='productId'
                :image='car.images.ex'
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
</template>

<script>
    import coreAppMyOrders from 'core/components/MyAccount/MyOrders';
    import appFinanceOverlay from 'dsp2/components/FinanceOverlay';
    import appAccordion from 'core/components/Accordion';
    import appTooltip from 'dsp2/components/Elements/Tooltip';

    export default coreAppMyOrders.extend({
        props: {
            estValueDisclaimer: {
                required: false,
                type: String,
                default: ''
            },

            payInFullPayment: {
                required: true,
                type: Array
            }
        },

        data() {
            return {
                placeOrder: false,
                keepChanges: false,
                accept: false
            }
        },
        methods: {
            downloadDocument(url) {
                window.open(url, '_blank')
            },

            getStatusIndex(indexId) {
                return this.statuses.list.findIndex((element) => (element.id === indexId));
            }
        },

        components: {
            appFinanceOverlay,
            appAccordion,
            appTooltip
        },

        computed: {
            statusListMobile() {
                const activeIndex = this.getStatusIndex(this.statuses.active.id);

                if (activeIndex === 0) {
                    return this.statuses.list.slice(activeIndex, activeIndex + 3);
                } else if (this.statuses.list.length === activeIndex + 1) {
                    return this.statuses.list.slice(activeIndex - 2);
                } else {
                    return this.statuses.list.slice(activeIndex - 1, activeIndex + 2);
                }
            },

            disableAcceptButton() {
                return !this.accept;
            },

            carTitle() {
                return this.car.short_description;
            }
        }
    });
</script>
