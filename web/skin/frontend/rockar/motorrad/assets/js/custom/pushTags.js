function pushTags(eventType, virtualPage, pageTitle) {
    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            'event': eventType,
            'virtualPageURL': virtualPage,
            'virtualPageTitle': pageTitle
        });
    }
}

function pushEcommerceTags(tagObject) {
    if (typeof dataLayer !== 'undefined') {
        dataLayer.push(tagObject);
    }
}
