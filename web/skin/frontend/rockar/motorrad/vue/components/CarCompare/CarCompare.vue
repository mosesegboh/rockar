<template>
    <div id="car-compare-wrapper">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <div v-for="(index, item) in compareData.items" class="save-modal-wrapper">
            <car-compare-save-car
                :car-id="index"
                :customer-name="customerName"
                :product-name="item.name"
                :my-account-url="myAccountUrl"
                :save-wishlist-url="item.saveUrl"
                :is-in-wishlist="item.isSaved"
                :is-ajax-request="isAjaxRequest"
            ></car-compare-save-car>
        </div>

        <div class="car-compare">
            <div class="header">
                <p class="title">{{ getPageTitleAndSubtitle.title }}</p>
                <p class="subtitle">{{ getPageTitleAndSubtitle.subtitle | translate }}</p>
            </div>

            <div class="selector">
                <div class="selector-column left-column">
                    <div class="title">
                        <p>{{ 'Compare' | translate }}</p>
                    </div>
                    <p class="subtitle">{{ 'Select up to three models to compare technical specifications and features.' | translate }}</p>
                    <app-select
                        id="categorySelector"
                        :options="categories"
                        :title="Please select a category"
                        custom-event="CarCompare::newCategory"
                    ></app-select>
                </div>
                <div v-for="(index, item) in compareData.items" class="car-column">
                    <div class="img-block">
                        <a :href="item.productUrl" class="img-link">
                            <img :src="item.image" :alt="item.title" />
                        </a>
                        <div class="remove-car">
                            <a href="javascript:void(0)" @click="remove(item.removeUrl)">
                                <span class="close-icon"></span>
                            </a>
                        </div>
                    </div>
                    <a :href="item.productUrl" class="title">{{ item.title }}</a>
                    <p class="subtitle">{{ item.subTitle }}</p>
                    <p class="price">{{ Math.round(item.price) | numberFormat '0,0' true }}</p>
                </div>
                <div class="car-column add-car" v-if="compareData.canAdd">
                    <img :src="addCarImg" alt="{{ 'Add vehicle' | translate }}">
                    <button @click="redirectToCarFinder({ resetFilters: true })" class="button-empty button-medium">{{ 'Add vehicle' | translate }}</button>
                </div>
            </div>

            <div class="details">
                <div class="attribute-name-column left-column">
                    <template v-for="(index, category) in compareData.attributes">
                        <template v-if="this.selectedCategory === category.title">
                            <div class="category">
                                <div v-show="category.attributes" :title="category.title" class="group">
                                    <div v-for="attribute in category.attributes" class="attribute-wrapper">
                                        <p class="attribute">{{ attribute.title.toUpperCase() }}</p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </template>
                </div>
                <template v-for="item in compareData.items">
                    <div class="car-column">
                        <template v-for="(index, category) in compareData.attributes">
                            <template v-if="this.selectedCategory === category.title">
                                <div class="category">
                                    <div :title="category.title" class="group">
                                        <div v-for="attribute in category.attributes" class="attribute-wrapper">
                                            <p class="attribute">{{ item[attribute.code].toUpperCase() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>
                </template>
            </div>

            <div class="choices">
                <div class="step-back-column left-column">
                    <p @click="stepBack()" class="choice step-back">{{ 'Step back' | translate }}</p>
                </div>
                <div v-for="(index, item) in compareData.items" class="car-column">
                    <p v-if="item.productUrl" type="button" class="choice" @click="setLocation(item.productUrl)">{{ getChooseButtonText(item) + ' >' | translate}}</p>
                    <p v-if="item.visibleInYouDrive" type="button" class="choice" @click="this.redirectToYouDrive(item.youDriveIndex)">{{ 'Test drive' | translate}}</p>
                    <p v-if="!item.isSaved" type="button" class="choice" @click="openCarSavePopup(index)">{{ 'Save' | translate }}</p>
                </div>
            </div>
        </div>

        <div class="mobile-wrapper">
            <div class="car-compare-mobile">
                <div v-for="(index, item) in compareData.items" class="car-page">
                    <div class="top-naviation">
                        <p class="step-back" @click="stepBack()">{{ 'Step back' | translate }}</p>
                        <p class="compare-length" >{{ 'Compare list ' | translate }}({{ carCompareLength }})</p>
                    </div>

                    <div class="selector">
                        <div class="img-block">
                            <img :src="item.image" :alt="item.title" class="car-image"/>
                            <div class="remove-car">
                                <a href="javascript:void(0)" @click="remove(item.removeUrl)">
                                    <span class="close-icon"></span>
                                </a>
                            </div>
                        </div>

                        <div class="compare-item-data">
                            <div class="data-wrapper">
                                <a :href="item.productUrl" class="title">{{ item.title }}</a>
                                <p class="subtitle">{{ item.subTitle }}</p>
                                <p class="price">{{ Math.round(item.price) | numberFormat '0,0' true }}</p>
                            </div>
                        </div>
                        <app-select
                                id="categorySelector"
                                :options="categories"
                                :title="Please select a category"
                                custom-event="CarCompare::newCategory"
                        ></app-select>
                    </div>

                    <div class="details">
                        <template v-for="(index, category) in compareData.attributes">
                            <template v-if="this.selectedCategory === category.title">
                                <div class="category">
                                    <div v-show="category.attributes" :title="category.title" class="group">
                                        <div v-for="attribute in category.attributes" class="attribute-wrapper">
                                            <p class="attribute-title">{{ attribute.title.toUpperCase() }}</p>
                                            <p class="attribute-value">{{ item[attribute.code].toUpperCase() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>

                    <div class="choices">
                        <p v-if="item.visibleInYouDrive" type="button" class="choice" @click="this.redirectToYouDrive(item.youDriveIndex)">{{ 'Test Drive' | translate}}</p>
                        <p v-if="!item.isSaved" type="button" class="choice" @click="openCarSavePopup(index)">{{ 'Save' | translate }}</p>
                    </div>
                </div>

                <div class="car-page add-car" v-if="compareData.canAdd">
                    <div class="top-naviation">
                        <p class="step-back" @click="stepBack()">{{ 'Step back'.toUpperCase() | translate }}</p>
                        <p class="compare-length" >{{ 'Compare list ' | translate }}({{ carCompareLength }})</p>
                    </div>
                    <img :src="addCarImg" alt="{{ 'Add vehicle' | translate }}">
                    <button @click="redirectToCarFinder({ resetFilters: true })" class="button-empty button-medium add-car-button">{{ 'Add vehicle' | translate }}</button>
                </div>
            </div>

            <div class="mobile-choose-button" v-if="activeMobileItem">
                <button @click="setLocation(activeMobileItem.productUrl)">{{ `${getChooseButtonText(activeMobileItem)}` | translate}}</button>
            </div>
        </div>
    </div>
</template>

<script>
    import coreCarCompare from 'core/components/CarCompare/CarCompare';
    import carCompareSaveCar from 'motorrad/components/CarCompare/CarCompareSaveCar';

    export default coreCarCompare.extend({
        components: {
            carCompareSaveCar
        }
    });
</script>
