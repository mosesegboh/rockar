<template>
    <div>
        <label class="{{labelClass}}">{{label}} (Y/M)</label>
            <div class="row">
                <div class="col-5">
                    <input
                        type="text"
                        placeholder="Year(s)"
                        v-on:change="sendFeedback"
                        v-model="years | numbersOnly"
                    />
                </div>
                <div class="col-1">
                    <h2 class="align-items center">/</h2>
                </div>
                <div class="col-6">
                    <app-select
                            @select="setMonthsUpdateParent"
                            title="-"
                            :init-selected="extractSelectedMonth"
                            :options="createSelect(false, this.getMonths, 'label', 'key')"
                            :disabled="disabled">
                    </app-select>
                </div>
            </div>
        <slot id="error-message-container"></slot>
    </div>
</template>
<script>
    import appSelect from 'core/components/Elements/Select';
    import yourDetailsHelpers from 'core/components/Checkout/YourDetails/helpers';
    import inputFilter from 'mini/components/Shared/InputFilters';


    export default Vue.extend({

        mixins: [
            yourDetailsHelpers,
            inputFilter
        ],

        name: 'TimeSpanSelector',

        props: {
            label: String,
            disabled: Boolean,
            labelClass: {
                type: String,
                required: false,
                default: 'side-label'
            },
            maxYear: {
                required: false,
                type: Number,
                default: 50
            },
            timespan: {
                type: Number,
                required: false,
                default: 0
            }
        },

        computed: {
            getMonths() {
                return this.getCollection(11, 'Month', 'Months');
            },

            extractSelectedMonth() {
                return this.extractMonths();
            }
        },

        data() {
            return {
                months: this.extractMonths(),
                years: this.extractYears()
            }
        },

        methods: {

            /**
             * sets month value sends data back to container component.
             * @param e event object.
             */
            setMonthsUpdateParent(e) {
                this.months = this.checkNumber(e.value) ? e.value : 0;
                this.sendFeedback();
            },

            /**
             * sends feedback to the parent component.
             */
            sendFeedback() {
                this.$emit('input', (this.years * 12) + this.months)
            },

            /**
             * Creates an array of objects to populate the app-select component.
             * Default value added with key of true to get around createSelect logic
             * @param maxVal integer, highest value
             * @param singular string, description in singular (Month)
             * @param collection string description in plural or collection (Months)
             * @returns {[]} to populate the app-select component.
             */
            getCollection(maxVal, singular, collection) {
                const toReturn = [];
                toReturn.push({ key: true, label: `0 ${collection}` });
                let description = singular;

                for (let i = 1; i <= maxVal; i++) {
                    toReturn.push({ key: i, label: `${i} ${description}` });
                    description = collection;
                }

                return toReturn;
            },

            /**
             * given the timespan in the lowest common denominator -- months --> 60 months is 5 years.
             * Breaks this value into years.
             * @returns {null|number}
             */
            extractYears() {
                if (!this.timespan || this.timespan === 0) {
                    return null;
                }

                return Math.floor(this.timespan / 12);
            },

            /**
             * when timespan is not a rounded value, timespan % 12 > 0, these will be months.
             * @returns {number} number of months represented by the timespan value.
             */
            extractMonths() {
                if (!this.timespan || this.timespan === 0) {
                    return 0;
                }

                return this.checkNumber(this.timespan % 12) ? this.timespan % 12 : 0;
            },

            /**
             * Centralised point to check if the input given is valid.
             * @param {object} value that will be checked to see if this is a number
             * @returns {boolean} true, this is a number, false this is not a number.
             */
            checkNumber(checkThis) {
                return typeof checkThis === 'number';
            }
        },

        components: {
            appSelect
        }
    });
</script>