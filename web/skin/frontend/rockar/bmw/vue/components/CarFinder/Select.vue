<script>
    import coreSelect from 'core/components/Elements/Select';

    export default coreSelect.extend({
        computed: {
            selectTitle() {
                if (this.selectedValue >= 0) {
                    if (this.itemTitle) {
                        return this.itemTitle;
                    }
                    return this.options[this.selectedValue] ? this.options[this.selectedValue].title : '';
                }
                return this.title;
            },
        },

        methods: {
            select(option, id, event = true) {
                this.highlightIndex = -1;
                this.tempIndex = [];
                this.tempKey = null;

                if (typeof id !== 'undefined') {
                    if (this.selected !== id) {
                        if (!option) {
                            this.selected = -1;
                            this.itemTitle = this.title;
                            if (event) {
                                this.$dispatch(this.customEvent, false);
                            }
                            this.$emit('select', false);
                        } else {
                            this.selected = id;
                            this.itemTitle = option.title;
                            if (event) {
                                this.$dispatch(this.customEvent, option);
                            }
                            this.$emit('select', option);
                        }
                    }
                    if (option.title !== this.itemTitle) {
                        this.itemTitle = option.title;
                        if (event) {
                            this.$dispatch(this.customEvent, option);
                        }
                        this.$emit('select', option);
                    }
                }

                this.toggle();
            },
        }

    })
</script>