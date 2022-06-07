jQuery(() => {
    const toggleFixedHeader = function() {
        const fNav = jQuery('.fixed-header');
        const hDesc = jQuery('.header-desktop');

        if (hDesc.is(':visible')) {
            const offset = hDesc.height();
            const wyPos = jQuery(this).scrollTop();

            if (wyPos > offset) {
                fNav.addClass('expanded');
            } else {
                fNav.removeClass('expanded');
            }
        } else {
            fNav.removeClass('expanded');
        }
    };

    jQuery(window).on('scroll', toggleFixedHeader);
    jQuery(document).ready(toggleFixedHeader);

    jQuery('.sidebar-toggle').on('click', () => {
        jQuery('html').toggleClass('nav-expanded');
    });

    jQuery('.sidebar .expand > a').on('click', (e) => {
        e.preventDefault();

        if (jQuery(e.currentTarget).next('ul').hasClass('expanded')) {
            jQuery('.sidebar ul.expanded').slideUp().removeClass('expanded');
        } else {
            jQuery('.sidebar ul.expanded').slideUp().removeClass('expanded');
            jQuery(e.currentTarget).next('ul').addClass('expanded').slideDown();
        }
    });

    jQuery('.header-mobile .navigation .search').on('click', () => {
        jQuery('.header-mobile .navigation').toggleClass('closed');
    });
});
