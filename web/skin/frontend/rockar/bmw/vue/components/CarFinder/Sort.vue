<template>
    <div class="sort-button">
        <button class="button button-empty" @click="toggleSortDirection()">{{ 'Price' | translate }} {{ sortDirectionText }}</button>
    </div>
</template>

<script>
    import translateString from 'core/filters/Translate';

    export default Vue.extend({
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
            sortDirectionText() {
                return this.sortDirection === 'asc' ? translateString('low - high') : translateString('high - low')
            }
        },

        methods: {
            translateString,

            toggleSortDirection() {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
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
                this.$dispatch('CarFinder::updateFilters');
            },

            saveSortDataFail(error) {
                this.$dispatch('ProductGrid::ajaxLoad', false);
            }
        }
    });
</script>
