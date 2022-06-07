import coreFinanceOverlay from 'core/components/Shared/FinanceOverlay';
import AjaxHelper from 'dsp2/components/Shared/AjaxHelper';

export default {
    mixins: [coreFinanceOverlay, AjaxHelper],

    data() {
        return {
            calculatorCache: [],
            calculatorCacheExpireTime: 600000,
            discountId: 30
        }
    },

    computed: {
        getInstalmentID() {
            return Array.isArray(this.options)
                ? this.options.find(item => item.group_title.includes('Instalment Sale'))
                : false;
        }
    },

    methods: {
        /**
         * This takes in the active group ID and checks whether
         * it is the same as the instalment sale ID, if it is the same a true value is returned
         * @param {Integer} groupId - The ID given to each finance option
         * @return {Boolean}
         */
        isInstalmentSaleID(groupId) {
            const instalment = this.getInstalmentID;

            return instalment && parseInt(instalment.group_id) === parseInt(groupId);
        },

        setFinanceParams(params) {
            this.financeParams = JSON.parse(JSON.stringify(params));
        },

        /**
         * Get Options by product id
         * @return {Promise|void}
         */
        getDataByProductId() {
            // Repeated calls are made to this request unnecessarily
            // Due components extension
            // so limit requests to one at a time
            if (this.getLoader('getDataByProductId')) {
                // Error will be shown in console but no functionality affected
                return Promise.reject(new Error('Simultaneous/duplicated calls to the finance options api.'));
            }

            // before calling a new request,
            // check if the options with the current slider values
            // are already cached
            const options = this.getCalculatorCache(this.getCalculatorCacheKey());

            if (options) {
                this.options = options;
                this.applyOptionData();

                this.$dispatch('Main::onFinanceCalculatorChange', this.options[this.activeMethod]);

                return;
            }

            this.setLoader('getDataByProductId');

            return this.$super(coreFinanceOverlay, 'getDataByProductId');
        },

        applyOptionData() {
            for (let i = 0; i < this.options.length; i++) {
                if (parseInt(this.options[i].group_id) === parseInt(this.activeGroup)) {
                    this.activeMethodType = this.options[i].type;
                    this.activeMethod = i;
                }
            }

            if (this.payInFullPayment.find(payment => payment.group_id === this.activeGroup.group_id) !== undefined) {
                this.showSlider = false;
            }

            this.isHirePayment = this.isHirePaymentByGroupId(this.activeGroup);
        },

        getDataByProductIdSuccess(options) {
            this.options = options.data.options;
            this.ajaxLoading = false;
            this.dataReceived = true;

            this.applyOptionData();
            this.setLoader('getDataByProductId', false);
            this.setCalculatorCache(this.getCalculatorCacheKey(), this.options);

            this.options.forEach((option) => {
                if (parseInt(this.activeGroup) === parseInt(option.group_id)) {
                    this.option = option;
                }
            });

            this.$dispatch('ProductPod::onDataSuccess');
        },

        /**
         * Handle get options data failure
         * @param {Object} error
         */
        getDataByProductIdFail(error) {
            this.$super(coreFinanceOverlay, 'getDataByProductIdFail', error);
            this.setLoader('getDataByProductId', false);
            this.$dispatch('ProductPod::onDataFail');
        },

        getCalculatorCacheKey() {
            return [
                this.financeParams.term,
                this.financeParams.mileage,
                this.financeParams.deposit,
                this.financeParams.depositMultiple,
                this.financeParams.balloonPercentage,
                this.currentCouponCode
            ].join('/');
        },

        setCalculatorCache(key, options) {
            const time = new Date().getTime();

            for (let i = 0; i < this.calculatorCache.length; i++) {
                if (this.calculatorCache[i].key === key) {
                    this.calculatorCache[i].options = options;
                    this.calculatorCache[i].time = time;

                    return;
                }
            }

            this.calculatorCache.push({ key, options, time });
        },

        getCalculatorCache(key) {
            const result = this.calculatorCache.find(cache => cache.key === key);

            if (result) {
                return !this.isCalculatorCacheExpired(result) ? result.options : false;
            }

            return false;
        },

        isCalculatorCacheExpired(cache) {
            const timeNow = new Date().getTime();

            return timeNow - cache.time > this.calculatorCacheExpireTime;
        }
    },

    created() {
        if (this.couponCode) {
            this.currentCouponCode = this.couponCode;
        }

        EventsBus.$on('FinanceQuote::couponCodeUpdate', couponCode => {
            this.currentCouponCode = couponCode;
            this.getDataByProductId();
        });
    },

    watch: {
        activeMethod(val) {
            this.showSlider = parseInt(this.options[val].pay_in_full) === 0;
        }
    },

    events: {
        'FinanceOverlay::clearCache'() {
            this.calculatorCache = [];
        }
    }
};
