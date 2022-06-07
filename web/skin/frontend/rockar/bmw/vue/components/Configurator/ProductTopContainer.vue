<template>
    <div class="page-title-wrapper">
        <div class="page-title-content new-car-actions">
            <div class="heading-container">
                <div class="product-headings">
                    <div class="main-heading">{{ product.title }}</div>
                    <div class="sub-heading">{{ product.short_title }}</div>
                </div>
            </div>
            <div class="product-actions">
                <slot name="productActions"></slot>
                <div class="share-product">
                    <app-save-car
                        v-if="vehicle.isInWishlist !== undefined"
                        button-classes="button button-empty-dark"
                        :customer-name="product.customerName"
                        :product-name="product.title"
                        :product-title="product.name"
                        :product-subtitle="product.short_title"
                        :product-sku="product.sku"
                        :product-price="rockarPrice"
                        :save-wishlist-url="vehicle.saveWishlistUrl"
                        :my-account-url="product.myAccountUrl"
                        :is-in-wishlist="vehicle.isInWishlist"
                        :is-ajax-request="product.isWishlistAjax"
                        :show-as-link="false">
                    </app-save-car>
                </div>
                <div class="finance-quote-block">
                    <div class="fq-button-wrapper">
                        <a href="#" class="link-edit-finance top" @click="showFinanceOverlay()">
                            <span class="finance-calculator-icon"></span>
                            <span class="edit-finances">{{ 'Edit finance' | translate }}</span>
                        </a>
                    </div>
                </div>
                <div class="test-drive-block">
                    <div class="td-button-wrapper">
                        <a href="#" class="link-to-test-drive top" @click="redirectToYouDrive()">
                            <span class="book-test-drive-icon"></span>
                            <span class="move-to-test-drive">{{ 'Test Drive' | translate }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <span :class="{'pdp-data-container': offerTagData}">
            <app-attributes
                :car-attributes="carAttributes">
            </app-attributes>
            <app-offer-tags v-if="offerTagData"
                :offer-tag-data="offerTagData">
            </app-offer-tags>
        </span>
    </div>
</template>

<script>
import coreProductTopContainer from 'core/components/Configurator/ProductTopContainer';
import appSaveCar from 'bmw/components/SaveCar';
import UrlParser from 'core/mixins/UrlParser';
import Constants from 'bmw/components/Shared/Constants';
import appAttributes from 'bmw/components/Elements/Attributes';
import appOfferTags from 'bmw/components/Elements/OfferTags';

export default coreProductTopContainer.extend({
    mixins: [Constants, UrlParser],

    components: {
        appSaveCar,
        appAttributes,
        appOfferTags
    },

    props: {
        options: {
            required: true,
            type: Object
        },
        carAttributes: {
            required: true,
            type: Object,
            default() {
                return {}
            }
        },
        offerTagData: {
            required: false,
            type: Object
        }
    },

    methods: {
        redirectToYouDrive() {
            if (this.options.youDriveUrl) {
                window.location.href = this.options.youDriveUrl;
            }
        },
    }
});
</script>
