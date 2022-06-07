<template>
    <div v-if="experiencePopupData.show_popup" :class="['popup', className ? className : '', show ? '' : 'popup-hide']" @click="closePopup()">
        <div class="popup-overlay"></div>
        <div class="popup-container">
            <div class="bg-close">
                <div class="popup-content" :style="{ width: width ? width : '' }" @click.stop>
                    <img :src="experiencePopupData.image" :alt="experiencePopupData.label" />
                    <div class="content-wrapper">
                        <div class="experience">
                            <h3>{{ experiencePopupData.label }}</h3>
                            <p>{{ experiencePopupData.textblock }}</p>
                            <p class="link">
                                <a :href="experiencePopupData.link_url" target="_blank">{{ experiencePopupData.link_label }}</a>
                            </p>

                            <div class="row actions">
                                <button type="button" name="button" @click="closePopup" class="button button-black right popup-close">{{ 'Close' | translate }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default Vue.extend({
    props: {
        width: {
            required: false,
            type: String
        },
        className: {
            required: false,
            type: String,
            default: null
        },
        experiencePopupData: {
            required: true,
            type: Object
        }
    },

    data() {
        return {
            preloader: '<span class="popup-loader"></span>',
            show: false
        }
    },

    methods: {
        closePopup() {
            this.show = false;
            jQuery('body').removeClass('modal-open');
            this.$dispatch('close-popup');

            this.$http({
                url: this.experiencePopupData.save_url,
                method: 'POST',
                emulateJSON: true,
                data: {
                    product_id: this.experiencePopupData.product_id,
                    experience_id: this.experiencePopupData.experience_id
                }
            });
        },
    },

    watch: {
        'show'(isOpening) {
            if (isOpening) {
                jQuery('body').addClass('modal-open');
            } else {
                jQuery('body').removeClass('modal-open');
            }
        }
    },

    ready() {
        if (this.experiencePopupData.experience_id && this.experiencePopupData.show_popup) {
            setTimeout(() => {
                this.show = true;
                jQuery('body').addClass('modal-open');
            }, this.experiencePopupData.popup_delay * 1000);
        }

        // Prevent popup auto-close when clicking inside it's body
        jQuery('.popup-content').on('click', (e) => {
            e.stopPropagation();
        });
    },

    destroyed() {
        jQuery('body').removeClass('modal-open');
    }
});
</script>
