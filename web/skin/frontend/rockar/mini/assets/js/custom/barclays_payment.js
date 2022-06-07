jQuery(() => {
    if (jQuery('body').hasClass('cms-barclays')) {
        jQuery('select').each((index, el) => {
            new Select(jQuery(el), false, false, 4);
        });
    }
});
