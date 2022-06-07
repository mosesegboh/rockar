<template>
    <div class="accessories-detailed-wrapper">
        <div class="accessories-detailed">
            <div class="image-container">
                <img :src="detailedAccessory.image" :alt="detailedAccessory.name">
            </div>
        </div>
        <div class="accessories-detailed content">
            <div class="accessory-title">
                <h1 v-html="detailedAccessory.name"></h1>
                <div class="accessory-details" v-if="detailedAccessory.extendedDescription">
                    <p v-html="detailedAccessory.extendedDescription"></p>
                </div>
            </div>

            <div class="accessory-price">
                <span>{{ 'Price' | translate }}</span>
                <div class="price"> {{ detailedAccessory.price | numberFormat '0.00' true }} </div>
            </div>
            <div class="accessories-modal-ctas">
                <button type="button" name="button" @click="close()" class="button dsp2-outline">
                    {{ 'Cancel' | translate }}
                </button>
                <button type="button" name="button"
                    class="button button-blue-lagoon"
                    :disabled="detailedAccessory.preSelected || detailedAccessory.status !== 0"
                    @click="addDetailedAccessoryAndClose(detailedAccessory);"
                >
                    {{ 'Add' | translate }}
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

            addDetailedAccessoryAndClose(detailedAccessory) {
                this.$emit('add-and-close', detailedAccessory);
            }
        }
    })
</script>
