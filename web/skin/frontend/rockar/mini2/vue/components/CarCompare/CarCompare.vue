<template>
    <div id="car-compare-component" v-show="showCarCompare">
        <div class="toggle" :class="{ 'active': active }">
            <div class="toggle-button" @click="toggle()">
                <div class="icon"></div>
                <span>{{ active ? 'Close' : 'Open'  | translate }}</span>
            </div>
        </div>
        <div id="car-compare-wrapper" :class="{ 'active': active }">
            <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

            <div v-for="(index, item) in compareData.items" class="save-modal-wrapper" :key="index">
                <app-compare-save-car
                    :car-id="item.id"
                    :customer-name="customerName"
                    :product-name="item.name"
                    :product-title="item.title"
                    :product-subtitle="item.bodystyle"
                    :my-account-url="myAccountUrl"
                    :is-in-wishlist.sync="item.isSaved"
                    :is-ajax-request="isAjaxRequest"
                    :save-wishlist-url="item.saveUrl"
                    :remove-from-wishlist-url="item.removeFromWishlistUrl"
                    :image="item.image"
                    :ajax-loading.sync="ajaxLoading"
                ></app-compare-save-car>
            </div>

            <div class="car-compare">
                <div class="header">
                    <div class="selector">
                        <div class="selector-column left-column">
                            <div class="title">
                                <p>
                                    <span class="heading">{{ 'Compare List' | translate }}</span>
                                    <span class="clear" @click="openClearAllPopup()">{{ 'Clear All' | translate }}</span>
                                </p>
                            </div>
                        </div>
                        <div v-for="(index, item) in compareData.items" class="car-column" :key="index">
                            <div class="img-block">
                                <app-offer-tags
                                    :offer-tag-data="item.compare"
                                >
                                </app-offer-tags>
                                <a :href="item.productUrl" class="img-link">
                                    <img :src="item.image" :alt="item.title" />
                                </a>
                                <div class="remove-car">
                                    <a href="javascript:void(0)" @click="remove(item.removeUrl, item.sku)">
                                        <span class="close-icon"></span>
                                    </a>
                                </div>
                            </div>
                            <a :href="item.productUrl" class="title">{{ item.title | translate }}</a>
                        </div>
                        <div class="add-car" v-if="compareData.canAdd"></div>
                        <span class="clear" @click="openClearAllPopup()">{{ 'Clear All' | translate }}</span>
                    </div>
                    <div class="choices">
                        <div class="step-back-column left-column">
                        </div>
                        <div v-for="(index, item) in compareData.items" class="car-column" :key="index">
                            <button v-if="item.productUrl" type="button" class="button dsp2-money" @click="setLocation(item.productUrl)">{{ 'Proceed to checkout' | translate}}</button>
                            <div class="save-button" @click="openCarSavePopup(item.id)">
                            <p :class="{'added-to-wishlist': item.isSaved}"><span>{{ 'Add to wishlist' | translate }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="details">
                    <div class="category category-price">
                        <div class="left-column">
                            <p class="category-title">{{ 'Price' | translate }}</p>
                        </div>
                        <div v-for="(index, item) in compareData.items" class="car-column" :key="index">
                            <div class="attribute-value price">
                                <p class="price-total-title">{{ 'Offer Price' | translate }}</p>
                                <p class="price-total" :class="{'price-main': item.cash}">{{ Math.round(item.price) | numberFormat '0,0' true }}</p>
                            </div>
                            <div class="attribute-value price" v-if="!item.cash">
                                <p class="price-per-month-title">{{ 'Per Month' | translate }}</p>
                                <p class="price-per-month price-main">{{ Math.round(item.monthlyPrice) | numberFormat '0,0' true }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="category category-extra">
                        <div class="left-column">
                            <p class="category-title">{{ 'Extra Features' | translate }}</p>
                        </div>
                        <div class="category-body category-body-with-images">
                            <div class="left-column">
                                <div class="category-subtitle-wrapper">
                                    <p class="category-subtitle">{{ 'Options' | translate }}</p>
                                </div>
                            </div>
                            <template v-for="(index, item) in compareData.items">
                                <div class="car-column" :key="index">
                                    <div class="attribute-value">
                                        <img :src="item.exteriorWithImages.image" :alt="this.decodeHtml(item.exteriorWithImages.value)">
                                        <p class="short-title">{{ this.decodeHtml(item.exteriorWithImages.value) }}</p>
                                    </div>
                                    <div class="attribute-value no-border">
                                        <img :src="item.wheelWithImages.image" :alt="this.decodeHtml(item.wheelWithImages.value)">
                                        <p class="short-title">{{ this.decodeHtml(item.wheelWithImages.value) }}</p>
                                    </div>
                                    <div class="interior-image"><img :src="item.interiorImage" :alt="'Interior Image' | translate"></div>
                                    <div class="attribute-value">
                                        <img :src="item.interiorWithImages.image" :alt="this.decodeHtml(item.interiorWithImages.value)">
                                        <p class="short-title">{{ this.decodeHtml(item.interiorWithImages.value) }}</p>
                                    </div>
                                    <div class="attribute-value no-border">
                                        <template v-if="item.trimFinisherWithImages">
                                            <img :src="item.trimFinisherWithImages.image" :alt="this.decodeHtml(item.trimFinisherWithImages.value)" v-if="item.trimFinisherWithImages.image">
                                            <p class="short-title">{{ this.decodeHtml(item.trimFinisherWithImages.value) }}</p>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="category-body">
                            <div class="left-column">
                                <div class="category-subtitle-wrapper">
                                    <p class="category-subtitle">{{ 'Line/Packages' | translate }}</p>
                                </div>
                            </div>
                            <template v-for="(index, item) in compareData.items">
                                <div class="car-column" :key="index">
                                    <template v-for="(index, extra) in item.linePackages">
                                        <div class="attribute-value" :key="index">
                                            <p class="short-title">{{ this.decodeHtml(extra.label) }}</p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div class="category-body">
                            <div class="left-column">
                                <div class="category-subtitle-wrapper">
                                    <p class="category-subtitle">{{ 'Extra Options' | translate }}</p>
                                </div>
                            </div>
                            <template v-for="(index, item) in compareData.items">
                                <div class="car-column" :key="index">
                                    <template v-for="(index, extra) in item.extraOptions">
                                        <div class="attribute-value" :key="index">
                                            <p class="short-title">{{ this.decodeHtml(extra.label) }}</p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="category category-technical" v-if="compareData.items.length > 0">
                        <div class="left-column">
                            <p class="category-title">{{ 'Technical Features' | translate }}</p>
                        </div>
                        <div class="category-body">
                            <div class="left-column">
                                <div v-for="(index, specs) in compareData.items[0].technicalSpecs" :key="index" class="category-subtitle-wrapper">
                                    <p class="category-subtitle">{{ specs.title }}</p>
                                    <div class="attribute-title-wrapper">
                                        <div v-for="(index, spec) in specs.items" :key="index" class="attribute-title">
                                            <p>{{ spec.title }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <template v-for="(index, item) in compareData.items">
                                <div class="car-column" :key="index">
                                    <div v-for="(index, specs) in item.technicalSpecs" :key="index">
                                        <p class="category-subtitle schrodingers-title">{{ specs.title }}</p>
                                        <div v-for="(index, spec) in specs.items" :key="index" class="attribute-value">
                                            <p>{{ this.decodeHtml(spec.value) || '‏‏‎ ‎' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="category category-standard">
                        <div class="left-column">
                            <p class="category-title">{{ 'Standard Features' | translate }}</p>
                        </div>
                        <template v-for="(index, item) in compareData.items">
                            <div class="car-column" :key="index">
                                <template v-for="(index, feature) in item.standardFeatures" track-by="$index">
                                    <div class="attribute-value" :key="index">
                                        <p class="short-title" :title="feature">{{ this.decodeHtml(feature) }}</p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mobile-wrapper">
                <div class="header">
                    <p>{{ 'Compare list' | translate }}</p>
                    <span class="clear" @click="openClearAllPopup()">{{ 'Clear All' | translate }}</span>
                </div>
                <div class="car-compare-mobile">
                    <div class="car-compare-mobile-top">
                        <div v-for="(index, item) in compareData.items" class="car-page" :key="index">
                            <div class="selector">
                                <div class="img-block">
                                    <div class="top-navigation">
                                        <p class="compare-length">{{ index + 1 }}/{{ carCompareLength }}</p>
                                    </div>
                                    <app-offer-tags
                                        :offer-tag-data="item.compare"
                                    >
                                    </app-offer-tags>
                                    <img :src="item.image" :alt="item.title" class="car-image"/>
                                    <div class="remove-car">
                                        <a href="javascript:void(0)" @click="remove(item.removeUrl, item.sku)">
                                            <span class="close-icon"></span>
                                        </a>
                                    </div>
                                </div>
                                <a :href="item.productUrl" class="title">{{ item.title | translate }}</a>
                                <div class="choices">
                                    <div class="save-button" @click="openCarSavePopup(item.id)">
                                        <span class="save-icon" :class="{ 'added-to-wishlist': item.isSaved }"></span>
                                    </div>
                                    <div class="checkout-button" v-if="item.productUrl" @click="setLocation(item.productUrl)">
                                        <span class="checkout-icon"></span>
                                        {{ 'Buy this MINI' | translate }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="car-compare-mobile-bottom-titles">
                        <div class="titles title price">{{ 'Price' | translate }}</div>
                        <div class="titles title extra">{{ 'Extra Features' | translate }}</div>
                        <div class="titles subtitle options">{{ 'Options' | translate }}</div>
                        <div class="titles subtitle linePackages">{{ 'Line/Packages' | translate }}</div>
                        <div class="titles subtitle extraOptions">{{ 'Extra Options' | translate }}</div>
                        <div class="titles title technical">{{ 'Technical Features' | translate }}</div>
                        <div class="technical-attributes" v-if="compareData.items.length > 0">
                            <div v-for="(index, specs) in compareData.items[0].technicalSpecs" :key="index">
                                <div class="titles attribute-title">{{ specs.title }}</div>
                                <div>
                                    <template v-for="(index, spec) in specs.items">
                                        <div class="titles attribute-subtitle" :key="index">{{ spec.title }}</div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="titles title standard">{{ 'Standard Features' | translate }}</div>
                    </div>

                    <div class="car-compare-mobile-bottom">
                        <div v-for="(index, item) in compareData.items" class="car-page" :key="index">
                            <div class="details">
                                <div class="category category-price">
                                    <div class="schrodingers-title"><p>{{ 'Price' | translate }}</p></div>
                                    <div class="attribute-value price" v-if="!item.cash">
                                        <p>{{ 'Per Month' | translate }}</p>
                                        <p class="price-per-month price-main">{{ Math.round(item.monthlyPrice) | numberFormat '0,0' true }}</p>
                                    </div>
                                    <div class="attribute-value price">
                                        <p>{{ 'Offer Price' | translate }}</p>
                                        <p class="price-total" :class="{ 'price-main': item.cash }">{{ Math.round(item.price) | numberFormat '0,0' true }}</p>
                                    </div>
                                </div>

                                <div class="category category-extra">
                                    <div class="schrodingers-title"><p>{{ 'Extra Features' | translate }}</p></div>
                                    <div class="category-body category-body-with-images">
                                        <div class="schrodingers-title"><p>{{ 'Options' | translate }}</p></div>
                                        <div class="car-column" :key="index">
                                            <div class="attribute-value">
                                                <img :src="item.exteriorWithImages.image" :alt="this.decodeHtml(item.exteriorWithImages.value)">
                                                <p class="short-title">{{ this.decodeHtml(item.exteriorWithImages.value) }}</p>
                                            </div>
                                            <div class="attribute-value">
                                                <img :src="item.wheelWithImages.image" :alt="this.decodeHtml(item.wheelWithImages.value)">
                                                <p class="short-title">{{ this.decodeHtml(item.wheelWithImages.value) }}</p>
                                            </div>
                                            <div class="interior-image"><img :src="item.interiorImage" :alt="'Interior Image' | translate"></div>
                                            <div class="attribute-value no-border">
                                                <img :src="item.interiorWithImages.image" :alt="this.decodeHtml(item.interiorWithImages.value)">
                                                <p class="short-title">{{ this.decodeHtml(item.interiorWithImages.value) }}</p>
                                            </div>
                                            <div class="attribute-value no-border">
                                                <template v-if="item.trimFinisherWithImages">
                                                    <img :src="item.trimFinisherWithImages.image" :alt="this.decodeHtml(item.trimFinisherWithImages.value)">
                                                    <p class="short-title">{{ this.decodeHtml(item.trimFinisherWithImages.value) }}</p>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="category-body category-body-line-packages">
                                        <div class="schrodingers-title"><p>{{ 'Line/Packages' | translate }}</p></div>
                                        <div class="car-column">
                                            <template v-for="(index, extra) in item.linePackages">
                                                <div class="attribute-value" :key="index">
                                                    <p>{{ this.decodeHtml(extra.label) }}</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="category-body category-body-extra-options">
                                        <div class="schrodingers-title"><p>{{ 'Extra Options' | translate }}</p></div>
                                        <div class="car-column">
                                            <template v-for="(index, extra) in item.extraOptions">
                                                <div class="attribute-value" :key="index">
                                                    <p>{{ this.decodeHtml(extra.label) }}</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="category category-technical">
                                    <div class="schrodingers-title"><p>{{ 'Technical Features' | translate }}</p></div>
                                    <div class="car-column" :key="index">
                                        <div v-for="(index, specs) in item.technicalSpecs" :key="index">
                                            <p class="category-subtitle schrodingers-title">{{ specs.title }}</p>
                                            <div
                                                class="technical-items"
                                                :class="{ 'm-20': moreThanOne }"
                                                v-for="(index2, spec) in specs.items"
                                                :key="index2"
                                            >
                                                <div class="attribute-title">
                                                    <p class="schrodingers-title">{{ spec.title }}</p>
                                                </div>
                                                <div class="attribute-value">
                                                    <p>{{ this.decodeHtml(spec.value) || '‏‏‎ ‎' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="category category-standard">
                                    <div class="schrodingers-title"><p>{{ 'Standard Features' | translate }}</p></div>
                                    <div class="car-column">
                                        <template v-for="(index, feature) in item.standardFeatures" track-by="$index">
                                            <div class="attribute-value" :key="index">
                                                <p :title="feature">{{ this.decodeHtml(feature) }}</p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import coreCarCompare from 'dsp2/components/CarCompare/CarCompare';
    import appCompareSaveCar from 'mini2/components/CarCompare/CarCompareSaveCar';

    export default coreCarCompare.extend({
        components: {
            appCompareSaveCar
        }
    });
</script>
