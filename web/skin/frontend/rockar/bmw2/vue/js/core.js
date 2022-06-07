// Car Finder
import appModelFilter from 'bmw2/components/CarFinder/ModelFilter.vue';

let CoreComponents = {
    appModelFilter
};

var ModuleComponents = require('./modules');
var Dsp2CoreComponents = require('../../../dsp2/vue/js/core');

CoreComponents = Object.assign(Dsp2CoreComponents.default, CoreComponents);

Object.keys(ModuleComponents.exportArr).forEach((module) => {
    CoreComponents = Object.assign(ModuleComponents.exportArr[module], CoreComponents);
});

export default CoreComponents;
