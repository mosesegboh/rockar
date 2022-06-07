<template>
    <div id="configurator">
        <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

        <div>
            <app-accordion-group>
                <app-accordion
                    title="Extra Features"
                    :scroll-on-show="false"
                    class-name="accordion-light"
                    type="right-down"
                    id="extra_features"
                >
                    <div v-for="(dataIndex, data) in carData" :key="dataIndex">
                        <table v-if="data.group && data.items.length">
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="group-title">{{ data.group | translate }}</span>
                                    </td>
                                </tr>
                                <tr v-for="(index, item) in data.items" :key="index">
                                    <td>
                                        <span :class="{ 'no-icon': !item.remove }">{{ item.label | convertNCR }}</span>
                                    </td>
                                    <td>
                                        <span class="table-right">{{ !item.price ? '0.00' : item.price | numberFormat '0,0.00' true }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <table class="extra-total-table">
                        <tr>
                            <td>
                                <span>{{ 'Total Price' | translate }}</span>
                            </td>
                            <td>
                                <span class="table-right">{{ extrasTotal | numberFormat '0,0.00' true }}</span>
                            </td>
                        </tr>
                    </table>
                </app-accordion>

                <app-accordion
                    title="Technical Features"
                    :scroll-on-show="false"
                    type="right-down"
                    class-name="accordion-light"
                    id="technical_features"
                >
                    <div v-for="(index, section) in technicalSpecItems" :key="index">
                        <table>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="group-title">{{ section.subtitle | translate }}</span>
                                    </td>
                                </tr>
                                <tr v-for="(index, item) in section.items" :key="index">
                                    <td>
                                        <span v-html="item.title"></span>
                                    </td>
                                    <td>
                                        <span class="table-right" v-html="item.value"></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </app-accordion>

                <app-accordion
                    title="Standard Features"
                    :scroll-on-show="false"
                    class-name="accordion-light"
                    type="right-down"
                    id="standard_features"
                >
                    <div v-for="(index, features) in standardFeatures" :key="index">
                        <li class="accordion-list standard-features">
                            <div
                                class="features-item"
                                v-for="(index, item) in features.features"
                                :key="index"
                                track-by="$index"
                                v-html="item"
                            ></div>
                        </li>
                    </div>
                </app-accordion>
            </app-accordion-group>
            <div id="my-build-accessories" class="my-build accessories">
                <app-accessories v-if="accessories.length"
                    :accessories="accessories"
                    :accessories-groups ="accessoriesGroups"
                    :add-accessories-url="addAccessoriesUrl"
                    :product="product"
                    :active-color="colors.active"
                    :class-name="activeStep === 'accessories' ? 'active' : ''"
                ></app-accessories>
            </div>
        </div>
    </div>
</template>

<script>
import coreConfigurator from 'core/components/Configurator/Configurator';
import appSaveCar from 'dsp2/components/SaveCar';
import appChooseVehicleGrid from 'dsp2/components/Configurator/ChooseVehicleGrid';
import appTopFinanceQuote from 'dsp2/components/Configurator/TopFinanceQuote';
import appAccessories from 'dsp2/components/Configurator/Accessories';
import appNavigation from 'dsp2/components/Configurator/Navigation';
import UrlParser from 'dsp2/mixins/UrlParser';
import appProductTopContainer from 'dsp2/components/Configurator/ProductTopContainer';

export default coreConfigurator.extend({
    mixins: [UrlParser],

    components: {
        appSaveCar,
        appChooseVehicleGrid,
        appAccessories,
        appTopFinanceQuote,
        appNavigation,
        appProductTopContainer
    },

    props: {
        accessoriesGroups: {
            required: false,
            type: Array,
            default: []
        },

        carAttributes: {
            required: true,
            type: Object
        }
    },

    computed: {
        /**
         * removed accessories from steps
         */
        steps() {
            return [
                'details',
                'checkout'
            ];
        }
    },

    created() {
        const url = this.$root.parseURL().searchObject;
        const requestedData = {
            pxMmCode: url.mmCode,
            pxVrmInput: url.vrm,
            pxRegistrationYear: url.registrationYear,
            pxMileage: url.tradeInMileage,
            pxCondition: url.condition,
            pxAdditionalInfo: url.additionalInfo,
            pxSettlement: url.settlement
        };

        this.$store.commit('setDeepLinkRequestParams', requestedData);

        // Remove parsed params from URL to allow arbitrary data changes
        ['mmCode', 'vrm', 'registrationYear', 'tradeInMileage', 'condition', 'additionalInfo', 'settlement', 'isCorporate']
            .forEach((param) => {
                delete url[param];
            });
        const cleanedUrl = this.$root.makeURLSearch(url);
        window.history.pushState({}, document.title, `?${cleanedUrl}`);
    },

    ready() {
        if (this.productType === 'simple') {
            this.selectedCar = this.product.id;
            this.vehicle = this.product;
        } else {
            this.selectedCar = this.vehicles[0] ? this.vehicles[0].id : false;
        }

        this.$dispatch('Configurator::preSelectConfiguration');

        this.$nextTick(() => {
            this.sendProductDetails();
        });
    }
});
</script>
