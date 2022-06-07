/**
 * Global variable for cms pages
 */
var topHeightCms = jQuery('header').height() + jQuery('.breadcrumbs-wrapper').height()
        + jQuery('.messages-wrapper').height() + jQuery('.heading-image').height(),
    scrollSpeed = 700;

/**
 * Creates element that allows to scroll back to the top of the page content
 *
 * @param scroll
 * @param content
 */
function backToTop(scroll, content) {
    var scrollTop = scroll.scrollTop(),
        html = '<div class="scroll-top"><span></span></div>';

    if (scrollTop > topHeightCms) {
        if (!(jQuery('.scroll-top').length)) {
            jQuery(content).append(html);
        }
    } else {
        jQuery('.scroll-top').remove();
    }
}

/**
 * On scroll element click scroll to the top of the page
 */
jQuery('html').on('click touchstart', '.scroll-top', (e) => {
    e.preventDefault();

    jQuery('html, body').animate({
        scrollTop: topHeightCms
    }, scrollSpeed);
});

jQuery(window).scroll((e) => {
    backToTop(jQuery(e.target), '.cms-content');
});
