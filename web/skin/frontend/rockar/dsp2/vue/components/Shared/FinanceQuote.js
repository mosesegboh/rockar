import FinanceQuote from 'core/components/Shared/FinanceQuote';
import numeral from 'numeral';

export default {
    mixins: [FinanceQuote],

    props: {
        activePayment: {
            required: true,
            type: Object
        },

        payInFullPayment: {
            required: true,
            type: Array
        },

        preSelectedAccessories: {
            required: false,
            type: Array,
            default() {
                return [];
            }
        }
    },

    methods: {
        progressFinanceQuote() {
            this.ajaxLoading = true;
            const data = {
                group_id: this.group_id,
                payment_type: this.payment_type,
                term: this.financeParams.term,
                mileage: this.financeParams.mileage,
                balloonPercentage: this.financeParams.balloonPercentage,
                deposit: this.financeParams.deposit,
                depositMultiple: this.financeParams.depositMultiple,
                maintenance: this.financeParams.maintenance,
                product_id: this.productId
            };

            this.$http({
                url: this.progressUrl,
                method: 'POST',
                emulateJSON: true,
                data
            }).then(this.updateFinanceQuote, this.forceUpdateFail);
        },

        canCheckout() {
            return parseFloat(this.monthlyPrice) > 0
                || this.payInFullPayment.find(payment => payment.group_id === this.activePayment.group_id) !== undefined;
        },

        getFormattedValue(data) {
            if (data.value) {
                return data.value;
            }

            if (data.price) {
                return `${currencySymbol} ${numeral(data.price).format('0,0.00')}`;
            }

            if (data.number) {
                return numeral(data.number).format('0,0');
            }
        },

        getGroupPrice(data) {
            const price = numeral(0);
            if (data.items && data.items.length) {
                data.items.forEach((item) => {
                    if (!item.price) {
                        item.price = '0.00';
                    }
                    price.add(numeral(item.price).format('0.00'));
                });
            }

            return `${currencySymbol} ${price.format('0,0.00')}`;
        },
    }
};
