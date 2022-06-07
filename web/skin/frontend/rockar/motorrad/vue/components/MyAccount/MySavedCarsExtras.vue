<template>
    <div class="row">
        <div class="col-9 car-extras">
            <p class="block-title h-common h-small">{{ 'Extras on this vehicle:' | translate }}</p>

            <app-accordion-group :open-first="false">
                <div class="accordion-group extras-on-car">
                    <app-accordion v-for="(title, data) in carExtras" :title="getExtrasTitle(title, data.length)" :scroll-on-show="false" class-name="accordion-light" type="right-down" :id="$index" track-by="$index">
                        <li class="accordion-list">
                            <template v-if="data.length > 0">
                                <table class="table table-responsive table-borderless">
                                    <tbody>
                                        <tr v-for="item in data" track-by="$index">
                                            <td>{{ item.label }}</td>
                                            <td class="extras-price">{{ item.price === false ? '0.00' : item.price | numberFormat 0,0.00 }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>
                        </li>
                    </app-accordion>
                </div>
            </app-accordion-group>
        </div>
    </div>
</template>

<script>
    import appAccordion from 'motorrad/components/Elements/Accordion';
    import appAccordionGroup from 'motorrad/components/AccordionGroup';

    export default Vue.extend({
        props: {
            carExtras: {
                required: true,
                type: Object
            }
        },

        methods: {
            getExtrasTitle(title, count) {
                return `${title} (${count})`;
            }
        },

        components: {
            appAccordion,
            appAccordionGroup
        }
    });
</script>
