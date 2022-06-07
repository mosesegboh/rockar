<template>
    <app-modal :show.sync="show" class-name="car-compare-clear simple-popup">
        <div slot="content">
            <header class="modal-header">
                <span class="desktop">{{ 'Clear Compare List' | translate }}</span>
                <span class="mobile">{{ 'Compare List' | translate }}</span>
            </header>
            <div class="row">
                <p>
                    {{ 'Are you sure you want to clear the compare list?' | translate }}
                </p>
            </div>
            <div class="cta">
                <button class="button dsp2-outline" @click=closePopup()>{{ 'Cancel' | translate }}</button>
                <button class="button" :class="getButtonClass" @click="clearAll() + closePopup()">{{ 'Clear'| translate }}</button>
            </div>
        </div>
    </app-modal>
</template>

<script>
    import appModal from 'dsp2/components/Elements/Modal';

    export default Vue.extend({
        components: {
            appModal
        },

        props: {
            show: {
                type: Boolean,
                required: false,
                default: false
            },

            compareClearUrl: {
                required: true,
                type: String
            }
        },

        events: {
            'CarCompare::openClearPopup'() {
                this.show = true;
            }
        },

        methods: {
            clearAll() {
                this.$parent.$broadcast('CarCompare::clearAll', this.compareClearUrl);
            },

            closePopup() {
                this.show = false;
            }
        },

        computed: {
            getButtonClass() {
                return 'dsp2-money';
            }
        }
    });
</script>
