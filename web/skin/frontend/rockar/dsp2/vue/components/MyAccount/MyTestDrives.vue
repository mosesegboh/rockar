<template>
    <div class="my-account-wrapper my-test-drives">
        <div class="my-test-drives-wrapper">
            <div class="general-preloader" v-show="ajaxLoading"><div class="show-loading"></div></div>
            <div class="my-test-drives-header">
                <div class="desktop">
                    <div
                        v-for="(index, option) in tabOptions"
                        :key="index"
                        class="desktop-tab"
                        :class="{ 'active': option.value === selected }"
                        @click="selectTab(option)"
                    >
                        <div class="desktop-tab-title">
                            {{ option.title | translate }}
                        </div>
                    </div>
                </div>
                <div class="mobile">
                    <app-select
                        @select="selectTab"
                        :options="tabOptions"
                        :init-selected="selected"
                    ></app-select>
                </div>
            </div>
            <template v-for="option in tabOptions" :key="option.value">
                <div v-if="selected === option.value">
                    <div class="test-drive-cards-wrapper">
                        <template v-if="isMobile">
                            <app-my-test-drives-booking
                                :booking="option"
                                :map-api-key="mapApiKey"
                                :selected="selected"
                                v-on:cancel-drive="cancelDrive"
                                v-on:edit-drive="editDrive"
                                v-on:update-drive="updateDrive"
                            />
                        </template>
                        <template v-else>
                            <app-universal-carousel
                                class-name="test-drives-carousel"
                                :settings="carouselSettings">
                                <app-my-test-drives-booking
                                    :booking="option"
                                    :map-api-key="mapApiKey"
                                    :selected="selected"
                                    v-on:cancel-drive="cancelDrive"
                                    v-on:edit-drive="editDrive"
                                    v-on:update-drive="updateDrive"
                                />
                            </app-universal-carousel>
                        </template>
                    </div>
                    <p class="empty-test-drive-text" v-if="!option.data.length">
                        {{  option.emptyText | translate }}
                    </p>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    import coreAppMyTestDrives from 'core/components/MyAccount/MyTestDrives';
    import appSelect from 'dsp2/components/Elements/Select';
    import appMyTestDrivesBooking from 'dsp2/components/MyAccount/MyTestDrivesBooking';
    import appUniversalCarousel from 'dsp2/components/UniversalCarousel';
    import translateString from 'core/filters/Translate';

    export default coreAppMyTestDrives.extend({
        props: {
            testDriveRequests: {
                required: true,
                type: Array
            }
        },

        data() {
            return {
                isMobile: true,
                selected: 'upcoming'
            }
        },

        computed: {
            tabOptions() {
                return [
                    {
                        title: translateString('Upcoming Test Drives'),
                        value: 'upcoming',
                        data: this.activeTestDrives,
                        emptyText: translateString('You currently don\'t have any active test drives')
                    },
                    {
                        title: translateString('Test Drive Requests'),
                        value: 'requested',
                        data: this.testDriveRequests,
                        emptyText: translateString('You currently don\'t have any requested test drives')
                    },
                    {
                        title: translateString('Uncompleted Test Drives'),
                        value: 'uncompleted',
                        data: this.cancelledTestDrives,
                        emptyText: translateString('You currently don\'t have any uncompleted test drives')
                    },
                    {
                        title: translateString('Completed Test Drives'),
                        value: 'completed',
                        data: this.completedTestDrives,
                        emptyText: translateString('You currently don\'t have any completed test drives')
                    }
                ];
            },

            slidesToShow() {
                return this.tabOptions.some((item) => item.value === this.selected && item.data.length > 1) ? 2 : 1;
            },

            carouselSettings() {
                return {
                    'slidesToShow': 1,
                    'initialSlide': 0,
                    'prevArrow': '<span class="slick-prev">Previous</span>',
                    'nextArrow': '<span class="slick-next">Next</span>',
                    'dots': false,
                    'lazyload': 'ondemand',
                    'infinite': false,
                    'mobileFirst': true,
                    'speed': 300,
                    'responsive': [
                        {
                            'breakpoint': 1024,
                            'settings': {
                                'slidesToShow': 2
                            }
                        }
                    ]
                };
            }
        },

        methods: {
            cancelDriveSuccess(resp) {
                this.ajaxLoading = false;

                let itemIndex = this.activeTestDrives.findIndex((item) => item.id === resp.data.bookingId);

                if (itemIndex === -1) {
                    itemIndex = this.testDriveRequests.findIndex((item) => item.id === resp.data.bookingId)
                }

                if (itemIndex >= 0) {
                    this.$broadcast('Carousel::removeFromSlider', itemIndex, resp.data.bookingId);

                    if (!this.removeCanceledTestDrives(itemIndex, resp.data.bookingId)) {
                        this.removeCanceledTestDriveRequests(itemIndex, resp.data.bookingId);
                    }
                }
            },

            removeCanceledTestDrives(itemIndex, bookingId) {
                if (this.activeTestDrives[itemIndex] && this.activeTestDrives[itemIndex].id === bookingId) {
                    this.cancelledTestDrives.push(this.activeTestDrives[itemIndex]);
                    this.activeTestDrives = this.activeTestDrives.filter((_, i) => i !== itemIndex);

                    return true;
                }

                return false;
            },

            removeCanceledTestDriveRequests(itemIndex, bookingId) {
                if (this.testDriveRequests[itemIndex] && this.testDriveRequests[itemIndex].id === bookingId) {
                    this.cancelledTestDrives.push(this.testDriveRequests[itemIndex]);
                    this.testDriveRequests = this.testDriveRequests.filter((_, i) => i !== itemIndex);
                }
            },

            selectTab(data) {
                this.selected = data.value;
            },

            handleResize() {
                this.isMobile = document.documentElement.clientWidth < 1024;
            }
        },

        events: {
            'Carousel::itemRemoved'(itemIndex, bookingId) {
                if (!this.removeCanceledTestDrives(itemIndex, bookingId)) {
                    this.removeCanceledTestDriveRequests(itemIndex, bookingId);
                }
            }
        },

        ready() {
            this.isMobile = document.documentElement.clientWidth < 1024;

            window.addEventListener('resize', this.handleResize);
        },

        beforeDestroy() {
            window.removeEventListener('resize', this.handleResize);
        },

        components: {
            appSelect,
            appMyTestDrivesBooking,
            appUniversalCarousel
        }
    });
</script>
