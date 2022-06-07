<script>
    import coreNotification from 'core/components/Notification';
    import EventTracker from 'dsp2/mixins/EventTracker';

    export default coreNotification.extend({
        mixins: [
            EventTracker
        ],

        props: {
            isLoggedIn: {
                required: false,
                type: String,
                default: false
            }
        },

        ready() {
            this.showGlobalNotifications();
            this.loggedInEvent();
        },

        methods: {
            loggedInEvent() {
                /**
                 * Fire event if user is logged in
                 */
                if (this.isLoggedIn !== '1') {
                    if (localStorage.getItem('LoggedInEventTriggered')) {
                        localStorage.removeItem('LoggedInEventTriggered');
                    }
                } else {
                    if (!localStorage.getItem('LoggedInEventTriggered')) {
                        this.fireEventForTracking(
                            this.getEventConstants().PAGEDESCRIPTION.TRIGGERS,
                            this.getEventConstants().TRIGGERTRACKERVALUES.DSPLOGIN
                        );

                        // Save that event was tiggered, to avoid tiggering event on
                        // every refresh and after browser windows was clossed.
                        localStorage.setItem('LoggedInEventTriggered', 'true');
                    }
                }
            }
        }
    });
</script>
