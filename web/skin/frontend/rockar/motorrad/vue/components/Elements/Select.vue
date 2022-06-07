<script>
    import ps from 'perfect-scrollbar';
    import coreSelect from 'core/components/Elements/Select';

    export default coreSelect.extend({
        events: {
            'Select::updateSelected'(id) {
                this.selected = id;
            }
        }
    });
</script>
