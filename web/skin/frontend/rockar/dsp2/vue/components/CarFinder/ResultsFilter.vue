<template>
    <div class="sort-filter">
        <div class="results">
            <span>{{ getCarListProductCount() }}</span>
            <span>{{ 'Results' | translate }}</span>
        </div>
        <div class="applied-filters">
            <template v-if="appliedFilters.length > 0">
                <p class="title active" v-if="isFilterVisible">{{ 'Filtered by:' | translate }}</p>
                <ul>
                    <li v-for="(index, filter) in appliedFilters">
                        <div>
                            <span>{{ filter.label }}</span>
                            <a href="javascript:void(0)" @click="removeFilter(filter)">
                                <span class="icon-close-blue"></span>
                            </a>
                        </div>
                    </li>
                </ul>
            </template>
        </div>
        <app-sort
            :sort-data-save-url="options.sortDataSaveUrl"
            :sort-direction="sortDirection"
        ></app-sort>
    </div>
</template>

<script>
    import appSort from 'dsp2/components/CarFinder/Sort';
    import Constants from 'dsp2/components/Shared/Constants';

    export default Vue.extend({
        mixins: [Constants],

        data: () => ({
            productCount: ''
        }),

        props: {
            options: {
                required: true,
                type: Object
            },

            appliedFilters: {
                required: true,
                type: Array
            },

            isFilterVisible: {
                required: true,
                type: Boolean
            },

            filters: {
                required: true,
                type: Array
            }
        },

        methods: {
            removeFilter(val) {
                this.$emit('remove', val);
            },

            getCarListProductCount() {
                this.filters.forEach((option) => {
                    if (option.code === 'visible_in') {
                        this.productCount = parseInt(option.options[this.VISIBLEINFILTERVALUES.CATALOG_ONLY].count)
                            + parseInt(option.options[this.VISIBLEINFILTERVALUES.CATALOG_AND_YOUDRIVE].count);
                    }
                });

                return this.productCount;
            }
        },

        computed: {
            sortDirection() {
                return this.$store.state.carFinder.sortOrder
                    ? this.$store.state.carFinder.sortOrder
                    : this.options.sortDirection;
            }
        },

        components: {
            appSort
        }
    });
</script>
