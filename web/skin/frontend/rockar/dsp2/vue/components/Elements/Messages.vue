<template>
    <div>
        <div class="messages" transition="slide" v-if="showMessage">
            <div :class="messageClass">
                {{ message | translate }}
            </div>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            message: {
                required: true,
                type: String
            },

            type: {
                required: false,
                type: String,
                default: 'warning'
            },

            show: {
                required: true,
                type: Boolean
            }
        },

        data() {
            return {
                showMessage: this.show,
                timeOut: null
            }
        },

        methods: {
            displayMessage() {
                if (this.show) {
                    clearTimeout(this.timeOut);
                    this.showMessage = true;
                    this.timeOut = setTimeout(() => {
                        this.showMessage = false;
                    }, 10000);
                }
            }
        },

        computed: {
            messageClass() {
                return `${this.type}-msg`;
            }
        },

        events: {
            'Message::show'() {
                this.displayMessage();
            }
        },

        created() {
            if (this.showMessage) {
                this.timeOut = setTimeout(() => {
                    this.showMessage = false;
                }, 10000);
            }
        }
    });
</script>