<template>
    <div class="pod-specs-container">
        <div class="pod-specs">
            <template v-for="(index, attribute) in attributesToDisplay">
                <div class="pod-spec-item">
                    <div class="pod-spec-item-data">
                        <img class="pod-spec-item-icon" :src="attribute.icon" alt=""
                             v-if="['1','2'].includes(attribute.display) && !!attribute.icon">
                        <span v-if="['2','3'].includes(attribute.display)" class="pod-spec-item-value" v-html="attribute.value"></span>
                        <span class="pod-spec-item-label">{{ attribute.label }}</span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
export default Vue.extend({
    data() {
        return {
            carAttributesToShow: {
                combustion: [
                    'n_0_to_100',
                    'engine_power',
                    'ec_combined_mpg'
                ],
                electric: [
                    'n_0_to_100',
                    'engine_power'
                ],
                hybrid: [
                    'n_0_to_100',
                    'engine_power'
                ]
            }
        }
    },

    props: {
        carAttributes: {
            required: true,
            type: Object
        },
        fuelType: {
            required: true,
            type: String
        }
    },

    computed: {
        attributesToDisplay() {
            const attributes = [];

            const fuelType = ['electric', 'hybrid'].includes(this.fuelType.toLowerCase())
                ? this.fuelType.toLowerCase()
                : 'combustion';

            this.carAttributesToShow[fuelType].forEach(attrCode => {
                if (this.carAttributes[attrCode]) {
                    attributes.push(this.carAttributes[attrCode]);
                }
            });

            return attributes;
        },
    },
});
</script>
