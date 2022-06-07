<template>
    <div class="sort">
        <p class="title">{{ 'Sort by:' | translate }}</p>
        <div class="filter-options">
            <template v-if="isMobile">
                <select @change="toggleSortDirection($event)" v-model="selectedSort">
                    <option selected disabled>{{ 'Sort' | translate }}</option>
                    <option v-for="option in sortOptions"
                        :id="option.id"
                        :value="option.title"
                        :class="{ 'active': selectedDirection === option.id }"
                    >
                        {{ option.title | translate }}
                    </option>
                </select>
            </template>
            <template v-else>
                <select @change="toggleSortDirection()">
                    <option v-for="option in sortOptions"
                        :id="option.id"
                        :value="option.title"
                        :selected="$index === selectedDirection ? 'selected' : false"
                        :class="{ 'active': selectedDirection === option.id }"
                    >
                        {{ option.title | translate }}
                    </option>
                </select>
            </template>
        </div>
    </div>
</template>

<script>
    import Constants from 'dsp2/components/Shared/Constants';

    export default Vue.extend({
        mixins: [Constants],

        data: () => ({
            sortOptions: [
                {
                    id: 0,
                    title: 'Price: Low to High'
                },

                {
                    id: 1,
                    title: 'Price: High to Low'
                }
            ],

            selectedSort: 'Sort'
        }),

        props: {
            sortDataSaveUrl: {
                required: true,
                type: String
            },

            sortDirection: {
                required: false,
                type: String,
                default: 'asc'
            }
        },

        computed: {
            selectedDirection() {
                return this.sortDirection === 'asc' ? 0 : 1;
            },

            isMobile() {
                return this.$root.$refs.carFinder.isMobile;
            }
        },

        methods: {
            toggleSortDirection(event) {
                if (event) {
                    if (event.target.value === 'Sort') {
                        this.selectedSort = this.sortOptions[this.selectedDirection].title;
                        return;
                    } else {
                        this.sortDirection = parseInt(event.target.selectedOptions[0].id) === 0 ? 'asc' : 'desc';
                    }
                } else {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                }
                this.$store.commit('setSortOrder', this.sortDirection);
                this.$dispatch('ProductGrid::ajaxLoad', true);
                this.saveSortDataInSession();
            },

            saveSortDataInSession() {
                this.$http({
                    url: this.sortDataSaveUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        dir: this.sortDirection
                    }
                }).then(this.saveSortDataSuccess, this.saveSortDataFail);
            },

            saveSortDataSuccess() {
                this.$dispatch('ProductGrid::ajaxLoad', false);
                this.$dispatch('CarFinder::sortProducts');
            },

            saveSortDataFail(error) {
                this.$dispatch('ProductGrid::ajaxLoad', false);
            }
        }
    });
</script>
