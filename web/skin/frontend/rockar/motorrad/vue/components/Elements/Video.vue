<template>
    <div class="video-section" v-if="show">
        <div class="backdrop"></div>
        <div class="video-overlay">
            <div class="close" @click="$emit('close')">X</div>
            <iframe width="500" height="300" :src="videoURL" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            show: {
                required: true,
                type: Boolean
            },

            id: {
                required: false,
                type: String,
                default: false
            },

            url: {
                required: false,
                tpye: String,
                default: false
            }
        },

        computed: {
            videoURL() {
                if (this.url) {
                    return this.url;
                }

                return `https://www.youtube.com/embed/${this.id}`;
            }
        },

        watch: {
            show: {
                handler(newV) {
                    var body = jQuery('body');
                    if (newV) {
                        body.addClass('modal-open');
                    } else {
                        body.removeClass('modal-open');
                    }
                }
            }
        }
    });
</script>