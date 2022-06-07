jQuery(() => {

    const sendInitialHomeOfferGA = function() {
        var offerWrappers = jQuery('.widget.widget-static-block').filter('[data-id]').has('.offer'),
            promotions = [];

        if (offerWrappers.length) {
            offerWrappers.each(function(i, el) {
                var cmsBlock = jQuery(el);

                promotions.push({
                    'id': cmsBlock.data('id'),
                    'name': cmsBlock.data('title'),
                    'creative': 'homepage hero-wrapper offer',
                    'position': i + 1,
                });

            });

            const homeOfferObjectArray = {
                'event': 'promoView',
                'ecommerce': {
                    'promoView': {
                        'promotions': promotions
                    }
                }
            };

            pushEcommerceTags(homeOfferObjectArray);
        }
    };

    const sendHomeOfferGA = function(button) {
        var parentWrapper = button.closest('.widget.widget-static-block');

        if (parentWrapper.length) {
            var parentIndex = jQuery('.widget.widget-static-block').filter('[data-id]').index(parentWrapper) + 1;

            const homeOfferObject = {
                'event': 'promotionClick',
                'ecommerce': {
                    'promoClick': {
                        'promotions': [
                            {
                                'id': parentWrapper.data('id'),
                                'name': parentWrapper.data('title'),
                                'creative': 'homepage hero-wrapper offer',
                                'position': parentIndex,
                            }
                        ]
                    }
                },
                'eventCallback'() {
                    document.location = button.attr('href');
                }
            };

            pushEcommerceTags(homeOfferObject);
        }
    };

    jQuery('.hero-wrapper.offer .offer-details .button').on('click', (e) => {
        e.preventDefault();
        sendHomeOfferGA(jQuery(e.currentTarget));
    });

    jQuery(document).ready(sendInitialHomeOfferGA());
});