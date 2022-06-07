/**
 * @category     Peppermint
 * @package      Peppermint\ExtendedRules
 * @author       Dumitru Mocanu <dumitru.mocanu@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
ModelByBrand.addMethods({
    updateFields: function (models) {
        this.targetElement.update(models.map(function (value) {
            return '<option value="' + value.ID + '">' + value.Model + '</option>';
        }).join(''));

        if (parseInt(this.getOldBrandValue()) === this.getSelectedBrand()) {
            this.preselectFields();
        }
    },

    getSelectedBrand: function () {
        if ($(this.brandElement)) {
            const brandId = parseInt($(this.brandElement).select('option:selected').first().readAttribute('value'));

            return isNaN(brandId) ? false : brandId;
        }

        return false;
    }
});
