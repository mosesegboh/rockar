<template>
    <app-cosy-views-carousel
        :slides="slides"
        :options="options"
        :show.sync="show"
        :index.sync="index"
    ></app-cosy-views-carousel>
    <app-modal :show="show"
        @close-popup="show = false"
    >
        <div slot="content">
            <app-configurator-carousel-images
                :slides="slides"
                v-ref:modal-cosy-view-carousel
            ></app-configurator-carousel-images>
        </div>
    </app-modal>
</template>

<script>
    import appCosyViewsCarousel from 'dsp2/components/Configurator/CosyViewsCarousel';
    import appConfiguratorCarouselImages from 'core/components/Configurator/CarouselImages';
    import appModal from 'core/components/Elements/Modal';

    export default Vue.extend({
        components: {
            appCosyViewsCarousel,
            appModal,
            appConfiguratorCarouselImages
        },

        props: {
            slides: {
                required: true,
                type: Array
            },

            options: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            }
        },

        watch: {
            'show'(newValue) {
                if (newValue
                    && this.$refs.modalCosyViewCarousel
                    && this.$refs.modalCosyViewCarousel.$el
                ) {
                    jQuery(this.$refs.modalCosyViewCarousel.$el).slick('unslick');
                    jQuery(this.$refs.modalCosyViewCarousel.$el).slick(this.modalCarouselSettings);
                    jQuery(this.$refs.modalCosyViewCarousel.$el).slick('slickGoTo', this.index, true);
                }
            }
        },

        data() {
            return {
                show: false,
                index: 0,
                modalCarouselSettings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    initialSlide: 0,
                    mobileNavigation: 1,
                    dots: false,
                    lazyload: 'ondemand',
                    infinite: false,
                    mobileFirst: true,
                    centerMode: false,
                    speed: 300
                }
            }
        }
    });
</script>
