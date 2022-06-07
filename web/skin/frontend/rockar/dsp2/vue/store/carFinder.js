export default {
    state: {
        carFilters: [],
        step: 0,
        sortOrder: ''
    },

    mutations: {
        setCarFilters(state, filters) {
            state.carFilters = filters;
        },

        setCarFilterStep(state, step) {
            state.step = step;
        },

        setSortOrder(state, value) {
            state.sortOrder = value;
        }
    }
}
