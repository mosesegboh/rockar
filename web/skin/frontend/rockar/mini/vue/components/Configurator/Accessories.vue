<template>
    <div class="accessories" id="accessories-list">
        <div class="general-preloader" v-show="ajaxLoading">
            <div class="show-loading"></div>
        </div>
        <div class="row">
            <app-accordion-group :open-first="true" v-if="accessories.length > 0 && className === 'active'" v-ref:accessories-list>
                <div class="accordion-group">
                    <app-accordion
                        v-for="group in accessories"
                        :scrollable="true"
                        :title="group.name"
                        type="right-down"
                        class-name="accordion-light scroll"
                        :class="formatAccessoryName(group.name)"
                        :id="group.group_id"
                        :track-by="group.id"
                        :key="group.id"
                    >
                        <li>
                            <div class="accordion-content">
                                <div class="accessories-section-wrapper">
                                    <ul class="item-grid">
                                        <li v-for="accessory in group.accessories"
                                            :track-by="accessory.id"
                                            :key="accessory.id"
                                        >
                                            <div class="accessories-block">
                                                <h5>
                                                    {{ accessory.name }}
                                                </h5>

                                                <div class="image-container">
                                                    <img :src="accessory.image" :alt="accessory.name">
                                                </div>

                                                <div class="accessory-price">
                                                    {{ accessory.price | numberFormat '0.00' true }}
                                                </div>

                                                <div>
                                                    <p class="accessory-show-details" @click="showAccessoriesDetails(accessory)">
                                                        <span class="nav-arrow arrow-next"></span>
                                                        {{ 'Show details' | translate }}
                                                    </p>
                                                </div>

                                                <button type="button" name="button"
                                                    class="button button-narrow button-empty-light"
                                                    v-bind:class="{ 'mini-gray': !accessory.status }"
                                                    :disabled="accessory.preSelected"
                                                    @click="manipulateOnItem(accessory)"
                                                >
                                                    {{ buttonTitle(accessory) | translate }}
                                                </button>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </app-accordion>
                </div>
            </app-accordion-group>

            <div>
                <app-modal :show.sync="showConfirmation" class="confirmation-popup simple-popup" v-ref:modal>
                    <div slot="content">
                        <p class="modal-header"> {{ 'Are you sure you want to remove this item?' | translate }}</p>
                        <div class="align-right">
                            <button class="button-narrow button-empty-light popup-cancel" @click="hideConfirmation()">
                                <span><span>{{ 'No' | translate }}</span></span>
                            </button>
                            <button class="button-default button-narrow button-blue-lagoon popup-confirm" @click="add(currentAccessory)">
                                <span><span>{{ 'Yes' | translate }}</span></span>
                            </button>
                        </div>
                    </div>
                </app-modal>
            </div>

            <app-modal :show.sync="showDetails" v-show="showDetails" class="carfinder-px-modal">
                <div slot="content">
                    <div class="accessories-detailed-wrapper">

                        <template v-if="isMobile">
                            <div class="accessories-detailed">
                                <h3 class="accessory-price">
                                    {{ detailedAccessory.price | numberFormat '0.00' true }}
                                </h3>
                                <div class="accessory-title">
                                    <h1 v-html="detailedAccessory.name"></h1>
                                </div>
                            </div>
                            <div class="accessories-detailed">
                                <div class="image-container">
                                    <img :src="detailedAccessory.image" :alt="detailedAccessory.name">
                                </div>
                            </div>
                            <div class="accessories-detailed">
                                <div class="accessory-details">
                                    <p v-html="detailedAccessory.extendedDescription"></p>
                                </div>
                            </div>
                            <div class="accessories-detailed">
                                <div class="accessories-modal-ctas">
                                    <button type="button" name="button" @click="closePopup()" class="button button-narrow button-empty-light popup-close">
                                        {{ 'Cancel' | translate }}
                                    </button>
                                    <button type="button" name="button"
                                        class="button button-blue-lagoon"
                                        :disabled="detailedAccessory.preSelected || detailedAccessory.status !== 0"
                                        @click="add(detailedAccessory); closePopup();"
                                    >
                                        {{ 'Select' | translate }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <app-accessories-detailed
                                :detailed-accessory="detailedAccessory"
                                v-on:close="closePopup()"
                                v-on:add="add(detailedAccessory)"
                            >
                            </app-accessories-detailed>
                        </template>
                    </div>
                </div>
            </app-modal>
        </div>
    </div>
</template>

<script>
    import appAccordionGroup from 'core/components/AccordionGroup';
    import appAccordion from 'core/components/Accordion';
    import appModal from 'core/components/Elements/Modal';
    import appAccessories from 'core/components/Configurator/Accessories';
    import appAccessoriesDetailed from 'mini/components/Elements/DetailedAccessories';

    export default appAccessories.extend({
        props: {
            className: {
                required: false,
                type: String,
                default: null
            }
        },

        data() {
            return {
                showDetails: false,
                detailedAccessory: {},
                showConfirmation: false,
                isMobile: false,
                fullWidth: 0
            }
        },

        beforeCreate() {
            this.fullWidth = document.documentElement.clientWidth
        },

        ready() {
            window.addEventListener('resize', this.handleResize)
        },

        beforeDestroy() {
            window.removeEventListener('resize', this.handleResize)
        },

        methods: {
            add(accessory) {
                this.ajaxLoading = true;
                this.showDetails = false;
                this.currentAccessory = accessory;
                const status = this.currentAccessory.status === 1 ? 0 : 1;
                this.updateAddedAccessories(status, accessory);
            },

            handleResize() {
                this.fullWidth = document.documentElement.clientWidth;
                this.isMobile = this.fullWidth < 737;
            },

            manipulateOnItem(accessory) {
                if (accessory.status === 1) {
                    this.currentAccessory = accessory;
                    this.showConfirmation = true;
                } else {
                    this.add(accessory);
                }
            },

            updateAddedAccessories(status, accessory) {
                const id = accessory.id;

                if (!this.productID) {
                    this.productID = this.product.id;
                }

                if (status === 1) {
                    this.currentAccessory.status = status;
                    this.addedAccessories[id] = accessory;
                    this.sendAddedAccessories();
                } else if (status === 0) {
                    this.currentAccessory.status = status;
                    this.showConfirmation = false;
                    delete this.addedAccessories[id];
                    this.sendAddedAccessories();
                }
            },

            showAccessoriesDetails(item) {
                this.showDetails = true;
                this.detailedAccessory = item;
            },

            closePopup() {
                this.showDetails = false;
            },

            hideConfirmation() {
                this.currentAccessory = null;
                this.showConfirmation = false;
            },

            buttonTitle(accessory) {
                return !accessory.status ? '+ Add' : 'Remove';
            }
        },

        components: {
            appAccordionGroup,
            appAccordion,
            appModal,
            appAccessoriesDetailed
        }
    });
</script>
