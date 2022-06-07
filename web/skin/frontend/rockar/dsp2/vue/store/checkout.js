export default {
    state: {
        deliveryAddress: false,
        activeStepIndex: 0
    },

    mutations: {
        changeDeliveryAddress(state, address) {
            state.deliveryAddress = address;
        },

        setActiveStepIndex(state, step) {
            state.activeStepIndex = step;
        }
    }
}
