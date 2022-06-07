/**
 * Needed Mage js
 * -------------------------------------------------------------------------------------------------
 */

(function($, window, undefined) {
    var jMage = window.jMage = window.jMage || {};

    // misc functions
    $.extend(jMage, {
        toQueryParams(paramString, separator) {
            var match = $.trim(paramString).match(/([^?#]*)(#.*)?$/),
                parts,
                hash = {};

            if (!match) return {};

            parts = match[1].split(separator || '&');
            for (var i = 0, n = parts.length; i < n; i++) {
                var pair,
                    key,
                    value;

                if ((pair = parts[i].split('='))[0]) {
                    key = decodeURIComponent(pair.shift());
                    value = pair.length > 1 ? pair.join('=') : pair[0];

                    if (typeof value !== 'undefined') {
                        value = decodeURIComponent(value);
                    }

                    if (key in hash) {
                        if (!$.isArray(hash[key])) {
                            hash[key] = [hash[key]];
                        }
                        hash[key].push(value);
                    } else {
                        hash[key] = value;
                    }
                }
            }

            return hash;
        },


        /**
         * Formats currency using patern
         * format - JSON (pattern, decimal, decimalsDelimeter, groupsDelimeter)
         * showPlus - true (always show '+'or '-'),
         *      false (never show '-' even if number is negative)
         *      null (show '-' if number is negative)
         */

        formatCurrency(price, format, showPlus) {
            var precision = isNaN(format.precision = Math.abs(format.precision)) ? 2 : format.precision;
            var requiredPrecision = isNaN(format.requiredPrecision = Math.abs(format.requiredPrecision)) ? 2 : format.requiredPrecision;

            // precision = (precision > requiredPrecision) ? precision : requiredPrecision;
            // for now we don't need this difference so precision is requiredPrecision
            precision = requiredPrecision;

            var integerRequired = isNaN(format.integerRequired = Math.abs(format.integerRequired)) ? 1 : format.integerRequired;

            var decimalSymbol = typeof format.decimalSymbol === 'undefined' ? ',' : format.decimalSymbol;
            var groupSymbol = typeof format.groupSymbol === 'undefined' ? '.' : format.groupSymbol;
            var groupLength = typeof format.groupLength === 'undefined' ? 3 : format.groupLength;

            var s = '';

            if (typeof showPlus === 'undefined' || showPlus === true) {
                var x = (showPlus ? '+' : '');
                s = price < 0 ? '-' : x;
            } else if (showPlus === false) {
                s = '';
            }

            var i = String(parseInt(price = Math.abs(+price || 0).toFixed(precision)));
            var pad = (i.length < integerRequired) ? (integerRequired - i.length) : 0;
            while (pad) {
                i = `0${i}`;
                pad--;
            }
            j = (j = i.length) > groupLength ? j % groupLength : 0;
            re = new RegExp(`(\\d{${groupLength}})(?=\\d)`, 'g');

            /**
             * replace(/-/, 0) is only for fixing Safari bug which appears
             * when Math.abs(0).toFixed() executed on "0" number.
             * Result is "0.-0" :(
             */
            var r = (j ? i.substr(0, j) + groupSymbol : '') + i.substr(j).replace(re, `$1${groupSymbol}${(precision ? decimalSymbol + Math.abs(price - i).toFixed(precision).replace(/-/, 0).slice(2) : '')}`);
            var pattern = '';
            if (format.pattern.indexOf('{sign}') === -1) {
                pattern = s + format.pattern;
            } else {
                pattern = format.pattern.replace('{sign}', s);
            }

            return pattern.replace('%s', r).replace(/^\s\s*/, '').replace(/\s\s*$/, '');
        },

        setLocation: function setLocation(url) {
            window.location.href = url;
        }
    });

    /**
     * Template
     */
    var Template = jMage.Template = function Template(template, pattern) {
        this.initialize(template, pattern);
    }
    Template.Pattern = /(^|.|\r|\n)(#\{(.*?)\})/;
    $.extend(Template.prototype, {
        initialize(template, pattern) {
            this.template = template.toString();
            this.pattern = pattern || Template.Pattern;
        },

        evaluate(object) {
            var result = '',
                source = this.template,
                pattern = this.pattern,
                replacement,
                replace = function(match) {
                    if (object == null) {
                        return (String(match[1]));
                    }

                    var before = match[1] || '';
                    if (before === '\\') {
                        return match[2];
                    }

                    var ctx = object,
                        expr = match[3],
                        replacePattern = /^([^.[]+|\[((?:.*?[^\\])?)\])(\.|\[|$)/;

                    match = replacePattern.exec(expr);
                    if (match == null) return before;

                    while (match != null) {
                        var comp = (match[1].lastIndexOf('[', 0) === 0) ? match[2].replace(/\\\\]/g, ']') : match[1];
                        ctx = ctx[comp];
                        if (null === ctx || '' === match[3]) {
                            break;
                        }

                        expr = expr.substring('[' === match[3] ? match[1].length : match[0].length);
                        match = replacePattern.exec(expr);
                    }

                    ctx = ctx == null ? '' : String(ctx);
                    return before + ctx;
                };

            if (typeof pattern === 'string') {
                pattern = String(pattern).replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
            }

            if (!(pattern.length || pattern.source)) {
                replacement = replace('');
                return replacement + source.split('').join(replacement) + replacement;
            }

            while (source.length > 0) {
                if (match = source.match(pattern)) {
                    result += source.slice(0, match.index);
                    replacement = replace(match);
                    replacement = replacement == null ? '' : String(replacement);
                    result += replacement;
                    source = source.slice(match.index + match[0].length);
                } else {
                    result += source;
                    source = '';
                }
            }

            return result;
        }
    });
})(jQuery, window);
