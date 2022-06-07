<template>
    <div
        v-show="show"
        class="cloudimage-360"
        data-spin-reverse="true"
        data-hide-360-logo="true"
        :data-amount="images.length"
        :data-image-list="imageList">
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            threeSixtyScript: {
                required: true,
                type: String
            },

            images: {
                required: true,
                type: Array
            },

            show: {
                required: true,
                type: Boolean
            }
        },

        watch: {
            'show'(value) {
                if (value) {
                    this.update360();
                }
            }
        },

        created() {
            this.setThreeSixtyScript();
        },

        ready() {
            window.addEventListener('resize', this.handleResize);
        },

        beforeDestroy() {
            window.removeEventListener('resize', this.handleResize);
        },

        methods: {
            setThreeSixtyScript() {
                const script = document.createElement('script');
                script.setAttribute('src', this.threeSixtyScript);
                script.async = true;
                document.head.appendChild(script);
            },

            handleResize() {
                this.update360();
            },

            update360() {
                if (typeof window.CI360 !== 'undefined' && typeof window.CI360._viewers !== 'undefined') {
                    // Without a timeout canvas is not resized on every window resize event
                    setTimeout(() => {
                        window.CI360._viewers.forEach((e) => {
                            e.update();
                        });
                    }, 5);
                }
            }
        },

        computed: {
            imageList() {
                const images = [];

                this.images.forEach((image) => {
                    images.push(`"${image.image}"`);
                });

                return `[${images.join()}]`;
            }
        }
    });
</script>
