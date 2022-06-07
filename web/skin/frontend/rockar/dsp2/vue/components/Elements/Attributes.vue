<template>
    <div class="attributes-container">
        <div class="attributes">
            <div class="information-attribute" v-for="attribute in attributesToDisplay">
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
                'transmission'
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