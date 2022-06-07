<template>
    <div class="finance-selection" id="finance-selection">
        <div class="finance-options" v-if="isDesktopView()">
            <div class="finance-groups">
                <div class="label">
                    <p>{{ 'Finance options:' | translate }}</p>
                </div>

                <app-accordion-group>
                    <div class="group-wrapper accordion-group" v-for="(index, group) in financeGroups">
                      <template v-if="group.optionGroupTitle !== undefined">
                          <div class="finance-group-accordion" v-if="group.groupedItems.length > 1">
                              <app-accordion
                                      :id="group.optionGroupTitle"
                                      :title="group.optionGroupTitle"
                                      :scroll-on-show="false"
                                      :show="group.optionGroupTitle === selectedGroupName && canOpenAccordions"
                                      type="right-down"
                                      class-name="accordion-light"
                                      @click="openAccordionGroup(group)"
                              >
                                  <div class="wrapper">
                                      <button v-for="option in group.groupedItems"
                                              @click="selectFinance(option.group_id)"
                                              class="finance-group-button"
                                              :class="{'selected': option.group_id == activeFinanceGroupId}"
                                      >
                                          {{ option.group_title | translate }}
                                      </button>
                                  </div>
                              </app-accordion>
                          </div>
                          <div class="finance-group-button" v-if="group.groupedItems.length === 1">
                              <button
                                      @click="selectFinance(group.groupedItems[0].group_id)"
                                      class="finance-group-button"
                                      :class="{'selected': group.groupedItems[0].group_id == activeFinanceGroupId}"
                              >
                                  {{ group.groupedItems[0].group_full_title ? group.groupedItems[0].group_full_title : group.groupedItems[0].group_title | translate}}
                              </button>
                          </div>
                      </template>
                      <div class="finance-group-button" v-else>
                            <button
                                    :id="group.group_title"
                                    class="button-gray-light-2 finance-group-button"
                                    @click="selectFinance(group.group_id)"
                                    :class="{'selected': group.group_id == activeFinanceGroupId}"
                            >
                                {{ group.group_full_title ? group.group_full_title : group.group_title | translate}}
                            </button>
                        </div>
                    </div>
                </app-accordion-group>
            </div>
            <div class="finance-video" v-if="selectedFinanceGroupData && showIframe">
                <div class="aspect-ratio-wrapper">
                    <div class="aspect-ratio-16-9">
                        <div class="video-wrapper">
                            <div class="video-border">
                                <div class="video">
                                    {{{ selectedFinanceGroupData.video }}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="finance-options-mobile" v-if="!isDesktopView()">
            <div class="select-wrapper" :class="{'selected': activeFinanceGroupId > 0}">
                <div class="question-icon-wrapper" @click="showFinanceGroupInfo = !showFinanceGroupInfo">
                    <span class="icon icon-question"></span>
                </div>
                <app-car-finder-select
                        @select="selectFinance"
                        :options="financeGroupsMobile"
                        :init-selected="selectedMobileGroupIndex"
                ></app-car-finder-select>
            </div>
            <div class="finance-info" v-show="showFinanceGroupInfo">
                <div class="finance-video" v-if="selectedFinanceGroupData && showIframe">
                    <div class="aspect-ratio-wrapper">
                        <div class="aspect-ratio-16-9">
                            <div class="video-wrapper">
                                <div class="video-border">
                                    <div class="video">
                                        {{{ selectedFinanceGroupData.video }}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="finance-option-description">
                    <strong class="finance-option-description-header" v-if="selectedFinanceGroupData">{{ selectedFinanceGroupData.group_title }}</strong>
                    <p class="finance-option-description-text" v-if="selectedFinanceGroupData" v-html="selectedFinanceGroupData.group_description"></p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import coreFinanceOptions from 'core/components/CarFinder/FinanceOptions';
    import appAccordionGroup from 'bmw/components/AccordionGroup';
    import responsiveBreakpoints from 'bmw/components/Shared/ResponsiveBreakpoints';

    export default coreFinanceOptions.extend({
        mixins: [responsiveBreakpoints],

        components: {
            appAccordionGroup
        },

        computed: {
            /**
             * Finance Filter Vue component
             */
            financeFilter() {
                return this.$root.$refs.financeFilter;
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
