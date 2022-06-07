const $window = jQuery(window);
let menuHeight;
let $menu;
let menuPosition;
let $limit;
let limitInitialHeight;

/**
 * general function to check if mobile resolution is used.
 *
 * @returns {boolean}
 */
function isMobileResolution() {
    return (window.outerWidth <= 736);
}

function adjustStickyMenu() {
    if ($menu.length) {
        const scrollPosition = $window.scrollTop() + jQuery(window).height();
        const scrollMinPosition = scrollPosition - jQuery(window).height();
        const topMargin = 20;
        menuPosition = $limit.offset().top;
        menuHeight = jQuery('.menu-sticky').outerHeight();
        limitInitialHeight = isMobileResolution() ? 'auto' : $limit.outerHeight();

        $menu.parent().css({ height: limitInitialHeight });

        const limitHeight = $limit.outerHeight() + $limit.offset().top;
        let newPosition = 0;
        let atBottom = false;

        if (scrollPosition > limitHeight) {
            jQuery('.menu-sticky').addClass('menu-sticky-apply');
            newPosition = limitHeight - topMargin * 3;
            atBottom = true;
        } else if (scrollMinPosition > menuPosition) {
            jQuery('.menu-sticky').addClass('menu-sticky-apply');
            newPosition = scrollPosition - menuPosition + topMargin - jQuery(window).height();
            atBottom = false;
        } else {
            jQuery('.menu-sticky').removeClass('menu-sticky-apply');
            newPosition = 0;
            atBottom = false;
        }

        $menu.css({
            top: atBottom ? 'auto' : newPosition,
            bottom: atBottom ? 0 : 'auto'
        });
    }
}

function openFirstMenu() {
    const firstElement = $menu.find('li:first-child');

    if (!firstElement.hasClass('no-child')) {
        firstElement.addClass('active');
        firstElement.find('ul').show({
            duration: 250,
            progress() {
                adjustStickyMenu();
            }
        });
        firstElement.find('ul li:first-child').addClass('active active-element');
    }
}

/**
 * Sets menu to be at fixed position when scrolling long page
 *
 */
function setMenuFixed() {
    if ($menu.length) {
        menuHeight = jQuery('.menu-sticky').outerHeight();
        $menu.parent().css({
            position: 'relative',
            height: limitInitialHeight
        });

        $window.on('scroll', adjustStickyMenu);
        $window.on('resize', adjustStickyMenu);
    }
}

function setStickyVariables() {
    $menu = jQuery('.menu-sticky');
    $limit = jQuery('.menu-sticky-limit');
    limitInitialHeight = isMobileResolution() ? 'auto' : $limit.outerHeight();
    menuPosition = $limit.offset().top;
    setMenuFixed();
    openFirstMenu();
    adjustStickyMenu();
}

/**
 * Toggles menu sub-menu items
 *
 * @param clickedTitle
 */
function toggleSubMenu(clickedTitle) {
    const clickedMenuTitle = clickedTitle;

    jQuery('.cms-menu').find('.child, .no-child').each((e) => {
        jQuery(e).removeClass('active-element');
    });

    jQuery('.cms-menu .parent:not(.no-child) ul')
        .not(clickedMenuTitle.parent('li').children('ul'))
        .slideUp('slow')
        .parent('li').removeClass('active');

    clickedMenuTitle.parent('li').children('ul')
        .stop()
        .slideToggle({
            duration: 200,
            progress() {
                menuHeight = jQuery('.menu-sticky').outerHeight();
                adjustStickyMenu();
            }
        })
        .parent('li').toggleClass('active');
}

/**
 * When clicked on menu title or subtitle,
 * scroll to paragraph in page content
 *
 * @param titleId
 */
function bindScrollToTitleOnClick(titleId) {
    jQuery('html, body').animate({
        scrollTop: jQuery(`#${titleId}`).offset().top
    }, 1000);
}

/**
 * Create cms page left menu from page main and sub titles (passed through widgets)
 *
 * Function used in /cms/menu.phtml template
 *
 * @param container wrapper element where all page content (without menu block) is
 * @param mainTitle main title element in page content
 * @param subTitle sub title element in page content
 */
function cmsMenu(container, mainTitle, subTitle) {
    let menu = '<ul class="menu menu-sticky">';
    let i = 1;

    // Find all containers (.cms-content .content-block)
    jQuery(container).each((index, e) => {
        let j = 1;
        // Find main title in each container
        jQuery(e).find(mainTitle).each((index, e) => {
            const mainTitle = jQuery(e);
            const subTitles = mainTitle.parent().find(subTitle);

            mainTitle.attr('id', `title-${i}`);

            // Create main title menu item
            menu += '<li class="parent';
            menu += (subTitles.size() > 0) ? '"' : ' no-child"';
            menu += `data-link="title-${i}"><span>${mainTitle.text()}</span>`;

            // if found go through all sub title elements
            if (subTitles.size() > 0) {
                menu += '<ul class="subtitle-wrapper">';
                subTitles.each((index, e) => {
                    const subTitle = jQuery(e);

                    subTitle.attr('id', `subtitle-${i}-${j}`);

                    // create sub title/child menu item
                    menu += `<li class="child" data-link="subtitle-${i}-${j}">${jQuery(e).text()}</li>`;
                    j++;
                });
                menu += '</ul></li>';
            } else {
                menu += '</li>';
            }
            i++;
        });
    });
    menu += '</ul>';

    const menuContainer = jQuery('.cms-menu');

    // Create cms page menu
    menuContainer.html(menu);
    setStickyVariables();

    menuContainer.find('.child, .no-child').each((index, e) => {
        jQuery(e).click(() => {
            // If clicked on title with no sub-menu close active sub-menus
            if (!jQuery(e.currentTarget).hasClass('child')) {
                menuContainer.find('.parent:not(.no-child) ul')
                    .slideUp('slow')
                    .parent('li').removeClass('active');
            }
            menuContainer.find('.child, .no-child').each((index, e) => {
                jQuery(e).removeClass('active-element');
            });
            jQuery(e).addClass('active-element');
            const thing = jQuery(e).data('link');
            bindScrollToTitleOnClick(jQuery(e).data('link'));
        });
    });

    menuContainer.find('.parent:not(.no-child) span').each((index, e) => {
        jQuery(e).click((e) => {
            toggleSubMenu(jQuery(e.currentTarget));
        });
    });
}
