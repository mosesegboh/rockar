<template>
    <div class="car-compare">
        <app-modal :show="openCompareState" class-name="simple-popup" @close-popup="closeCompare">
            <div slot="content" class="compare-add">
                <div class="preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>

                <div class="compare-content" v-if="!resultReceived && !limitReached">
                    <p class="h1">
                        {{ 'Adding vehicle to compare list:' | translate }}
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

                <div class="compare-content" v-if="resultReceived && !limitReached">
                    <p class="h1" v-if="!errorMessage && isInCompareList">
                        {{ 'Vehicle was added to compare list:' | translate }}
                    </p>

                    <p v-if="!errorMessage && isInCompareList">
                        {{ 'Vehicle has been added to' | translate }}
                        <a :href="compare.pageUrl" class="compare-content-link">
                            {{ 'Compare List' | translate }}
                        </a>
                    </p>

                    <p v-if="errorMessage">
                        {{ errorMessage }}
                    </p>
                </div>

                <div class="compare-content" v-if="limitReached">
                    <p class="h1">
                        {{ 'You’ve reached your compare limit:' | translate }}
                    </p>

                    <p>
                        {{ 'You can compare up to 3 vehicles. Click ' | translate }}
                        <a :href="compare.pageUrl" class="compare-content-link">{{ 'here' | translate }}</a>
                        {{ ' to remove one and add another.' | translate }}
                    </p>
                </div>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import coreCarCompare from 'core/components/CarFinder/CarCompare';

    export default coreCarCompare.extend({});
</script>