/**
*
* Alternative solution for prototype
* using ES6 Class to work with Country/Region fields in Magento.
*
* @author Vjaceslavs Hlutkovs <techteam@rockar.com>
* @copyright Copyright (c) 2016 Rockar Ltd (http://rockar.com)
*/

class Regions {

    // Object of regions, selector for country element, selector for regions element
    constructor(regions, countryEl, regionEl) {
        this.regions = regions;
        this.countrySelector = countryEl;
        this.regionSelector = regionEl;
        this.$countryEl = jQuery(countryEl);
        this.$regionEl = jQuery(regionEl);
        this.disable = false;

        this.init();
    }

    // Initialization checks all of the parameters and element existance in the DOM,
    // in case if something fails send console.error message and disabled plugin
    // in case if everything is fine initializes handlers
    init() {
        if (typeof this.regions === 'undefined') {
            this.sendException('Regions "regions" parameter is missing');
        } else if (typeof this.countrySelector === 'undefined') {
            this.sendException('Country element "countryEl" parameter is missing');
        } else if (typeof this.regionSelector === 'undefined') {
            this.sendException('Region element "regionEl" parameter is missing');
        } else if (jQuery(this.countrySelector).length === 0) {
            this.sendException('Country element was not found in the DOM');
        } else if (jQuery(this.regionSelector).length === 0) {
            this.sendException('Region element was not found in the DOM');
        }

        if (!this.disable) {
            this.eventHandlers();
        }
    }

    // Event Handler declaration
    eventHandlers() {
        this.$countryEl.on('change', this.countryChanged.bind(this));
    }

    // Updating DOM inside variables when input/select is replaced
    updateDOMCache() {
        this.$countryEl = jQuery(this.countrySelector);
        this.$regionEl = jQuery(this.regionSelector);
    }

    // Checks if the region select is required for that country
    isRequired(countryCode) {
        return this.regions.config.regions_required.indexOf(countryCode) > -1;
    }

    // Checks if selected country have regions
    haveRegions(countryCode) {
        return typeof this.regions[countryCode] !== 'undefined';
    }

    // Creates markup of the <select> for regions
    createRegionsSelectHTML(countryCode) {
        var elClasses = this.$regionEl.attr('class');
        var elName = this.$regionEl.attr('name');
        var regions = this.regions[countryCode];
        var html = `<select class="${elClasses}" name="${elName}">`;

        for (var key of Object.keys(regions)) {
            var region = regions[key];
            html += `<option value="${region.code}">${region.name}</option>`;
        }

        html += '</select>';

        return html;
    }

    // Creates markup of the <input> for regions
    createRegionsInputHTML() {
        var elClasses = this.$regionEl.attr('class');
        var elName = this.$regionEl.attr('name');
        var html = `<input type="text" class="${elClasses}" name="${elName}">`;

        return html;
    }

    // Triggers when country is changed.
    // Changes region input/select according to selected option.
    countryChanged() {
        var countryCode = this.$countryEl.val();
        var isRequired = this.isRequired(countryCode);
        var haveRegions = this.haveRegions(countryCode);

        if (isRequired) {
            this.$regionEl.addClass('required-entry');
        }

        if (haveRegions) {
            this.renderRegionsSelect(countryCode);
        } else {
            this.renderRegionsInput();
        }
    }

    // Renders region <select> on the page by replaing previous element
    renderRegionsSelect(countryCode) {
        var html = this.createRegionsSelectHTML(countryCode);
        this.$regionEl.replaceWith(html);
        this.updateDOMCache();
    }

    // Renders region <input> on the page by replaing previous element
    renderRegionsInput() {
        var html = this.createRegionsInputHTML();
        this.$regionEl.replaceWith(html);
        this.updateDOMCache();
    }

    // Disabled plugin and send exception message in console.
    sendException(msg) {
        this.disable = true;
        console.error('Regions:', msg);
    }

}
