<script>
import appSort from 'dsp2/components/CarFinder/Sort';

export default appSort.extend({
    methods: {
        toggleSortDirection(event) {
            if (event) {
                // adjust sort select width, depends on the options length, 35px is for padding and the dropdown arrow,
                // and 8px is for each symbol.
                event.target.style.width = `${35 + this.sortOptions[this.selectedDirection].title.length * 8}px`;

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