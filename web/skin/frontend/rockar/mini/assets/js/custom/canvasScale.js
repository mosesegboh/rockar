function adjustCanvas() {
    jQuery('canvas').each((e) => {
        if (jQuery(e).closest('.map-wrapper').length === 0) {
            const cWidth = jQuery(e).parent().width();
            const scalePercent = 40;

            if (cWidth > 0) {
                jQuery(e).css({
                    'max-height': `${cWidth / 100 * scalePercent}px`
                });
            }
        }
    });
}

jQuery(() => {
    jQuery(window).on('resize', adjustCanvas);
    setTimeout(adjustCanvas, 2000);
});
