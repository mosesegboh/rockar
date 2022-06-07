export default {
    data() {
        return {
            /**
             * @property {Boolean} ajaxLoading
             * This property is only included for backwards compatibility
             * Please {@see isAjaxLoading} for future checks
             * for if anything is ajax loading
             */
            ajaxLoading: false,
            itemLoading: {}
        };
    },

    computed: {
        /**
         * Check if any ajax calls are loading
         * @return {Boolean}
         */
        isAjaxLoading() {
            return Object.values(this.itemLoading)
                .some(loading => loading);
        }
    },

    methods: {
        /**
         * Register something is loading
         * @param {String} key
         * @param {Boolean} [isLoading=true]
         * @return {Boolean} isLoading?
         */
        setLoader(key, isLoading = true) {
            this.$set(`itemLoading.${key}`, isLoading);

            return this.getLoader(key);
        },

        /**
         * Checks if item is loading
         * @param {String} key
         * @return {Boolean} isLoading?
         */
        getLoader(key) {
            return Boolean(this.itemLoading[key]);
        }
    },

    created() {
        this.unwatchIsAjaxLoading = this.$watch('isAjaxLoading', newVal => {
            this.ajaxLoading = newVal;
        });
    }
}
