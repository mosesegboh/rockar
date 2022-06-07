<template>
    <div class="accessories" id="accessories-list">
        <div class="general-preloader" v-show="ajaxLoading">
            <div class="show-loading"></div>
        </div>
        <div class="row">
            <div class="accessories-header">
                <div class="accessories-title">
                    {{ 'add extra Accessories' | translate }}
                </div>
                <div class="filter">
                    <div class="title">{{ 'Show' | translate }}:</div>
                    <div class="filter-options">
                        <select v-model="selectedGroup" @change="changeGroup()">
                            <option selected>{{ 'All' | translate }}</option>
                            <option v-for="(index, group) in accessoriesGroups"
                                     :id="group.group_id"
                                     :value="group.group_identifier">
                                {{ group.group_name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <app-carousel-accessories
                :slides="accessories"
                :options="options"
                v-on:show-accessories="showAccessoriesDetails"
                v-on:manipulate-on-item="manipulateOnItem"
                v-on:button-title="buttonTitle"
            >
            </app-carousel-accessories>

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

            <app-modal
                :show.sync="showDetails"
                v-show="showDetails"
                class="carfinder-px-modal"
                :class="{ 'extended-description': detailedAccessory.extendedDescription }"
            >
                <div slot="content">
                    <div class="accessories-detailed-wrapper">
                            <app-accessories-detailed
                                :detailed-accessory="detailedAccessory"
                                v-on:close="closePopup()"
                                v-on:add-and-close="addDetailedAccessoryAndClose(detailedAccessory)"
                            >
                            </app-accessories-detailed>
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
    import appAccessoriesDetailed from 'dsp2/components/Elements/DetailedAccessories';
    import appCarouselAccessories from 'dsp2/components/Configurator/CarouselAccessories';

    export default Vue.extend({
        props: {
            className: {
                required: false,
                type: String,
                default: null
            },

            accessories: {
                required: true,
                type: Array
            },

            accessoriesGroups: {
                required: true,
                type: Array
            },

            addAccessoriesUrl: {
                required: true,
                type: String
            },

            product: {
                required: true,
                type: Object
            },

            activeColor: {
                required: false,
                type: String,
                default: ''
            }
        },

        data() {
            return {
                showDetails: false,
                detailedAccessory: {},
                showConfirmation: false,
                isMobile: false,
                fullWidth: 0,
                productID: null,
                ajaxLoading: false,
                configurationID: null,
                currentAccessory: null,
                showAccessories: true,
                addedAccessories: {},
                selectedGroup: '',
                options: {
                    dots: true,
                    infinite: true,
                    adaptiveHeight: true,
                    slidesToShow: 4,
                    slidesToScroll: 4,
                    responsive: [
                        {
                            breakpoint: 1408,
                            settings: {
                                slidesToShow: 3,
                                slidesToScroll: 3,
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2,
                            }
                        },
                        {
                            breakpoint: 600,
                            settings: {
                                centerMode: true,
                                centerPadding: '35%',
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]
                },
            }
        },

        beforeCreate() {
            this.fullWidth = document.documentElement.clientWidth
        },

        ready() {
            const accessoryIds = [];

            // include already added accessories into added accessories
            if (this.accessories.length) {
                this.accessories.forEach((accessory) => {
                    if (accessory.status === 1) {
                        this.addedAccessories[accessory.id] = accessory;
                    }
                });
            }

            Object.keys(this.addedAccessories).forEach((key, index) => {
                accessoryIds.push(this.addedAccessories[key].id);
            });

            this.$dispatch('Configurator::sendAddedAccessories', accessoryIds);
            window.addEventListener('resize', this.handleResize)
        },

        beforeDestroy() {
            window.removeEventListener('resize', this.handleResize)
        },

        events: {
            'Accessories::removeAccessory'(accessory) {
                this.accessories.forEach((value) => {
                    if (value.id === accessory.remove) {
                        this.add(value);
                    }
                });
            }
        },

        methods: {
            add(accessory) {
                this.ajaxLoading = true;
                this.currentAccessory = accessory;
                const status = this.currentAccessory.status === 1 ? 0 : 1;
                this.updateAddedAccessories(status, accessory);
                this.hideConfirmation()
            },

            sendAddedAccessories() {
                const accessoryIds = [];
                Object.keys(this.addedAccessories).forEach((key, index) => {
                    accessoryIds.push(this.addedAccessories[key].id);
                });
                this.$dispatch('Configurator::sendAddedAccessories', accessoryIds);

                this.$http({
                    url: this.addAccessoriesUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        id: this.$parent.selectedCar,
                        configurable_id: this.$parent.product.id,
                        color: this.activeColor,
                        accessories: accessoryIds
                    }
                }).then(this.addSuccess, this.addFail);
            },

            addSuccess(resp) {
                this.ajaxLoading = false;

                if (resp.data.success) {
                    this.$dispatch('Main::updateFinanceQuote', resp);
                } else {
                    this.currentAccessory.status = this.currentAccessory.status === 1 ? 0 : 1;
                    this.updateAddedAccessories(this.currentAccessory.status, this.currentAccessory.id);
                }

                this.currentAccessory = null;
            },

            addFail(resp) {
                this.ajaxLoading = false;
                this.currentAccessory.status = this.currentAccessory.status === 1 ? 0 : 1;
                this.updateAddedAccessories(this.currentAccessory.status, this.currentAccessory.id);
                this.currentAccessory = null;
            },

            manipulateOnItem(accessory) {
                this.add(accessory);
            },

            updateAddedAccessories(status, accessory) {
                const id = accessory.id;
                if (status === 1) {
                    this.currentAccessory.status = status;
                    this.addedAccessories[id] = accessory;
                    this.sendAddedAccessories();
                } else if (status === 0) {
                    this.currentAccessory.status = status;
                    delete this.addedAccessories[id];
                    this.sendAddedAccessories();
                }
            },

            formatAccessoryName(accessory) {
                return accessory.replace(/ /g, '-').toLowerCase();
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
            },

            addDetailedAccessoryAndClose(detailedAccessory) {
                this.add(detailedAccessory);
                this.closePopup();
            },

            changeGroup() {
                this.$emit('changeGroup', this.selectedGroup);
            }
        },

        components: {
            appAccordionGroup,
            appAccordion,
            appModal,
            appAccessoriesDetailed,
            appCarouselAccessories
        }
    });
</script>
