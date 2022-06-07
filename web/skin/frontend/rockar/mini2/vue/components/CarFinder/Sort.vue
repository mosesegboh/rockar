<script>
import appSort from 'dsp2/components/CarFinder/Sort';
import appResultsFilter from 'dsp2/components/CarFinder/ResultsFilter';

export default appSort.extend({
    methods: {
        toggleSortDirection(event) {
            if (event) {
                event.target.style.width = `${25 + this.sortOptions[this.selectedDirection].title.length * 8}px`;

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
        }
    }
});
</script>