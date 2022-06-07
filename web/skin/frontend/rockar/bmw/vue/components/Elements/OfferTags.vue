<template v-if="offerTagData">
    <div class="offertags-container"
         :class="{'offertags-background': brandColor === '1', 'offertags-blank': action === 'text' && brandColor === '0', 'offertags-it': action === 'icon_text', 'display-none': offerTagData.length === 0}">
        <div class="offertags">
            <img class="offertag-icon" v-if="icon"
                 :src="icon"
                 :alt="name | translate">
            <span class="offertag-label" v-if="label && action !== 'icon'">{{ label | translate }}</span>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: [
            'offerTagData'
        ],

        data() {
            return {
                action: null,
                brandColor: null,
                icon: null,
                label: null,
                name: null,
                sortOrder: null
            }
        },

        methods: {
            setInitialOfferTagData() {
                const offerTagData = this.offerTagData;

                this.action = offerTagData.action_type || '';
                this.brandColor = offerTagData.brand_bg_color || '';
                this.icon = offerTagData.icon || '';
                this.label = offerTagData.label || '';
                this.name = offerTagData.name || '';
                this.sortOrder = offerTagData.sort_order || '';
            }
        },

        created() {
            this.setInitialOfferTagData();
        }
    })
</script>
