<template>
    <div id="page-header" v-show="visible">
        <div class="header-desktop">
            <div class="previous" @click="previous()" v-show="page !== ''">
                <span>{{ 'Previous' | translate }}</span>
            </div>
            <div class="title">
                <span>{{ isCheckout ? checkoutTitle : title | translate }}</span>
            </div>
        </div>
        <div class="header-mobile">
            <span class="previous" @click="previous()"></span>
            {{ title | translate }}
        </div>
    </div>
</template>

<script>
import appPageHeader from 'dsp2/components/PageHeader';

export default appPageHeader.extend({
    computed: {
        isCheckout() {
            return this.$root.$refs.checkout;
        },

        checkoutTitle() {
            return this.$root.$refs.checkoutAccordionNav.stepTitles[this.AccordionGroup.openStep];
        }
    }
})
</script>
