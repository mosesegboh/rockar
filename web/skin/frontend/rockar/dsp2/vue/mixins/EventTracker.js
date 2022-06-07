export default {
    methods: {
        getEventConstants() {
            const EVENTRACKERVALUES = {
                LANDINGPAGE: 'dsp landing page',
                RESULTSPAGE: 'search stock',
                FININSTALMENT: 'finance overlay > instalment sale',
                FINSELECT: 'finance overlay > select finance',
                FINCASH: 'finance overlay > cash',
                FINOTHER: 'finance overlay > other finance',
                PDPDETAILS: 'vehicle details > ',
                COMPAREEXPAND: 'compare list > ',
                PXDETAILS: 'trade in overlay > vehicle details',
                PXCONFIRM: 'trade in overlay > confirm vehicle details',
                PXCUSTOM: 'trade in overlay > manual vehicle details',
                PXCONDITION: 'trade in overlay > vehicle condition',
                PXVALUATION: 'trade in overlay > trade in valuation',
                CHECKOUTDETAILS: 'checkout > personal details',
                CHECKOUTDELIVERY: 'checkout > collection delivery',
                PXCHECKOUT: 'checkout > trade in',
                FINCHECKOUT: 'checkout > payment option',
                CHECKOUTAPPLICATION: 'checkout > apply for finance',
                CHECKOUTSUMMARY: 'checkout > summary',
                CHECKOUTSUCCESS: 'checkout > success',
                MYACCOUNT: 'my account',
                TDVEHICLE: 'test drive > choose vehicle',
                TDCONFIRMATION: 'test drive > appointment confirmed',
                TDREQUEST: 'test drive > request confirmed'
            }

            const TRIGGERTRACKERVALUES = {
                DSPLOGIN: 'dsp login true',
                SELECTEDMODELS: 'dsp landing page > ',
                ADDTOCOMPARE: 'add vehicle to compare button > ',
                QUOTEOPEN: 'email quote start > ',
                QUOTESUCCESS: 'email quote finished > ',
                WISHLIST: 'wishlist > '
            }

            const PAGEDESCRIPTION = {
                VIEWS: 'pageName',
                TRIGGERS: 'triggerName'
            }

            const TRIGGERNAMES = {
                VIEW: 'pageView',
                EVENT: 'eventTrigger'
            }

            return {
                EVENTRACKERVALUES,
                PAGEDESCRIPTION,
                TRIGGERTRACKERVALUES,
                TRIGGERNAMES
            };
        },

        fireEventForTracking(eventName, message) {
            let eventDetails = {},
                triggerName;

            if (eventName === this.getEventConstants().PAGEDESCRIPTION.VIEWS) {
                triggerName = this.getEventConstants().TRIGGERNAMES.VIEW;
                eventDetails = {
                    pageName: message
                }
            } else {
                triggerName = this.getEventConstants().TRIGGERNAMES.EVENT;
                eventDetails = {
                    triggerName: message
                }
            }

            const event = new CustomEvent(triggerName, {
                detail: eventDetails
            });

            window.dispatchEvent(
                event
            );
        }
    }
}
