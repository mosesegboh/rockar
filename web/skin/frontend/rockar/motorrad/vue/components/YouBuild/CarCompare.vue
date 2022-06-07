<template>
    <div class="car-compare row">
        <div class="pod-attribute"
             :class="[compareClicked ? 'compare-button-dark' : 'compare-button-light', isIE11 ? 'ie11-browser' : '']"
             @click="changeCompare()">
            <p>{{ 'Compare >' | translate }}</p>
        </div>
        <div class="pod-attribute choose-button" @click="openCar">
            <p>{{ 'Choose' | translate }}</p>
        </div>
        <app-modal :show="openCompareState" class-name="simple-popup" @close-popup="closeCompare">
            <div slot="content" class="compare-add">
                <div class="preloader" v-show="ajaxLoading">
                    <div class="show-loading"></div>
                </div>

                <div class="compare-content" v-if="errorMessage && !limitReached">
                    <p class="h1">
                        {{ 'Adding car to compare list:' | translate }}
                    </p>

                    <p v-if="errorMessage">
                        {{ errorMessage }}
                    </p>

                    <p v-if="!errorMessage && serverError">
                        {{ 'An error occurred. Please try again later.' | translate }}
                    </p>

                    <p v-if="!serverError">
                        {{ 'Vehicle is being added to compare list.' | translate }}
                    </p>
                </div>

                <div class="compare-content" v-if="!errorMessage && !limitReached && compareClicked">
                    <p class="h1">
                        {{ 'Vehicle was added to compare list:' | translate }}
                    </p>

                    <p>
                        {{ 'Vehicle has been added to' | translate }}
                        <a :href="compareUrl" class="compare-content-link" @click="saveUrl()">
                            {{ 'Compare List' | translate }}
                        </a>
                    </p>
                </div>

                <div class="compare-content" v-if="!errorMessage && !limitReached && !compareClicked">
                    <p class="h1">
                        {{ 'Vehicle was removed from compare list:' | translate }}
                    </p>

                    <p>
                        {{ 'Vehicle has been removed from' | translate }}
                        <a :href="compareUrl" class="compare-content-link" @click="saveUrl()">
                            {{ 'Compare List' | translate }}
                        </a>
                    </p>
                </div>

                <div class="compare-content" v-if="limitReached">
                    <p class="h1">
                        {{ 'You’ve reached your compare limit:' | translate }}
                    </p>

                    <p>
                        {{ 'You can compare up to 3 vehicles. Click ' | translate }}
                        <a :href="compareUrl" class="compare-content-link">{{ 'here' | translate }}</a>
                        {{ ' to remove one and add another.' | translate }}
                    </p>
                </div>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import coreCarCompare from 'core/components/YouBuild/CarCompare'

    export default coreCarCompare.extend({
        components: {
            coreCarCompare
        }
    });
</script>