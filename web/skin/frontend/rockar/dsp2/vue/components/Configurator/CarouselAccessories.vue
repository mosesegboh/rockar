<template>
    <div class="accessories-section-wrapper">
        <div v-for="slide in slides" :class="slide.group_identifier">
            <div class="carousel-block">
                <div class="image-container">
                    <img :src="slide.image" :alt="slide.name" @click="showAccessories(slide)">
                    <div class="details">
                        <div class="title">
                            <p class="dsp2-label">{{ slide.name }}</p>
                        </div>
                        <div class="group-title">
                            <p>{{ slide.group_name }}</p>
                        </div>
                    </div>
                </div>

                <div class="content">
                    <div class="accessory-price">
                        {{ slide.price | numberFormat '0.00' true }}
                    </div>
                    <div class="accessory-add-item"
                         v-bind:class="{ 'added': !slide.status }"
                         :disabled="slide.preSelected"
                         @click="manipulateOnItem(slide)"
                    >
                        {{ slide.status ? 'Remove' : 'Add' | translate }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default Vue.extend({
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
        },

        generalClass: {
            required: false,
            type: String,
            default: ''
        },

        checkboxes: {
            required: false,
            type: Boolean,
            default: false
        },

        selected: {
            required: false,
            type: String,
            default: ''
        },

        currentId: {
            required: false,
            type: String,
            default: ''
        }
    },

    data() {
        return {
            buttonText: '',
            defaultOptions: {
                slidesToShow: 4,
                slidesToScroll: 4,
                prevArrow: '<span class="slick-prev">Previous</span>',
                nextArrow: '<span class="slick-next">Next</span>',
                responsive: [
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 3
                        }
                    }
                ],
                navAll: {
                    id: 'all',
                }
            }
        }
    },

    methods: {
        showAccessories(item) {
            this.$emit('show-accessories', item);
        },

        manipulateOnItem(item) {
            this.$emit('manipulate-on-item', item);
        },

        selectGroup(group) {
            if (group === 'All') {
                jQuery(this.$el).slick('slickUnfilter');
            } else {
                jQuery(this.$el).slick('slickUnfilter').slick('slickFilter', `.${group}`);
            }
        }
    },

    computed: {
        finalOptions() {
            const overrides = {};
            const result = jQuery.extend(this.defaultOptions, this.options);

            return jQuery.extend(result, overrides);
        }
    },

    ready() {
        jQuery(this.$el).on('init', () => {
            jQuery(window).trigger('resize');
        });
        jQuery(this.$el).slick(this.finalOptions);
    },

    created() {
        this.$parent.$on('changeGroup', this.selectGroup);
    }
});
</script>
