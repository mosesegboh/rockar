<template>
    <div class="finance-selection" id="finance-selection">
        <div class="finance-options">
            <div class="finance-groups">
                <app-select
                    @select="selectFinance"
                    :options="financeGroupOptions"
                    :init-selected="selectedMobileGroupIndex"
                    :label="'Choose Payment Option'"
                ></app-select>
            </div>
        </div>
    </div>
</template>

<script>
    import coreFinanceOptions from 'core/components/CarFinder/FinanceOptions';
    import appAccordionGroup from 'dsp2/components/AccordionGroup';
    import responsiveBreakpoints from 'dsp2/components/Shared/ResponsiveBreakpoints';
    import appSelect from 'dsp2/components/Elements/Select';

    export default coreFinanceOptions.extend({
        mixins: [responsiveBreakpoints],

        components: {
            appAccordionGroup,
            appSelect
        },

        computed: {
            /**
             * Finance Filter Vue component
             */
            financeFilter() {
                return this.$root.$refs.financeFilter;
            },

            financeGroupOptions() {
                const groups = this.financeOptions.items;

                const groupedGroups = [];

                Object.keys(groups).forEach((key) => {
                    groupedGroups.push({
                        title: groups[key].group_full_title,
                        value: groups[key].group_id
                    });
                });

                return groupedGroups;
            }
        },

        methods: {
            /**
             * Override default2 selectFinance method.
             * when different finance type is selected need rerun method to pull in
             * correct min, max, step on slider
             */
            selectFinance(data) {
                this.$super(coreFinanceOptions, 'selectFinance', data);

                if (this.financeFilter) {
                    this.financeFilter.changePayInFull();
                }
            }
        },

        events: {
            'FinanceOptions::stopVideo'() {
                this.showIframe = false;
            },

            'FinanceOptions::playVideo'() {
                this.showIframe = true;
            }
        }
    });
</script>
