import Vuex from 'vuex';
import VueResource from 'vue-resource';
import VueValidator from 'vue-validator';
import Store from 'core/store';
import 'babel-polyfill';

Vue.config.optionMergeStrategies.events = Vue.config.optionMergeStrategies.methods;

// import vendors
import numeral from 'numeral';

// change locale to uk.
numeral.register('locale', 'en-gb', {
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
    ordinal: (number) => number,
    currency: {
        symbol: 'R'
    }
});
numeral.locale('en-gb');

// utils
import initSideImages from 'core/utils/SideImage';
import appFormValidation from 'core/utils/FormValidation';

// import components
import CoreComponents from 'core/js/core';
import AdditionalComponents from 'core/js/components';
const Components = Object.assign(CoreComponents, AdditionalComponents);

// Transition import and declaration
import transitions from 'core/transitions';

transitions.forEach((transition) => {
    Vue.transition(transition.name, transition.content);
});

// Filters import and declaration
import filters from 'core/filters';

filters.forEach((filter) => {
    Vue.filter(filter.name, filter.content);
});

/* eslint-disable no-new */

Vue.use(Vuex);
Vue.use(VueResource);
Vue.use(VueValidator);

// Validations import and declaration
import validations from 'core/utils/VueValidation';

validations.forEach((validation) => {
    Vue.validator(validation.name, validation.content);
});

Vue.config.debug = true;

const store = new Vuex.Store(Store);

window.EventsBus = new Vue();

window.app = new Vue({
    el: 'body',
    store,

    data: {
        currencySymbol,
        toggleMainNavigation: false,
        emailFieldValue: null,
        resizeTime: null,
        logoutRedirectUrl: null
    },

    computed: {
        currentNavigationButtonVisibility() {
            return jQuery('.navigation-menu-wrapper').is(':visible');
        },

        currentDevice() {
            return this.$store.state.general.device;
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

        detectResponsive() {
            const windowW = jQuery(window).width();

            if (windowW >= 1440 && this.currentDevice !== 'ldesktop') {
                this.$store.commit('setResponsiveDevice', 'ldesktop');
            } else if (windowW < 1440 && windowW >= 1025 && this.currentDevice !== 'desktop') {
                this.$store.commit('setResponsiveDevice', 'desktop');
            } else if (windowW < 1025 && windowW >= 737 && this.currentDevice !== 'tablet') {
                this.$store.commit('setResponsiveDevice', 'tablet');
            } else if (windowW < 737 && this.currentDevice !== 'mobile') {
                this.$store.commit('setResponsiveDevice', 'mobile');
            }
        },

        initSideImages,

        initFooter() {
            jQuery('.footer-menu').on('click', 'h4', (e) => {
                var winWidth = jQuery(window).width();
                if (winWidth < 737) {
                    var submenu = jQuery(e.target).next('.footer-submenu');
                    submenu.slideToggle();
                }
            });
        },

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

        initNumberFormatting() {
            // fail safe if reinitialised
            var inputs = jQuery('input.number:not(.number-validation-active)');

            inputs.on('focus', (e) => {
                jQuery(e).val(0);
            });

            inputs.on('keydown', (e) => {
                var fieldVal = numeral(jQuery(e.target).val()).value();
                var fieldMaxLength = jQuery(e.target).data('length');
                var fieldLength = fieldVal.toString().length;
                if (fieldLength < parseInt(fieldMaxLength) && (e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) {
                    e.target.selectionStart = e.target.selectionEnd = e.target.value.length;
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
            jQuery(tabButton).click(() => {
                var tabId = jQuery(this).attr('data-tab');

                jQuery(tabButton).removeClass('current');
                jQuery(tabContent).removeClass('current');

                jQuery(this).addClass('current');
                jQuery(`#${tabId}`).addClass('current');

                this.alignContents();
            });
        },

        licenseValidationPopup() {
            var closerClasses = '.popup.license-validation .popup-container, .popup.license-validation .button, .popup.license-validation .popup-button-close';

            jQuery('#driving_license_type').on('change', (e) => {
                var value = jQuery(e.target).val();

                if (value === 'uk-provisional') {
                    jQuery('.popup.license-validation').css('display', 'block');
                }
            });

            jQuery(closerClasses).on('click', (e) => {
                var className = e.target.className;

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
            return (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
        },

        loggedOutPopupClose() {
            var closerClasses = '.popup.logged-out-overlay .popup-container, .popup.logged-out-overlay .button, .popup.logged-out-overlay .popup-button-close';

            jQuery(closerClasses).on('click', (e) => {
                var className = e.target.className;

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
                jQuery(el).on('click', (e) => {
                    var videoWrapper = '';
                    if (jQuery(e.target).next(content).length === 0) {
                        // close if clicked on background
                        jQuery(e.target).find(content).each((index, el) => {
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
        }
    },

    events: {
        'Main::configuratorAdvanceStep'() {
            this.$broadcast('Configurator::advanceStep');
        },

        'Main::checkout'(toggle) {
            this.$broadcast('FinanceQuote::checkout', toggle);
        },

        'Main::updateFinanceQuote'(data) {
            this.$broadcast('FinanceQuote::updateFinanceQuote', data);
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

        'Main::paymentUpdated'() {
            this.$broadcast('CarouselColor::paymentUpdated');
            this.$broadcast('FinanceQuote::updateFinanceType');
            this.$broadcast('FinanceQuote::updateMSPData');
        },

        'Main::configuratorStepBack'() {
            this.$broadcast('Configurator::stepBack');
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

        'Main::removeAccessory'(accessory) {
            this.$broadcast('Accessories::removeAccessory', accessory);
        },

        'Main::TimePickerGetAvailableTimes'() {
            this.$broadcast('TimePicker::getAvailableTimes');
        }
    },

    created() {
        this.$store.commit('setCurrencySymbol', window.currencySymbol);
    },

    ready() {
        this.initCalendar();
        this.initFormValidations();
        this.initNumberFormatting();
        this.initFooter();
        this.alignContents();
        this.tabSwitcher('ul.tabs li', '.tab-content');
        this.licenseValidationPopup();
        this.loggedOutPopupClose();
        this.detectResponsive();
        this.resetCmsVideo('.hero-inner-video .popup-container', '.popup-button-close', '.content-wrapper');
        this.resetCmsVideo('.hero-inner-video .popup-container', '.bg-close', '.content-wrapper');

        this.$nextTick(() => {
            this.initSideImages();
        });

        window.addEventListener('resize', () => {
            this.handleResize();
            this.initSideImages();
            this.detectResponsive();
        });

        jQuery('.navbar-toggle').on('click', (e) => {
            var $button = jQuery(e.currentTarget);

            if ($button.hasClass('active')) {
                $button.parent().find('ul').slideUp(250);
                $button.removeClass('active');
            } else {
                jQuery('.account-toggle').removeClass('active');
                jQuery('.account-toggle').parent().find('ul').hide();
                $button.parent().find('ul').slideDown(250);
                $button.addClass('active');
            }
        });

        jQuery('.navigation-bar-mobile .account-toggle').on('click', (e) => {
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
        });

        jQuery('.navigation-bar .account-toggle').hover((e) => {
            var $button = jQuery(e.target);
            $button.find('ul').stop().slideDown(250);
        }, (e) => {
            var $button = jQuery(e.target);
            $button.find('ul').stop().slideUp(250);
        });

        jQuery('.page-loader').hide();
        jQuery(window).bind('beforeunload', (e) => {
            if (e.target.activeElement.className !== 'attachment-download') {
                jQuery('.page-loader').show();
            }
        });

        jQuery('#register-form #email_address').bind('keyup blur', (e) => {
            var el = jQuery(e.target);
            var fakeEl = jQuery('#email_address_fake');
            fakeEl.val(el.val());
        });

        setTimeout(() => {
            jQuery('.messages').slideUp(250);
        }, 10000);

        jQuery('#documents-upload-form').on('submit', (e) => {
            const form = jQuery(e.target);
            const submitButton = form.find('[type="submit"]');

            if (form.valid()) {
                submitButton.addClass('disabled').prop('disabled', 'disabled');
            } else {
                submitButton.removeClass('disabled').removeProp('disabled');
            }
        });

        jQuery('html').removeClass('loading');
    },

    components: Components
});
