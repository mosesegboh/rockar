/**
 * Class with native object helpers.
 *
 * @author Arturs Lataks <techteam@rockar.com>
* @copyright Copyright (c) 2016 Rockar Ltd (http://rockar.com)
 */

class ObjectHelpers {
    /**
     * Constructor
     *
     * @param {{}} object Object to operate with
     */
    constructor(object) {
        this.object = object;
    }

    /**
     * Get key from object returning default value if key value is not found in object.
     *
     * @param {string} key Key to retrieve from object
     * @param {*} defaultValue Default value to return in case of requested value is not found in object.
     */
    getValue(key, defaultValue = false) {
        return this.object.hasOwnProperty(key) ? this.object[key] : defaultValue;
    }

    /**
     * Get nested value from the object by dot notated key.
     *
     * @param {string} path Dot notated path to value
     * @returns {*}
     */
    getNestedValue(path) {
        let object = this.object;
        path = path.split('.');
        while (path.length && (object = object[path.shift()]));

        return object;
    }

    /**
     * Function checks if (nested) key exists in an object
     *
     * @param {string} path Dot notated path to object key
     * @returns {boolean}
     */
    nestedKeyExists(path) {
        let object = this.object;
        path = path.split('.');

        for (var i = 0; i < path.length; i++) {
            if (!object || !object.hasOwnProperty(path[i])) {
                return false;
            }

            object = object[path[i]];
        }

        return true;
    }
}
