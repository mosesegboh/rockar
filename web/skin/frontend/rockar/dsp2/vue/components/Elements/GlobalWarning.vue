<template>
    <div v-if="show">
        <div class="global-warning">
            <div :class="{'error-msg': type === this.messageType.error, 'success-msg': type === this.messageType.success}">
                <a class="close-icon-warning" @click="close"></a>
                <span :class="{'icon-info-red': type === this.messageType.error, 'icon-info-green': type === this.messageType.success}"></span>
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
            messageType: {
                error: 'error',
                success: 'success'
            }
        };
    },

    methods: {
        close() {
            this.show = false;
            this.$dispatch('GlobalWarning::close');
        }
    }
});
</script>
