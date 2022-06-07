<template>
    <div class="test-drive-card" v-for="(index, drive) in booking.data" :key="index">
        <div class="test-drive-card-top">
            <div class="text">
                <h3>{{ getCarTitle(drive) }}</h3>
                <p class="dsp2-caption">{{ drive.vehicles[0].bodystyle }}</p>
            </div>
            <div class="image">
                <img :src="drive.vehicles[0].image" :alt="drive.vehicles[0].bodystyle" />
            </div>
        </div>

        <div class="test-drive-card-body" v-if="drive.vehicles[0].extras && drive.vehicles[0].extras.length > 0">
            <app-accordion
                title="Extra Features"
                :scroll-on-show="false"
                class-name="accordion-light"
                type="right-down"
                id="extra_features"
            >
                <ul>
                    <li class="options-item" v-for="(index, extra) in drive.vehicles[0].extras" :key="index">
                        <p>
                            {{ extra }}
                        </p>
                    </li>
                </ul>
            </app-accordion>
        </div>

        <div class="test-drive-card-bottom" :class="{ 'one-column': !isMapDisplayed }">
            <app-maps
                v-if="isMapDisplayed"
                :js-api="false"
                :api-key="mapApiKey"
                :zoom="10"
                :query="drive.place"
            ></app-maps>
            <div class="info-actions">
                <div class="info">
                    <div>
                        <span>{{ 'Dealer' | translate }}</span>
                        <span>{{ drive.place }}</span>
                    </div>
                    <div v-if="isDateDisplayed">
                        <span>{{ 'Date/Time' | translate }}</span>
                        <span>{{ drive.time | dateFormat 'D MMMM - ha' 'ddd MMM Do YYYY - hh:mm A' }}</span>
                    </div>
                </div>
                <div class="actions">
                    <button
                        v-if="booking.value === 'upcoming' && drive.vehicles[0].enabled"
                        class="button button-money" @click="editDrive(drive.id)"
                    >
                        {{ 'Edit' | translate }}
                    </button>
                    <app-confirmation-modal
                        :confirmation-question="'Do you really want to cancel?' | translate"
                        :render-in-body="true"
                    >
                        <button
                            v-if="booking.value === 'upcoming' || booking.value === 'requested'"
                            class="button dsp2-outline"
                            @click="cancelDrive(drive.id)"
                        >
                            {{ 'Cancel' | translate }}
                        </button>
                    </app-confirmation-modal>
                    <button
                        class="button dsp2-money"
                        @click="editDrive(drive.id)"
                        v-if="booking.value === 'uncompleted' && drive.vehicles[0].enabled && drive.booked_on"
                    >
                        {{ 'Rebook' | translate }}
                    </button>
                    <button
                        v-if="booking.value === 'completed' && drive.vehicles[0].chooseUrl && drive.vehicles[0].enabled"
                        class="button dsp2-money"
                        @click="chooseDrive(drive.vehicles[0].chooseUrl)"
                    >
                        {{ 'Choose' | translate }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import appMaps from 'dsp2/components/Elements/Maps';
    import appAccordion from 'core/components/Accordion';
    import appUniversalCarousel from 'dsp2/components/UniversalCarousel';
    import appConfirmationModal from 'dsp2/components/Elements/ConfirmationModal';

    export default Vue.extend({
        props: {
            mapApiKey: {
                required: true,
                type: String
            },

            booking: {
                required: true,
                type: Object
            },

            selected: {
                required: true,
                type: String
            }
        },

        computed: {
            isMapDisplayed() {
                const mapHiddenOptions = ['requested', 'uncompleted'];

                return !mapHiddenOptions.includes(this.selected);
            },

            isDateDisplayed() {
                const dateHiddenOptions = ['requested'];

                return !dateHiddenOptions.includes(this.selected);
            }
        },

        methods: {
            editDrive(id) {
                this.$emit('edit-drive', id);
            },

            cancelDrive(id) {
                this.$emit('cancel-drive', id);
            },

            updateDrive(url) {
                this.$emit('update-drive', url);
            },

            getCarTitle(drive) {
                return drive.vehicles[0].short_description;
            }
        },

        components: {
            appMaps,
            appAccordion,
            appUniversalCarousel,
            appConfirmationModal
        }
    });
</script>
