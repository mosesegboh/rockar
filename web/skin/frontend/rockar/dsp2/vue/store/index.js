import storeGeneral from 'dsp2/store/general';
import StoreCarFinder from 'dsp2/store/carFinder';
import StoreCheckout from 'dsp2/store/checkout';

export default {
    modules: {
        general: storeGeneral,
        carFinder: StoreCarFinder,
        checkout: StoreCheckout
    },
}
