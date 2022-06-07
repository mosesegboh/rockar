import coreSaveCar from 'core/components/Shared/SaveCar';

export default {
    mixins: [coreSaveCar],

    data() {
        return {
            openSave: false,
            ajaxLoading: false,
            resultReceived: false,
            serverError: false,
            duplicateError: false,
            validationResult: true,
            myAccountUrlFull: `${this.myAccountUrl}#my-saved-cars`,
            customName: `${this.customerName}'s the ${this.productName}`
        };
    },

    methods: {
        // rewrite of core validation for saveCare | edited only regex to take accented characters.
        validateForm() {
            const pattern = /^[a-zA-ZÀ-ÿ0-9_+-.,!@#$%^&*();=:{}£/|~`<>!?'"\[\]§\s ]+$/ug;
            this.validationResult = this.customName.trim().length !== 0 &&
                                    this.customName.length < 255 &&
                                    Boolean(this.customName.trim().match(pattern));
        }
    }
};
