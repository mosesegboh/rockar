<template>
    <div>
        <div class="compare-link-content">
            <template v-if="currentDevice !== 'mobile'">
                <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                    <a @click="openCompare" href="javascript:void(0);" class="button button-empty-gray" slot="tooltipElement">{{ 'Compare Vehicles' | translate }}
                        <span class="badge">{{ compare.carsIn }}</span>
                    </a>

                    <div class="tooltip-content"  slot="tooltipContent">
                        <p>{{ 'If you want to add a car to compare, click on the button underneath the vehicle image  that says \‘Add to Compare\’.' | translate }}</p>
                    </div>
                </app-tooltip>
            </template>
            <template v-else>
                <a @click="openCompare" href="javascript:void(0);" class="button button-empty-gray">{{ 'Compare Motorcycles' | translate }}
                    <span class="badge">{{ compare.carsIn }}</span>
                </a>
            </template>
        </div>

        <app-modal :show.sync="openWarning" class-name="simple-popup">
            <div slot="content" class="compare-warning">
                <div class="compare-content" v-if="compare.carsIn == 0">
                    <p class="h1">{{ 'No vehicles in compare list:' | translate }}</p>
                    <p>{{ 'There were no vehicles selected for comparison | translate }}</p>
                </div>

                <div class="compare-content" v-if="compare.carsIn == 1">
                    <p class="h1">{{ 'More vehicles needed for compare:' | translate }}</p>
                    <p>{{ 'There was only 1 vehicle selected for comparison. Please select at least 1 other vehicle for comparison.' | translate }}</p>
                </div>
            </div>
        </app-modal>
    </div>
</template>

<script>
    import appModal from 'core/components/Elements/Modal';
    import appTooltip from 'core/components/Elements/Tooltip';

    export default Vue.extend({
        components: {
            appModal,
            appTooltip
        },

        data() {
            return {
                openWarning: false,
                compare: this.$parent.$parent.compare
            }
        },

        computed: {
            currentDevice() {
                return this.$store.state.general.device;
            },
        },

        methods: {
            openCompare() {
                if (this.compare.carsIn > 1) {
                    window.location.href = this.compare.pageUrl;
                } else {
                    this.openWarning = true;
                }
            }
        }
    });
</script>
