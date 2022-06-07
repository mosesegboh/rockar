import EventTracker from 'dsp2/mixins/EventTracker';

export default {
    mixins: [EventTracker],

    methods: {
        setFinanceGroup(financeGroupId) {
            switch (parseInt(financeGroupId)) {
                case 3:
                    this.fireEventForTracking(
                        this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                        this.getEventConstants().EVENTRACKERVALUES.FININSTALMENT
                    );
                    break;

                case 1:
                    this.fireEventForTracking(
                        this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                        this.getEventConstants().EVENTRACKERVALUES.FINSELECT
                    );
                    break;

                case 13:
                    this.fireEventForTracking(
                        this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                        this.getEventConstants().EVENTRACKERVALUES.FINCASH
                    );
                    break;

                case 10:
                    this.fireEventForTracking(
                        this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                        this.getEventConstants().EVENTRACKERVALUES.FINOTHER
                    );
                    break;

                default:
                    return false;
            }
        },

        fireFinEventCheckout() {
            this.fireEventForTracking(
                this.getEventConstants().PAGEDESCRIPTION.VIEWS,
                this.getEventConstants().EVENTRACKERVALUES.FINCHECKOUT
            );
        }
    }
}
