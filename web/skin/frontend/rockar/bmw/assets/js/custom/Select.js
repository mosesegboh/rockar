/**
*
* Alternative solution for prototype
* using ES6 Class to work with Custom Select Boxes
*
* @author Vjaceslavs Hlutkovs <techteam@rockar.com>
* @copyright Copyright (c) 2016 Rockar Ltd (http://rockar.com)
*/

class Select {
    // Expecting to get select jQuery element
    constructor(selectEl, touchDisplay, disabled, elements, prefix) {
        this.$selectEl = selectEl;
        this.$selectParent = null;
        this.elNum = elements;
        this.disabled = disabled;
        this.prefix = prefix;

        this.customClass = null;
        this.selectName = null;
        this.selectParentSelector = null;
        this.selectSelector = null;
        this.selectNamespace = null;
        this.selectTitleSelector = null;
        this.selectDropdownSelector = null;
        this.selectDropdownItemSelector = null;

        this.totalElements = this.$selectEl.find('option').length - 1;

        this.tempKey = null;
        this.tempIndex = [];
        this.tempBody = true;
        this.highlightIndex = 0;

        this.init();
    }

    init() {
        if (!this.$selectEl.length) {
            this.sendException('Could not find element in DOM');
        } else if (!this.$selectEl.attr('name')) {
            this.sendException('Select element must have "name" attribute');
        } else {
            this.customClass = this.$selectEl.attr('class');
            this.selectName = this.$selectEl.attr('name');

            this.selectNamespace = this.selectName.replace(/\[|\]/g, '');

            this.selectParentSelector = `.selectbox-wrapper[data-id="${this.selectName}"]`;
            this.selectWrapperSelector = `${this.selectParentSelector} .selectbox`;
            this.selectSelector = `${this.selectParentSelector} select`;
            this.selectTitleSelector = `${this.selectParentSelector} .selectbox-select`;
            this.selectDropdownSelector = `${this.selectParentSelector} .selectbox-dropdown`;
            this.selectDropdownItemSelector = `${this.selectDropdownSelector} .dropdown-item`;

            this.renderSelect();
            this.toggleDisabled(this.disabled);
            this.eventHandlers();
        }
    }

    eventHandlers() {
        jQuery('body').on(`click.selectboxSelectItem_${this.selectNamespace}`, this.selectDropdownItemSelector, this.selectboxSelectItem.bind(this));
        jQuery('body').on(`keyup.jumpToCharacter_${this.selectNamespace}`, this.jumpToCharacter.bind(this));
        jQuery('body').on(`click.bodyClick_${this.selectNamespace}`, this.bodyClick.bind(this));
        jQuery('body').on(`focus.selecboxToggle_${this.selectNamespace}`, this.selectTitleSelector, this.selectboxToggle.bind(this));
        jQuery('body').on(`keydown.highlightItem${this.selectNamespace}`, this.handleControls.bind(this));
    }

    destroy() {
        jQuery('body').off(`click.selectboxSelectItem_${this.selectNamespace}`);
        jQuery('body').off(`keyup.jumpToCharacter_${this.selectNamespace}`);
        jQuery('body').off(`click.bodyClick_${this.selectNamespace}`);
        jQuery('body').off(`focus.selecboxToggle_${this.selectNamespace}`);
        jQuery('body').off(`keydown.highlightItem${this.selectNamespace}`);
    }

    handleControls(event) {
        if (this.isOpened()) {
            if (event.keyCode === 38) { // up
                event.preventDefault();
                this.highlightItem(-1);
            } else if (event.keyCode === 40) { // down
                event.preventDefault();
                this.highlightItem(1);
            } else if (event.keyCode === 9) { // tab
                event.preventDefault();
            } else if (event.keyCode === 27) { // esc
                event.preventDefault();
                this.selectboxToggle();
            } else if (event.keyCode === 32 || event.keyCode === 13) { // space & enter
                event.preventDefault();
                this.selectboxManualSelect(this.highlightIndex + 1);
            }
        }
    }

    highlightItem(dir) {
        if (dir === 1 && this.highlightIndex < this.totalElements) {
            this.highlightIndex = this.highlightIndex + 1;
            this.setHighlight(this.highlightIndex + 1);
        } else if (dir === -1 && this.highlightIndex > 0) {
            this.highlightIndex = this.highlightIndex - 1;
            this.setHighlight(this.highlightIndex + 1);
        }
    }

    setHighlight(id) {
        jQuery(this.selectDropdownItemSelector).removeClass('highlighted');
        jQuery(`${this.selectDropdownItemSelector}:nth-child(${id})`).addClass('highlighted');
        jQuery(this.selectDropdownSelector).scrollTop(jQuery(this.selectDropdownSelector).scrollTop() + jQuery(`${this.selectDropdownItemSelector}:nth-child(${id})`).position().top);
    }

    bodyClick() {
        if (this.tempBody && this.isOpened()) {
            this.selectboxToggle();
        }
    }

    checkValidation() {
        if (this.$selectEl.hasClass('validation-advice')) {
            jQuery(this.selectWrapperSelector).addClass('validation-error');
        } else {
            jQuery(this.selectWrapperSelector).removeClass('validation-error');
        }
    }

    getTitle() {
        var title;
        var prefix = this.prefix;

        if (this.$selectEl.find('option:selected').text()) {
            title = this.$selectEl.find('option:selected').text();
        } else {
            title = this.$selectEl.find('option').first().text();
        }

        if (typeof prefix === 'undefined') {
            prefix = '-';
        }

        return (title.trim().length) ? title : prefix;
    }

    getValues() {
        var optionsEl = jQuery(`${this.selectSelector} option`);
        var optionsArray = [];
        optionsEl.each((e) => {
            var tempData = {
                title: jQuery(e).text(),
                value: jQuery(e).val()
            };

            optionsArray.push(tempData);
        });

        return optionsArray || false;
    }

    isOpened() {
        return jQuery(this.selectDropdownSelector).is(':visible');
    }

    resetHighlight() {
        this.tempKey = null;
        this.tempIndex = [];
    }

    checkValue(pressedCharacter) {
        var pressedConvertedCharacter = String.fromCharCode(pressedCharacter);

        var _self = this;
        var values = this.getValues();

        if (!values) {
            return -1;
        }

        var matching = -1;

        for (var i = 0; i < values.length; i++) {
            var firstCharacter = values[i].title.charAt(0).toUpperCase();

            if (firstCharacter === pressedConvertedCharacter && _self.tempIndex.indexOf(i) === -1) {
                _self.tempIndex.push(i);
                matching = i;
                break;
            } else if (i + 1 === values.length) {
                matching = _self.tempIndex[0];
                _self.tempIndex = [_self.tempIndex[0]];
            }
        }

        return matching;
    }

    selectboxManualSelect(id) {
        var itemEl = jQuery(`${this.selectDropdownItemSelector}:nth-child(${id})`);
        var itemValue = itemEl.data('value');
        var itemTitle = itemEl.text();

        jQuery(`${this.selectTitleSelector} span`).text(itemTitle);

        this.$selectEl.val(itemValue);
        this.$selectEl.trigger('change');

        this.checkValidation();
        this.selectboxToggle();
    }

    selectboxSelectItem(e) {
        var itemEl = jQuery(e.target);
        var itemValue = itemEl.data('value');
        var itemTitle = itemEl.text();

        jQuery(`${this.selectTitleSelector} span`).text(itemTitle);

        this.$selectEl.val(itemValue);
        this.$selectEl.trigger('change');

        this.checkValidation();

        this.selectboxToggle();
    }

    selectboxToggle() {
        this.tempBody = false;

        setTimeout(() => {
            this.tempBody = true;
        }, 500);

        if (!this.disabled) {
            jQuery(this.selectParentSelector).find('.selectbox').toggleClass('open');
            jQuery(this.selectDropdownSelector).toggle();

            if (this.isOpened()) {
                this.highlightIndex = 0;
                this.setHighlight(1);
                this.resetHighlight();
            } else {
                document.activeElement.blur();
            }
        }
    }

    toggleDisabled(isDisabled) {
        var selectbox = jQuery(this.selectParentSelector).find('.selectbox');

        if (typeof isDisabled === 'undefined') {
            if (this.disabled) {
                this.disabled = false;
                selectbox.removeClass('disabled');
            } else {
                this.disabled = true;
                selectbox.addClass('disabled');
            }
        } else if (isDisabled) {
            this.disabled = true;
            selectbox.addClass('disabled');
        } else {
            this.disabled = false;
            selectbox.removeClass('disabled');
        }
    }

    generateSelectHTML() {
        var selectTitle = this.getTitle();
        var selectItems = this.$selectEl.find('option');
        var customClass = this.customClass ? this.customClass : '';
        var prefix = this.prefix;

        var html = `
            <div class="selectbox-wrapper ${customClass}" data-id="${this.selectName}">
                <div class="selectbox">
                    <div class="selectbox-select" tabindex="0">
                        <span>${selectTitle}</span>
                        <div class="dropdown-caret"></div>
                    </div>
                    <ul class="selectbox-dropdown">
        `;

        if (typeof prefix === 'undefined') {
            prefix = '-';
        }

        selectItems.each((e) => {
            var title = jQuery(e).text().trim().length ? jQuery(e).text() : prefix;
            html += `<li class="dropdown-item" data-value="${jQuery(e).val()}">${title}</li>`;
        });

        html += `
                    </ul>
                </div>
            </div>
        `;

        return html;
    }

    jumpToCharacter(e) {
        const keyCode = e.keyCode;

        if (this.tempKey !== e.keyCode) {
            this.tempKey = e.keyCode;
            this.tempIndex = [];
        }

        if (this.isOpened()) {
            const matchID = this.checkValue(keyCode);

            if (matchID > -1) {
                const index = matchID + 1;
                jQuery(`${this.selectDropdownItemSelector} .highlighted`).removeClass('highlighted');
                jQuery(`${this.selectDropdownItemSelector}:nth-child(${index})`).addClass('highlighted');
                jQuery(this.selectDropdownSelector).scrollTop(jQuery(this.selectDropdownSelector).scrollTop() + jQuery(`${this.selectDropdownItemSelector}:nth-child(${index})`).position().top);
            } else {
                jQuery(`${this.selectDropdownItemSelector} .highlighted`).removeClass('highlighted');
            }
        }
    }

    adjustHeight() {
        var _self = this;
        var totalElements = this.$selectEl.find('option').length;
        var elementsNumber = totalElements < _self.elNum ? totalElements : _self.elNum;
        var itemElHeight = jQuery(_self.selectTitleSelector).outerHeight();
        var selectDropdownHeight = itemElHeight * elementsNumber;
        jQuery(_self.selectDropdownSelector).css('height', `${selectDropdownHeight}px`);
    }

    renderSelect() {
        var html = this.generateSelectHTML();

        this.$selectEl.before(html);
        this.$selectEl.css({
            'visibility': 'hidden',
            'position': 'absolute'
        });
        this.$selectEl.parents('form').validate();
        this.$selectEl.parents('form').on('submit', this.checkValidation.bind(this));

        this.adjustHeight();
    }

    sendException(msg) {
        console.error('Select:', msg);
    }
}
