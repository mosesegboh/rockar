export default {
    data() {
        return {
            regex: {
                letters: /[^A-Za-z\s]/g,
                numbers: /[^0-9]/g
            }
        }
    },
    methods: {
        defaultFilter(value, regex, defaultValue) {
            if (!value) return defaultValue;

            return value.replace(regex, '');
        }
    },
    filters: {
        lettersOnly: {
            read(letter) {
                return this.defaultFilter(letter, this.regex.letters, '');
            },
            write(letter) {
                return this.defaultFilter(letter, this.regex.letters, '');
            }
        },
        numbersOnly: {
            read(numbers) {
                if (!numbers) return 0;

                return numbers.toString().replace(this.regex.numbers, '');
            },
            write(numbers) {
                if (!numbers) return 0;

                return numbers.toString().replace(this.regex.numbers, '');
            }
        },
        numbersOrEmpty: {
            read(numbers) {
                return this.defaultFilter(numbers, this.regex.numbers, '');
            },
            write(numbers) {
                return this.defaultFilter(numbers, this.regex.numbers, '');
            }
        }
    }
};
