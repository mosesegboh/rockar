export default {
    computed: {
        /**
         * Computed value which get's the breakpoints for the site
         * Breakpoints may be saved on the root component so in order to avoid duplicating
         * references to the root this mixin will be used
         * @return {Object}
         */
        resonsiveBreakpoints() {
            const breakpoints = {
                MOBILE: 736,
                TABLET: 1024,
                MIN_MOBILE_HEIGHT: 720,
                HORIZONTAL_BAR_MODE: 900
            };

            if (this.$root.RESPONSIVE_BREAKPOINTS) {
                Object.assign(breakpoints, this.$root.RESPONSIVE_BREAKPOINTS);
            }

            return breakpoints;
        }
    },

    methods: {
        /**
         * Is Mobile view ?
         * @return {Boolean}
         */
        isMobileView() {
            return window.innerWidth < this.resonsiveBreakpoints.MOBILE;
        },

        /**
         * Is Tablet view ?
         * @return {Boolean}
         */
        isTabletView() {
            return this.resonsiveBreakpoints.MOBILE <= window.innerWidth && window.innerWidth < this.resonsiveBreakpoints.TABLET;
        },

        /**
         * Is Desktop view ?
         * @return {Boolean}
         */
        isDesktopView() {
            return this.resonsiveBreakpoints.TABLET <= window.innerWidth;
        }
    }
};
