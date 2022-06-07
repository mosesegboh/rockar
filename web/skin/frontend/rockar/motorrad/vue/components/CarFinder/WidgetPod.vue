<template>
    <div class="category-products-list-item widget-pod" transition="opacity">
        <div class="pod-content">
            <div class="pod-images" @click="openCar">
                <div class="pod-image-container" v-for="image in images">
                    <img v-bind:src="image" v-if="image"/>
                </div>
            </div>
            <div class="pod-data" v-el:pod-data>
                <div class="pod-data-container">
                    <div class="pod-caption">
                        <div class="caption">
                            <div class="title">
                                <a v-on:mouseup="openCar">{{ title }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="description-block">
                        <p class="description-content">
                            {{ 'You selected A' | translate }}
                            {{ title }}
                            {{ 'Currently this model is only available for youbuild.' | translate }}
                        </p>
                    </div>
                    <div class="product-prices">
                        <div class="item-details-price-block">
                            <div class="item-details-price" v-if="monthlyPrice > 0">
                                <div class="price-wrapper">
                                    <div class="from-label-container">
                                        <span class="from-label">{{ 'From' | translate }}</span>
                                    </div>
                                    <div class="price-label-container">
                                        <span class="price">{{ Math.round(monthlyPrice) | numberFormat '0,0' true }}</span>
                                    </div>
                                    <div class="label-container">
                                        <span class="label">{{ 'A Month' | translate }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="item-details-price" v-if="rockarPrice > 0">
                                <div class="price-wrapper">
                                    <div class="from-label-container">
                                        <span class="from-label">{{ 'From' | translate }}</span>
                                    </div>
                                    <div class="price-label-container">
                                        <span class="price">{{ rockarPrice | numberFormat '0,0' true }}</span>
                                    </div>
                                    <div class="label-container">
                                        <span class="label">{{ 'Offer price' | translate }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="item-details-price" v-if="leadTimeRounded > 0">
                                <div class="price-wrapper">
                                    <div class="from-label-container">
                                        <span class="from-label">{{ 'From' | translate }}</span>
                                    </div>
                                    <div class="day-label-container">
                                        <span class="attr-day-day">{{ leadTimeRounded }}</span>
                                        <span v-if="leadTimeRounded == 1"
                                              class="attr-day-label">{{ 'Day' | translate }}</span>
                                        <span v-else class="attr-day-label">{{ 'Days' | translate }}</span>
                                    </div>
                                    <div class="label-container">
                                        <span class="delivery-label">{{ 'Delivery' | translate }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pod-actions">
                        <div class="row">
                            <div class="pod-attribute you-build-attr" @click="openCar">
                                <p>{{ 'Build Now' | translate }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            productUrl: {
                required: true,
                type: String
            },
            rockarPrice: {
                required: true,
                type: Number
            },
            monthlyPrice: {
                required: true,
                type: Number
            },
            title: {
                required: false,
                type: String
            },
            leadTime: {
                required: true,
                type: Number
            },
            images: {
                required: true,
                type: Array
            },
            updateYouBuildStepUrl: {
                required: false,
                type: String
            }
        },

        computed: {
            leadTimeRounded() {
                return Math.ceil(this.leadTime);
            },
        },

        methods: {
            openCar(mouseEvent) {
                if (mouseEvent.button === 0) {
                    // Forces to skip model selection, since it's not needed when continuing from widget
                    if (this.$root.$refs.carFinder) {
                        const promise = this.$root.$refs.carFinder.updateCurrentStepInSession(1, this.updateYouBuildStepUrl);

                        // Perform redirect either way
                        promise.finally(() => {
                            window.location.href = this.productUrl;
                        });
                    } else {
                        window.location.href = this.productUrl;
                    }
                } else if (mouseEvent.button === 1) {
                    window.open(this.productUrl, '_blank');
                    window.focus();
                }
            }
        }
    });
</script>
