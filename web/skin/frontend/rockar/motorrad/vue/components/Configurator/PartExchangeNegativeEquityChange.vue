<template>
    <div class="part-exchange-negative-equity-change">
        <div class="row">
            <p class="px-light-text">{{ 'Trade-in Settlement Due:' | translate }}</p>
            <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                <span class="action-badge info-small" slot="tooltipElement"></span>
                <div slot="tooltipContent">
                    <p>
                        Based on the information provided, you are required to settle the net amount due on your Trade-in vehicle.
                        This is the difference between the trade-in value and the finance amount still owing on the vehicle.
                    </p>
                </div>
            </app-tooltip>
            <p class="h1">{{ outStandingEquity | numberFormat '0,0' true }}</p>
            <app-select
                @select="selectEquityMethod"
                :options="negativeEquity"
                :item-height="50"
            ></app-select>
            {{ equityMethod.text }}
        </div>
        <div class="px-result-actions">
            <button class="button-default">{{ 'Continue with offer' | translate }}</button>
            <button class="button-empty">{{ 'Continue without Trade in' | translate }}</button>
        </div>
    </div>
</template>

<script>
    import appTooltip from 'core/components/Elements/Tooltip';
    import appSelect from 'core/components/Elements/Select';

    export default Vue.extend({
        data() {
            return {
                negativeEquity: [
                    {
                        title: 'Add to monthly payment',
                        value: 1,
                        text: 'Pay-off your trade-in shortfall on a monthly basis without the requirement of a lump sum cash payment.'
                    },
                    {
                        title: 'Once-off payment',
                        value: 2,
                        text: 'Settle your trade-in shortfall in a single lump sum payment.'
                    }
                ],

                equityMethod: {}
            }
        },

        methods: {
            selectEquityMethod(data) {
                this.equityMethod = data;
            }
        },

        components: {
            appTooltip,
            appSelect
        }
    });
</script>