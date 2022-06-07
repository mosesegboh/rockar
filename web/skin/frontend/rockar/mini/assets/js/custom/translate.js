class Translate {
    // Pass JSON object with translations
    constructor(data) {
        this.translations = data;
    }

    // Get list of translations
    getTranslations() {
        return this.translations;
    }

    // Return translation of the String
    translate(translateString) {
        if (this.translations[translateString]) {
            return this.translations[translateString];
        }

        return translateString;
    }

    // Add translations to Object
    add(originalTranslation, translateString) {
        if (typeof originalTranslation === 'object') {
            this.addObject(originalTranslation);
        } else {
            this.addString(originalTranslation, translateString);
        }
    }

    // Add transtions by passing two strings
    addString(originalString, translateString) {
        if (this.translations[originalString]) {
            throw `Translator: string "${originalString}" already exist in translations object`;
        } else {
            this.translations[originalString] = translateString;
        }
    }

    // Add transtions by passing object
    addObject(translationObject) {
        for (var key of Object.keys(translationObject)) {
            this.addString(key, translationObject[key]);
        }
    }
}
