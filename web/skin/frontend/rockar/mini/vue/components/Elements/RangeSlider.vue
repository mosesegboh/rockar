<script>
    import RangeSlider from 'core/components/Elements/RangeSlider';

    export default Vue.extend({
        mixins: [RangeSlider],

        methods: {
            generateSteps(slide) {
                const $sliderEl = jQuery(this.$el).find('.range-slider');

                if (this.displaySteps) {
                    const opt = jQuery(slide).data().uiSlider.options;
                    const vals = opt.max - opt.min;

                    for (let i = 0; i <= vals; i++) {
                        const el = jQuery('<label class="slider-label"></label>').css('left', `${(i / vals * 100)}%`);
                        $sliderEl.append(el);
                    }
                }
            }
        }
    });
</script>