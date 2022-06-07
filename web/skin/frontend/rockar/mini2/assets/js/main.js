import Vuex from 'vuex';
import VueResource from 'vue-resource';
import VueValidator from 'vue-validator';
import coreStore from 'core/store';
import brandStore from 'dsp2/store';
import 'babel-polyfill';

// import vendors
import numeral from 'numeral';

Vue.config.debug = !PRODUCTION;
Vue.config.devtools = !PRODUCTION;

// change locale to uk.
const locale = {
    delimiters: {
        thousands: ',',
        decimal: '.'
    },

    abbreviations: {
        thousand: 'k',
        million: 'm',
        billion: 'b',
        trillion: 't'
    },

    ordinal(number) {
        return number;
    },

    currency: {
        symbol: 'R'
    }
};
numeral.register('locale', 'en-za', locale);
numeral.locale('en-za');
numeral.defaultFormat(`$0${locale.delimiters.thousands}0${locale.delimiters.decimal}00`);

// utils
import initSideImages from 'core/utils/SideImage';
import appFormValidation from 'core/utils/FormValidation';
import appCmsBlock from 'core/utils/CmsBlock';
import VueOOP from 'core/utils/vue-oop';

// import components
import coreComponents from 'mini2/js/core';
const Components = Object.assign(coreComponents);

// Filters import and declaration
import filters from 'dsp2/filters';

filters.forEach((filter) => {
    Vue.filter(filter.name, filter.content);
});

// Transition import and declaration
import transitions from 'core/transitions';

transitions.forEach((transition) => {
    Vue.transition(transition.name, transition.content);
});

/* eslint-disable no-new */
Vue.config.optionMergeStrategies.events = Vue.config.optionMergeStrategies.methods;
Vue.use(Vuex);
Vue.use(VueResource);
Vue.use(VueValidator);
Vue.use(VueOOP);

// import mixins
import UrlParser from 'dsp2/mixins/UrlParser';
import VideoListener from 'dsp2/mixins/VideoListener';

// Validations import and declaration
import validations from 'dsp2/utils/VueValidation';

validations.forEach((validation) => {
    Vue.validator(validation.name, validation.content);
});

Vue.config.debug = true;

window.EventsBus = new Vue();

const navigationalKeyChars = [8, 9, 16, 17, 18, 20, 32, 33, 34, 35, 36, 37, 38, 39, 40, 45, 46];

window.app = new Vue({
    mixins: [UrlParser, VideoListener],
    el: 'body',
    store: new Vuex.Store(jQuery.extend(true, coreStore, brandStore)),

    data: {
        currencySymbol,
        toggleMainNavigation: false,
        emailFieldValue: null,
        resizeTime: null,
        logoutRedirectUrl: null,
        RESPONSIVE_BREAKPOINTS: {
            MIN_MOBILE: 600,
            MOBILE: 736,
            TABLET: 1024,
            MIN_MOBILE_HEIGHT: 720,
            HORIZONTAL_BAR_MODE: 900
        }
    },

    computed: {
        currentNavigationButtonVisibility() {
            return jQuery('.navigation-menu-wrapper').is(':visible');
        }
    },

    methods: {
        handleResize() {
            var button = jQuery('.navigation-toggle-button');

            if (button.is(':visible')) {
                this.toggleMainNavigation = false;
            } else {
                this.toggleMainNavigation = true;
            }

            clearTimeout(this.resizeTime);
            this.resizeTime = null;
            this.resizeTime = setTimeout(() => {
                this.alignContents(true);
            }, 200);
        },

        initSideImages,

        initCalendar() {
            jQuery('.single-calendar').datepicker({
                minDate: 0,
                prevText: '',
                nextText: ''
            });
        },

        initFormValidations() {
            new appFormValidation();
        },

        initCurrencyFormatting() {
            const inputs = jQuery('input.currency:not(.currency-validation-active)');
            inputs.on('keydown', (e) => {
                // allow cursor to move.
                const keyCode = e.keyCode;
                // arrows, home, end, delete, backspace
                if (navigationalKeyChars.includes(keyCode)) {
                    return;
                }
                if (!((keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105))) {
                    e.preventDefault();
                }
            });

            inputs.on('blur', (e) => {
                const formatValue = jQuery(e.target).val();
                jQuery(e.target).val(numeral(formatValue).format());
            });

            inputs.addClass('currency-validation-active');
        },

        initDigitOnlyInput() {
            const inputs = jQuery('input.digit:not(.digit-active)');
            inputs.on('keydown', (e) => {
                const keyCode = e.keyCode;
                if (navigationalKeyChars.includes(keyCode) || (keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105)) {
                    return true;
                } else {
                    e.preventDefault();
                }
            });
            inputs.addClass('digit-active');
        },

        initLetterOnlyInput() {
            const inputs = jQuery('input.letters-only-text-input:not(.letters-only-text-input-active)');
            inputs.on('keydown', (e) => {
                const keyCode = e.keyCode;
                if (navigationalKeyChars.includes(keyCode) || (keyCode >= 65 && keyCode <= 90)) {
                    return true;
                } else {
                    e.preventDefault();
                }
            });
            inputs.addClass('letters-only-text-input-active');
        },

        initNumberFormatting() {
            // fail safe if reinitialised
            var inputs = jQuery('input.number:not(.number-validation-active)');

            inputs.on('focus', (e) => {
                jQuery(e.target).val(0);
            });

            inputs.on('keydown', (e) => {
                var fieldVal = numeral(jQuery(e.target).val()).value();
                var fieldMaxLength = jQuery(e.target).data('length');
                var fieldLength = fieldVal.toString().length;
                if (fieldLength < parseInt(fieldMaxLength) && (e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) {
                    this.selectionStart = this.selectionEnd = this.value.length;
                } else if (e.keyCode !== 8) {
                    e.preventDefault();
                }
            });

            inputs.on('keydown', (e) => {
                e.preventDefault();
                var el = jQuery(e.target);
                var regularValue;

                if (e.keyCode !== 8) {
                    regularValue = el.val() + String.fromCharCode(e.keyCode);
                } else {
                    regularValue = el.val().slice(0, -1);
                }

                if (regularValue) {
                    var numberValue = parseInt(regularValue.split(',').join(''));
                    var newNumber = numeral(numberValue).format('0,0');
                    el.val(newNumber);
                } else {
                    el.val(0);
                }
            });

            inputs.on('click', (e) => {
                var valLength = jQuery(e.target).val().length * 2;
                jQuery(e.target)[0].setSelectionRange(valLength, valLength);
            });

            inputs.addClass('number-validation-active');
        },

        openInModal(id) {
            this.$refs[id].show = true;
        },

        resetFilters(status) {
            if (status) {
                this.$broadcast('CarFinder::resetFilters');
            }

            this.$refs.resetFilterModal.show = false;
        },

        closeAllAccordion() {
            var child = this.$children;
            var collapse = true;

            child.forEach((comp) => {
                if (typeof comp.cName !== 'undefined' && comp.cName === 'accordion' && !comp.show) {
                    collapse = false;
                }

                /**
                 * Could be that the child is accordion group and not accordions
                 */
                if (typeof comp.cName !== 'undefined' && comp.cName === 'accordionGroup') {
                    comp.$children.forEach((childComp) => {
                        if (typeof childComp.cName !== 'undefined' && childComp.cName === 'accordion' && !childComp.show) {
                            collapse = false;
                        }
                    });
                }
            });

            this.$broadcast('Accordion::closeAllAccordion', collapse);
        },

        closeDropDowns(e) {
            this.$broadcast('Select::closeDropDown', e);
        },

        equalHeader(container, header, title, row) {
            jQuery(this.$el).find(container).each((i, el) => {
                jQuery(el).find(row).each((i, el) => {
                    var titleHeight = 0,
                        headerBlock = jQuery(el).find(header);

                    // Get max height
                    headerBlock.each((i, el) => {
                        var titleBlock = jQuery(el).find(title);

                        // Reset old values
                        titleBlock.css('height', '');

                        titleHeight = (titleBlock.height() > titleHeight ? titleBlock.height() : titleHeight);
                    });

                    headerBlock.each((i, el) => {
                        jQuery(el).find(title).height(titleHeight);
                    });
                });
            });
        },

        alignContents() {
            const blockContentClass = '.explore-block-content';
            const cardClass = '.homepage-card';
            const row = '.row';

            this.equalHeader(blockContentClass, cardClass, '.homepage-card_title', row);
            this.equalHeader(blockContentClass, cardClass, '.homepage-card_body', row);
            this.equalHeader(blockContentClass, cardClass, '.button-default', row);
        },

        tabSwitcher(tabButton, tabContent) {
            jQuery(tabButton).click((e) => {
                var tabId = jQuery(e.target).attr('data-tab');

                jQuery(tabButton).removeClass('current');
                jQuery(tabContent).removeClass('current');

                jQuery(e.target).addClass('current');
                jQuery(`#${tabId}`).addClass('current');

                this.alignContents();
            });
        },

        licenseValidationPopup() {
            var closerClasses = '.popup.license-validation .popup-container, .popup.license-validation .button, .popup.license-validation .popup-button-close';

            jQuery('#driving_license_type').on('change', (el) => {
                var value = jQuery(el.target).val();

                if (value === 'uk-provisional') {
                    jQuery('.popup.license-validation').css('display', 'block');
                }
            });

            jQuery(closerClasses).on('click', (el) => {
                var className = el.target.className;

                if (className === 'popup-container' || className.indexOf('button') > -1 || className.indexOf('popup-close') > -1) {
                    jQuery('.popup.license-validation').css('display', 'none');
                }
            });
        },

        loggedOutPopup(logoutRedirectUrl) {
            if (typeof logoutRedirectUrl === 'undefined') {
                this.logoutRedirectUrl = null;
            } else {
                this.logoutRedirectUrl = logoutRedirectUrl;
            }
            jQuery('.popup').css('display', 'none');
            jQuery('.popup.logged-out-overlay').css('display', 'block');
        },

        detectMobile() {
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                jQuery('body').addClass('mobile-device');
            } else {
                jQuery('body').addClass('desktop-device');
            }
        },

        isMobile() {
            return (/Android|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
        },

        isChrome() {
            return !!window.chrome && !this.isOpera();
        },

        isEdge() {
            return navigator.appVersion.indexOf('Edge') > -1;
        },

        isExplorer() {
            return typeof document !== 'undefined' && !!document.documentMode && !this.isEdge();
        },

        isFirefox() {
            return typeof window.InstallTrigger !== 'undefined';
        },

        isSafari() {
            return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        },

        isOpera() {
            return !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
        },

        loggedOutPopupClose() {
            var closerClasses = '.popup.logged-out-overlay .popup-container, .popup.logged-out-overlay .button, .popup.logged-out-overlay .popup-button-close';

            jQuery(closerClasses).on('click', (el) => {
                var className = el.target.className;

                if (className === 'popup-container' || className.indexOf('button') > -1 || className.indexOf('popup-close') > -1) {
                    if (!this.logoutRedirectUrl) {
                        location.reload();
                    } else {
                        window.location.href = this.logoutRedirectUrl;
                    }
                }
            });
        },

        resetCmsVideo(wrapper, button, content) {
            jQuery(wrapper).find(button).each((i, el) => {
                jQuery(el).click((e) => {
                    var videoWrapper = '';

                    if (jQuery(e.target).next(content).length === 0) {
                        // close if clicked on background
                        jQuery(e.target).find(content).each((i, el) => {
                            videoWrapper = jQuery(el);
                        });
                    } else {
                        // close if clicked on close button
                        videoWrapper = jQuery(e.target).next(content);
                    }

                    videoWrapper.html(videoWrapper.html());
                });
            });
        },

        collectFormData($form) {
            var formData = {};

            $form.find('input, select').each((i, el) => {
                var key = jQuery(el).attr('name');

                if (jQuery(el).is(':checkbox')) {
                    if (jQuery(el).is(':checked')) {
                        formData[key] = 1;
                    } else {
                        formData[key] = 0;
                    }
                } else {
                    formData[key] = jQuery(el).val();
                }
            });

            return formData;
        },

        toggleAccountMenu(e) {
            var $button = jQuery(e.currentTarget);
            if ($button.hasClass('active')) {
                $button.parent().find('ul').slideUp(250);
                $button.removeClass('active');
            } else {
                jQuery('.navbar-toggle').removeClass('active');
                jQuery('.navbar-toggle').parent().find('ul').hide();
                $button.parent().find('ul').slideDown(250);
                $button.addClass('active');
            }
        },

        /**
         * Animate scrolling to element specified by ID
         *
         * @param id
         */
        scrollToElement(id) {
            const element = jQuery(`#${id}`);
            let offsetTop = element.offset().top;

            if (window.innerWidth < this.RESPONSIVE_BREAKPOINTS.HORIZONTAL_BAR_MODE) {
                offsetTop -= jQuery('.navigation-bar-mobile .main-nav').outerHeight();
            }

            if (element) {
                jQuery([document.documentElement, document.body]).animate({
                    scrollTop: offsetTop
                }, 1000);
            }
        },

        /**
         * Applies fix for internet explorer to ensure 'sticky-footer' has corrent margin top
         */
        applyExplorerFooterFix() {
            if (!this.isExplorer()) {
                return;
            }

            jQuery().ready(() => {
                const footer = document.querySelector('footer');
                const windowHeight = window.innerHeight || document.clientHeight;

                if (!footer || !windowHeight) {
                    return;
                }

                const coords = footer.getBoundingClientRect();
                const offset = windowHeight - coords.top - coords.height;

                if (offset <= 0) {
                    return;
                }

                footer.style.marginTop = `${offset}px`;
            });
        }
    },

    events: {
        'Main::openCheckout'() {
            this.$broadcast('ChooseVehicle::showAccessories');
        },

        'Main::checkout'() {
            this.$broadcast('ChooseVehicle::checkout');
        },

        'Main::FullConfiguratorCheckout'() {
            this.$broadcast('FullConfigurator::checkout');
        },

        'Main::updateFinanceQuote'(data) {
            this.$broadcast('FinanceQuote::updateFinanceQuote', data);
        },

        'Main::updatePartExchangeProduct'(data) {
            this.$broadcast('FinanceQuote::updatePartExchangeProduct', data);
        },

        'Main::updateFinanceOverlay'() {
            this.$broadcast('FinanceOverlay::conditionChanged');
        },

        'Main::forceUpdateFinanceQuote'() {
            this.$broadcast('FinanceQuote::forceUpdateFinanceQuote');
        },

        'Main::progressUpdateFinanceQuote'() {
            this.$broadcast('FinanceQuote::progressUpdateFinanceQuote');
        },

        'Main::showFinanceOverlay'() {
            this.$broadcast('FinanceQuote::showFinanceOverlay');
        },

        'Main::paymentUpdated'() {
            this.$broadcast('CarouselColor::paymentUpdated');
        },

        'Main::hideAccessories'() {
            this.$broadcast('Configurator::hideAccessories');
        },

        'Main::hideSubsection'() {
            this.$broadcast('FinanceQuote::hideSubsection');
        },

        'Main::saveCar'() {
            this.$broadcast('ChooseVehicle::saveCar');
        },

        'Main::showActiveConfiguration'() {
            this.$broadcast('ChooseVehicle::showActive');
        },

        'Main::financeQuoteShowSubsection'() {
            this.$broadcast('FinanceQuote::showSubsection');
        },

        'Main::removeAccessory'(accessory) {
            this.$broadcast('Accessories::removeAccessory', accessory);
        },

        'Main::TimePickerGetAvailableTimes'() {
            this.$broadcast('TimePicker::getAvailableTimes');
        },

        'Main::financeCanCheckoutVehicle'(canCheckout) {
            this.$broadcast('Finance::canCheckoutVehicle', canCheckout);
        },

        'Main::PxValueUpdated'(pxValue) {
            this.$broadcast('FinanceQuote::PxValueUpdated', pxValue);
        },

        'Main::onFinanceCalculatorChange'(data) {
            this.$broadcast('FinanceQuote::onFinanceCalculatorChange', data);
        },

        'Main::closeFilter'() {
            this.$broadcast('CarFilter::closeFilter');
        },

        'Main::showPartExchangeNotification'() {
            this.$broadcast('ChooseVehicle::showPartExchangeNotification', true);
        },

        'Main::updateVehicles'() {
            this.$broadcast('chooseVehicle::updateVehicles');
        },

        'Main::scrollToElement'(id) {
            this.scrollToElement(id);
        },

        'Main::syncPXWithVuex'(data = []) {
            this.$broadcast('PartExchange::updateMileage', data);
            this.$broadcast('PartExchangeVrm::updatedVrm', data);
            this.$broadcast('PartExchangeVrm::updateRegistrationYear', data);
        },

        'Main::resetPX'(value) {
            this.$broadcast('TradeInBlock::resetPX', value);
        },

        'Main::configuratorAdvanceStep'() {
            this.$broadcast('Configurator::advanceStep');
        },

        'Main::updateCompareData'() {
            this.$broadcast('CarCompare::updateCompareData');
        },

        'Main::toggleInCompareListPOD'(sku) {
            this.$broadcast('Product::toggleInCompareList', sku);
        },

        'Main::carCompareVisible'(value) {
            this.$broadcast('ProductPageOverlay::carCompareVisible', value);
        },

        'Main::updateWishlistProp'(data) {
            this.$broadcast('Product::updateWishlistProp', data);
            this.$broadcast('CarComapreSaveCar::toggleIsInWishlist', data);
        },

        'Main::clearCompare'() {
            this.$broadcast('Product::clearCompare');
        }
    },

    ready() {
        this.initCalendar();
        this.initFormValidations();
        this.initNumberFormatting();
        this.initCurrencyFormatting();
        this.initDigitOnlyInput();
        this.initLetterOnlyInput();
        this.alignContents();
        this.tabSwitcher('ul.tabs li', '.tab-content');
        this.licenseValidationPopup();
        this.loggedOutPopupClose();
        this.resetCmsVideo('.hero-inner-video .popup-container', '.popup-button-close', '.content-wrapper');
        this.resetCmsVideo('.hero-inner-video .popup-container', '.bg-close', '.content-wrapper');

        jQuery(document).click((event) => {
            window.lastElementClicked = event.target;
        });

        this.$nextTick(() => {
            this.initSideImages();
        });

        window.addEventListener('resize', () => {
            this.handleResize();
            this.initSideImages();
        });

        jQuery('.navbar-toggle').on('click', (e) => {
            const $button = jQuery(e.currentTarget);

            if ($button.hasClass('active')) {
                $button.parent().find('.nav-bar-wrapper').slideUp(250);
                $button.removeClass('active');
                jQuery('.mobile-top-section').removeClass('active');
                jQuery('.outer-container').css('z-index', '');
                jQuery('.main-nav').removeClass('active');
            } else {
                jQuery('.account-toggle').removeClass('active');
                jQuery('.account-toggle').parent().find('ul').hide();
                $button.parent().find('.nav-bar-wrapper').slideDown(250);
                $button.addClass('active');
                jQuery('.mobile-top-section').addClass('active');
                jQuery('.outer-container').css('z-index', '-1');
                jQuery('.main-nav').addClass('active');
            }
        });

        jQuery('.toggle-account-mob').on('click', (e) => {
            const $button = jQuery(e.currentTarget);

            if (jQuery($button).hasClass('active')) {
                jQuery($button).removeClass('active');
                jQuery('.mob-account-info').hide();
            } else {
                jQuery($button).addClass('active');
                jQuery('.mob-account-info').show();
            }
        });

        jQuery('.navigation-bar-mobile .account-toggle').on('click', (e) => {
            this.toggleAccountMenu(e);
        });

        jQuery('.navigation-bar .account-toggle').on('click', (e) => {
            jQuery(this).toggleAccountMenu(e);
        });

        jQuery('.navigation-bar .account-toggle').hover((e) => {
            var $button = jQuery(e.currentTarget);
            $button.find('ul').stop().slideDown(250);
        }, (e) => {
            var $button = jQuery(e.currentTarget);
            $button.find('ul').stop().slideUp(250);
        });

        jQuery('.page-loader').hide();

        jQuery('#register-form #email_address').bind('keyup blur', (e) => {
            var el = jQuery(e.target);
            var fakeEl = jQuery('#email_address_fake');
            fakeEl.val(el.val());
        });

        setTimeout(() => {
            jQuery('.messages').slideUp(250);
        }, 10000);

        this.applyExplorerFooterFix();
        jQuery('html').removeClass('loading');
    },

    components: Components
});
