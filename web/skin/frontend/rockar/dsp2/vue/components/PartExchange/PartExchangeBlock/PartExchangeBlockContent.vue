<template>
    <div v-if="hasSavedPx" class="px-saved">
        <div class="general-preloader" v-show="ajaxLoading">
            <div class="show-loading"></div>
        </div>
        <div class="px-saved-title">
            <p class="h7">{{ 'Trade-in Vehicle' | translate }}</p>
            <div class="px-icons">
                <app-confirmation-modal
                    :title="'Remove Trade-in' | translate"
                    :confirmation-question="'Do you really want to remove your trade-in?' | translate"
                    :affirmative="'Confirm' | translate"
                    :negative="'Cancel' | translate"
                    :wait-for-responce="true"
                    :width="'860'"
                    custom-class="trade-in-remove"
                    v-ref:part-exchange-confirmation-modal
                >
                    <span class="icon-delete" @click="removePX"></span>
                </app-confirmation-modal>
                <span class="icon-edit" @click="openTradeInModalPxActive(isExpired)"></span>
            </div>
        </div>
        <p class="dsp2-body-s">{{ productTitle }}</p>

        <template v-if="MyAccount">
            <div class="px-saved-estimated my-account">
                <p class="dsp2-caption">{{ 'Registration No' | translate }}</p>
                <p class="px-value">{{ vrm }}</p>
            </div>
            <div class="px-saved-estimated my-account">
                <p class="dsp2-caption">{{ 'Mileage' | translate }}</p>
                <p class="px-value">{{ mileage | numberKilometerFormat }}</p>
            </div>
            <div class="px-saved-estimated my-account">
                <p class="dsp2-caption">{{ 'Outstanding Finance' | translate }}</p>
                <p class="px-value">{{ outstandingFinance | numberCurrencyFormat }}</p>
            </div>
        </template>

        <hr>
        <div class="px-saved-estimated">
            <div class="dsp2-caption">
                <span>{{ 'Estimated Value' | translate }}</span>
                <app-tooltip tooltip-position="bottom">
                    <p class="icon-info tooltip-valuation" slot="tooltipElement"></p>
                    <div class="valuation-side-block" slot="tooltipContent" v-html="estValueDisclaimer"></div>
                </app-tooltip>
            </div>
            <p class="px-value">{{ savedPxValue | numberCurrencyFormat }}</p>
        </div>
        <template v-if="!isExpired && expireDate">
            <p class="expired">{{ '*Expires' | translate }} {{ expireDate }}</p>
        </template>
        <template v-else>
            <p class="red-2 expired">{{ 'Expired' | translate }}</p>
        </template>
        <hr>
    </div>
</template>

<script>
    import appConfirmationModal from 'dsp2/components/Elements/ConfirmationModal';
    import appTooltip from 'dsp2/components/Elements/Tooltip';
    import translateString from 'core/filters/Translate';

    export default Vue.extend({
        data: () => ({
            ajaxLoading: false
        }),

        components: {
            appConfirmationModal,
            appTooltip
        },

        props: {
            hasSavedPx: {
                required: true,
                type: Boolean
            },

            estValueDisclaimer: {
                required: true,
                type: String
            },

            productTitle: {
                required: false,
                type: String,
                default: ''
            },

            savedPxValue: {
                required: false,
                type: String,
                default: ''
            }
        },

        computed: {
            MyAccount() {
                return this.$root.$refs.myDetails;
            },

            isExpired() {
                return this.MyAccount
                    ? this.$parent.isExpired
                    : this.PartExchangeFilter.isExpired;
            },

            mileage() {
                return this.$root.$refs.partExchangeBlock.savedPx.mileage;
            },

            vrm() {
                return this.$root.$refs.partExchangeBlock.savedPx.vrm;
            },

            outstandingFinance() {
                return this.$root.$refs.partExchangeBlock.savedPx.outstanding_finance;
            },

            expireDate() {
                return !this.MyAccount
                    ? this.$root.$refs.financeFilter.expireDate
                    : this.$root.$refs.partExchangeBlock.expireDate;
            },

            PartExchangeFilter() {
                return this.$root.$refs.partExchange.$refs.partExchangeFilter;
            }
        },

        methods: {
            translateString,

            removePX() {
                this.$emit('remove');
            },

            openTradeInModalPxActive(isExpired) {
                this.$emit('open', isExpired);
            }
        }
    })
</script>
