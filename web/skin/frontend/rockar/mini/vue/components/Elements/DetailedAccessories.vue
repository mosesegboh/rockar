<template>
    <div class="accessories-detailed-wrapper">
        <div class="accessories-detailed">
            <div class="image-container">
                <img :src="detailedAccessory.image" :alt="detailedAccessory.name">
            </div>
        </div>
        <div class="accessories-detailed">
            <h3 class="accessory-price">
                {{ detailedAccessory.price | numberFormat '0.00' true }}
            </h3>
            <div class="accessory-title">
                <h1 v-html="detailedAccessory.name"></h1>
            </div>
            <div class="accessory-details">
                <p v-html="detailedAccessory.extendedDescription"></p>
            </div>
            <div class="accessories-modal-ctas">
                <button type="button" name="button" @click="close()" class="button button-narrow button-empty-light popup-close">
                    {{ 'Cancel' | translate }}
                </button>
                <button type="button" name="button"
                    class="button button-blue-lagoon"
                    :disabled="detailedAccessory.preSelected || detailedAccessory.status !== 0"
                    @click="addDetailedAccessory(detailedAccessory); close();"
                >
                    {{ 'Select' | translate }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            detailedAccessory: {
                required: false,
                type: Object
            }
        },

        methods: {
            close() {
                this.$emit('close');
            },

            addDetailedAccessory(detailedAccessory) {
                this.$emit('add');
            }
        }
    })
</script>