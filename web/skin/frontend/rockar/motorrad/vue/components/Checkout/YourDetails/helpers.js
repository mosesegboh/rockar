import moment from 'moment';

export default {
    computed: {
        totalForms() {
            return this.formsFilled.length;
        },
        selectFromDate() {
            let now = {
                month: moment().month() + 1,
                year: moment().year()
            }

            if (this.totalForms > 0) {
                if (typeof this.residenceMinYears !== 'undefined') {
                    now = {
                        month: Number(this.formsFilled[this.totalForms - 1].month_address_from),
                        year: Number(this.formsFilled[this.totalForms - 1].year_address_from)
                    }
                } else {
                    now = {
                        month: Number(this.formsFilled[this.totalForms - 1].month_employment_from),
                        year: Number(this.formsFilled[this.totalForms - 1].year_employment_from)
                    }
                }
            }

            const data = {
                months: [],
                years: []
            }

            this.dates.months.forEach((month) => {
                if (typeof this.residenceMinYears !== 'undefined') {
                    if (this.formData.year_address_from && Number(this.formData.year_address_from) === now.year) {
                        if (Number(month) <= now.month) {
                            data.months.push(Number(month));
                        }
                    } else {
                        data.months.push(Number(month));
                    }
                } else {
                    if (this.formData.year_employment_from && Number(this.formData.year_employment_from) === now.year) {
                        if (Number(month) <= now.month) {
                            data.months.push(Number(month));
                        }
                    } else {
                        data.months.push(Number(month));
                    }
                }
            });

            this.dates.years.forEach((year) => {
                if (year <= now.year) {
                    data.years.push(Number(year));
                }
            });

            data.years = this.createSelect(false, data.years);
            data.months = this.createSelect(false, data.months);

            return data;
        },

        selectToDate() {
            let fromDate = {};
            let toDate = {};

            // If it's RESIDENCE form
            if (typeof this.residenceMinYears !== 'undefined') {
                fromDate = {
                    month: Number(this.formData.month_address_from),
                    year: Number(this.formData.year_address_from)
                }

                toDate = {
                    month: Number(this.formData.month_address_to),
                    year: Number(this.formData.year_address_to)
                }
            } else { // If it's EMPLOYMENT form
                fromDate = {
                    month: Number(this.formData.month_employment_from),
                    year: Number(this.formData.year_employment_from)
                }

                toDate = {
                    month: Number(this.formData.month_employment_to),
                    year: Number(this.formData.year_employment_to)
                }
            }

            let now = {
                month: moment().month() + 1,
                year: moment().year()
            }

            if (this.totalForms > 0) {
                if (typeof this.residenceMinYears !== 'undefined') {
                    now = {
                        month: Number(this.formsFilled[this.totalForms - 1].month_address_from),
                        year: Number(this.formsFilled[this.totalForms - 1].year_address_from)
                    }
                } else if (this.formsFilled[this.totalForms - 1].employment_status !== '2') {
                    now = {
                        month: Number(this.formsFilled[this.totalForms - 1].month_employment_from),
                        year: Number(this.formsFilled[this.totalForms - 1].year_employment_from)
                    }
                }
            }

            const data = {
                months: [],
                years: []
            }

            // If there is "FROM YEAR"
            if (fromDate.year) {
                // We push all years that are equal or above selected year
                this.dates.years.forEach((year) => {
                    if (year >= fromDate.year && year <= now.year) {
                        data.years.push(Number(year));
                    }
                });
            } else { // If there is no "FROM YEAR"
                // We push all year available in initial array
                this.dates.years.forEach((year) => {
                    if (year <= now.year) {
                        data.years.push(Number(year));
                    }
                });
            }

            // If there is "TO YEAR"
            if (toDate.year) {
                // If there is "FROM YEAR" and "FROM MONTH"
                if (fromDate.year && fromDate.month) {
                    // If "TO YEAR" equals to "FROM YEAR"
                    if (toDate.year === fromDate.year) {
                        // If "TO/FROM YEAR" equals to "CURRENT YEAR"
                        if (toDate.year === now.year) {
                            // Push all the months that are below or equals to "CURRENT MONTH" and above or equals to "FROM YEAR"
                            this.dates.months.forEach((month) => {
                                if (Number(month) <= now.month && Number(month) >= fromDate.month) {
                                    data.months.push(Number(month));
                                }
                            });
                        } else { // If "TO/FROM YEAR" not equals to "CURRENT YEAR"
                            // Push all the months that are above or equals to "FROM YEAR"
                            this.dates.months.forEach((month) => {
                                if (Number(month) >= fromDate.month) {
                                    data.months.push(Number(month));
                                }
                            });
                        }
                    } else { // If "TO YEAR" is not equals to "FROM YEAR"
                        // If "TO YEAR" equals to "CURRENT YEAR"
                        if (toDate.year === now.year) {
                            // Push all the months that are below or equals to "CURRENT MONTH"
                            this.dates.months.forEach((month) => {
                                if (Number(month) <= now.month) {
                                    data.months.push(Number(month));
                                }
                            });
                        } else { // If "TO YEAR" not equals to "CURRENT YEAR"
                            // Push all the months that are in the initial array
                            this.dates.months.forEach((month) => {
                                data.months.push(Number(month));
                            });
                        }
                    }
                } else { // If there is no "FROM YEAR" and/or "FROM MONTH"
                    // If "TO YEAR" equals to "CURRENT YEAR"
                    if (toDate.year === now.year) {
                        // Push all the months that are below or equals to "CURRENT MONTH"
                        this.dates.months.forEach((month) => {
                            if (Number(month) <= now.month) {
                                data.months.push(Number(month));
                            }
                        });
                    } else { // If "TO YEAR" is not equals to "CURRENT YEAR"
                        // Push all the months
                        this.dates.months.forEach((month) => {
                            data.months.push(Number(month));
                        });
                    }
                }
            } else { // If there is no "TO YEAR"
                this.dates.months.forEach((month) => {
                    data.months.push(Number(month));
                });
            }

            data.months = this.createSelect(false, data.months);
            data.years = this.createSelect(false, data.years);

            return data;
        }
    },

    methods: {
        selectCountry(data) {
            this.formData.country = data.value;
        },

        getCountryLabel(value) {
            let result = '';

            this.countries.forEach((country) => {
                if (country.value === value) {
                    result = country.label;
                }
            });

            return result;
        },

        createCustomSelect(num) {
            const result = [];

            for (let i = 0; i !== num; i++) {
                result.push({
                    title: String(i),
                    value: String(i)
                });
            }

            return result;
        },

        createSelect(isObject, list, keyLabel = false, keyValue = false) {
            const options = [];

            if (isObject) {
                Object.keys(list).forEach((item) => {
                    options.push({
                        title: list[item],
                        value: item
                    });
                });
            } else {
                list.forEach((item) => {
                    if (!keyLabel && !keyValue) {
                        options.push({
                            title: item,
                            value: String(item)
                        });
                    } else {
                        if (item[keyLabel] && item[keyValue]) {
                            options.push({
                                title: item[keyLabel],
                                value: item[keyValue]
                            });
                        }
                    }
                });
            }

            return options;
        },

        formRemove() {
            if (this.completed) {
                this.$emit('fail');
            }

            this.$broadcast('Message::show');

            if (this.totalForms && this.formsFilled[this.totalForms - 1].employment_status !== '2') {
                this.formData = this.formsFilled[this.totalForms - 1];
            } else {
                this.formData = this.getBlankForm();
            }

            this.formsFilled.pop();
            this.showManual = false;

            this.$nextTick(() => {
                pca.magento.load();
            });
        }
    },

    created() {
        if (this.isCompleted) {
            this.$emit('success');
        } else {
            this.$nextTick(() => {
                this.$broadcast('Messages::show');
            });
        }
    }
}
