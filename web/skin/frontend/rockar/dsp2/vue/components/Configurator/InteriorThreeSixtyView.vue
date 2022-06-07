<template>
    <div class="interior-three-sixty">
        <div
            id="interior-360-panorama"
            class="carousel-image-wrapper"
            :style="customStyle"
        ></div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            pannellumScript: {
                required: true,
                type: String
            },

            images: {
                required: true,
                type: Array
            }
        },

        data() {
            return {
                customStyle: {},
                pannellum: false
            };
        },

        computed: {
            image() {
                return this.images[0].image;
            }
        },

        methods: {
            setPannellumScript() {
                if (!this.pannellum) {
                    const script = document.createElement('script');
                    script.setAttribute('src', this.pannellumScript);
                    script.async = true;
                    script.onload = this.init360;
                    document.head.appendChild(script);
                } else {
                    this.init360();
                }
            },

            handleResize() {
                if (this.pannellum) {
                    if (window.innerWidth >= 1024) {
                        const view = jQuery(this.$el).closest('.category-products-list');
                        this.customStyle = {
                            height: `${view.height()}px`,
                            width: `${view.width()}px`
                        };
                    } else {
                        this.customStyle = {};
                    }

                    this.pannellum.resize();
                }
            },

            init360() {
                if (typeof window.pannellum !== 'undefined') {
                    this.pannellum = window.pannellum.viewer('interior-360-panorama', {
                        autoLoad: true,
                        compass: false,
                        keyboardZoom: false,
                        mouseZoom: false,
                        panorama: this.image,
                        showFullscreenCtrl: false,
                        showZoomCtrl: false,
                        type: 'equirectangular'
                    });

                    this.handleResize();
                }
            }
        },

        created() {
            this.setPannellumScript();
        },

        ready() {
            window.addEventListener('resize', this.handleResize);
        },

        beforeDestroy() {
            window.removeEventListener('resize', this.handleResize);

            if (this.pannellum) {
                this.pannellum.destroy();
            }
        }
    });
</script>
