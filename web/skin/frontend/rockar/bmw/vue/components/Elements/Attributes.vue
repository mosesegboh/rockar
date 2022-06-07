<template>
    <div class="attributes-container">
        <div class="attributes">
            <div class="information-attribute" v-for="attribute in attributesToDisplay">
                <img class="attribute-icon" :src="attribute.icon"
                     v-if="['1','2'].includes(attribute.display) && !!attribute.icon">
                <span v-if="['2','3'].includes(attribute.display)" class="title">{{ attribute.value }}</span>
            </div>
        </div>
    </div>
</template>

<script>
export default Vue.extend({
    data() {
        return {
            carAttributesToShow: [
                'vehicle_condition',
                'fuel_type',
                'transmission',
                'km'
            ]
        }
    },

    props: [
        'carAttributes'
    ],

    computed: {
        attributesToDisplay() {
            const attributes = [];

            this.carAttributesToShow.forEach(attrCode => {
                if (this.carAttributes[attrCode]) {
                    attributes.push(this.carAttributes[attrCode]);
                }
            });

            return attributes;
        },
    }
});
</script>