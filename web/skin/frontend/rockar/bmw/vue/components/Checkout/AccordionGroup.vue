<script>
    import coreAccordionGroup from 'core/components/Checkout/AccordionGroup';
    import AccordionGroupBase from 'bmw/components/Shared/AccordionGroup';

    export default coreAccordionGroup.extend({
        mixins: [AccordionGroupBase],

        computed: {
            /**
             * Use the store state pxValue
             */
            pxValue() {
                return this.$store.state.general.mySavedCar.pxValue;
            },

            /**
             * Use the store state mileage
             */
            mileage() {
                return this.$store.state.general.mySavedCar.mileage;
            },

            /**
             * Use the store state outstandingFinance
             */
            outstandingFinance() {
                return this.$store.state.general.mySavedCar.outstandingFinance;
            },

            /**
             * Use the store state expireDate
             */
            expireDate() {
                return this.$store.state.general.mySavedCar.expireDate;
            }
        },

        events: {
            'CheckoutAccordionGroup::nextStep'(stepCode, skipStepCode) {
                if (!skipStepCode || skipStepCode !== stepCode) {
                    this.$dispatch('Checkout::nextStep', stepCode);
                }
            }
        },

        watch: {
            /**
             * Currently viewed step
             *
             * @param stepCode
             */
            'openStep'(stepIndex) {
                var deliveryStepIndex = this.getStepIndex('delivery_step'),
                    accordion = this.$children[stepIndex],
                    checkoutPage = '/checkout/onepage/',
                    checkoutPageTitle = 'Checkout',
                    checkoutStepTags = {};

                if (stepIndex === deliveryStepIndex) {
                    this.$broadcast('Map::init');
                }

                if (accordion.show === true && typeof accordion.$children[0].onAccordionShow === 'function') {
                    accordion.$children[0].onAccordionShow(accordion);
                }

                switch (stepIndex) {
                    case 0:
                        checkoutPage = '/virtual/checkout/onepage/2-Address';
                        checkoutPageTitle = 'Checkout - Your Address';
                        checkoutStepTags = {
                            'checkout': {
                                'actionField': {
                                    'step': 1
                                },
                                'products': [this.productsObject]
                            }
                        };
                        break;
                    case 1:
                        checkoutPage = '/virtual/checkout/onepage/3-Delivery';
                        checkoutPageTitle = 'Checkout - Delivery/Collections';
                        checkoutStepTags = {
                            'checkout': {
                                'actionField': {
                                    'step': 2
                                },
                                'products': [this.productsObject]
                            }
                        };
                        break;
                    case 2:
                        checkoutPage = '/virtual/checkout/onepage/4-Part-Exchange';
                        checkoutPageTitle = 'Checkout - Your Trade-In';
                        checkoutStepTags = {
                            'checkout': {
                                'actionField': {
                                    'step': 3
                                },
                                'products': [this.productsObject]
                            }
                        };
                        break;
                    case 3:
                        checkoutPage = '/virtual/checkout/onepage/5-Payment';
                        checkoutPageTitle = 'Checkout - Payment';
                        checkoutStepTags = {
                            'checkout': {
                                'actionField': {
                                    'step': 4
                                },
                                'products': [this.productsObject]
                            }
                        };
                        break;
                    case 4:
                        checkoutPage = '/virtual/checkout/onepage/6-Details';
                        checkoutPageTitle = 'Checkout - Apply for Finance';
                        checkoutStepTags = {
                            'checkout': {
                                'actionField': {
                                    'step': 5
                                },
                                'products': [this.productsObject]
                            }
                        };
                        break;
                    case 5:
                        checkoutPage = '/virtual/checkout/onepage/8-Summary';
                        checkoutPageTitle = 'Checkout - Summary';
                        checkoutStepTags = {
                            'checkout': {
                                'actionField': {
                                    'step': 6
                                },
                                'products': [this.productsObject]
                            }
                        };
                        break;
                    default:
                        break;
                }

                pushTags('VirtualPageview', checkoutPage, checkoutPageTitle);
                pushEcommerceTags({
                    'event': 'checkout',
                    'ecommerce': checkoutStepTags
                });
            }
        }
    });
</script>
