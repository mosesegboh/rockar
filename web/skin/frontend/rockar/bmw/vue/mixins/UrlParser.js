export default {
    methods: {
        parseURL(parser = window.location) {
            let i;
            let split;
            const queries = parser.search.replace(/^\?/, '').split('&');
            const searchObject = {};

            for (i = 0; i < queries.length; i++) {
                split = queries[i].split('=');
                split[0] = split[0].replace(/[\[\]']+/g, '');

                if (searchObject.hasOwnProperty(split[0])) {
                    if (searchObject[split[0]] instanceof Array) {
                        searchObject[split[0]].push(split[1]);
                    } else {
                        searchObject[split[0]] = [searchObject[split[0]], split[1]];
                    }
                } else {
                    searchObject[split[0]] = split[1];
                }
            }

            return {
                searchObject,
                protocol: parser.protocol,
                host: parser.host,
                hostname: parser.hostname,
                port: parser.port,
                pathname: parser.pathname,
                search: parser.search,
                hash: parser.hash
            };
        },

        makeURLSearch(searchObject) {
            const searchArray = [];

            Object.keys(searchObject).forEach((key) => {
                if (key && (typeof searchObject[key] !== 'undefined')) {
                    if (searchObject[key] instanceof Array) {
                        searchObject[key].forEach((item) => {
                            searchArray.push(`${key}[]=${item}`);
                        });
                    } else {
                        searchArray.push(`${key}=${searchObject[key]}`);
                    }
                }
            });

            return searchArray.join('&');
        }
    }
};
