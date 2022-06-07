<template>
    <div class="trade-in-block-wrapper" v-show="isVisible">
        <app-part-exchange-block-content
            @remove="removePX"
            @open="openTradeInModalPxActive"
            :has-saved-px="hasSavedPx"
            :product-title="productTitle"
            :saved-px-value="savedPxValue"
            :est-value-disclaimer="estValueDisclaimer"
            v-ref:part-exchange-info-block
        ></app-part-exchange-block-content>
        <template v-if="!hasSavedPx">
            <p class="trade-in-get-started">
                {{ 'Thinking about trading in your vehicle for a MINI? You have made the right choice.' | translate }}
                <br>
                {{ 'Let\'s get started.' | translate }}
            </p>
            <button class="button dsp2-action" @click="openTradeInModal()">
                {{ 'Add My Trade-in' | translate }}
            </button>
        </template>
    </div>
</template>

<script>
    import appPartExchangeBlock from 'dsp2/components/PartExchange/PartExchangeBlock/PartExchangeBlock';

    export default appPartExchangeBlock.extend({});
</script>
