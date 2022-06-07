<script>
    import dsp2ModelFilter from 'dsp2/components/CarFinder/ModelFilter.vue';

    export default dsp2ModelFilter.extend({
        computed: {
            matrixFilter() {
                let matrix = [];

                this.filters.forEach((filter, index) => {
                    if (this.matrixAttribute.indexOf(filter.code) > -1) {
                        matrix = this.getMatrixList(this.filters[index].options);
                    }
                });

                matrix.forEach((item) => {
                    item.title = item.title.replace(/^(BMW){1}\s*/i, '');
                });

                return matrix;
            }
        }
    });
</script>
