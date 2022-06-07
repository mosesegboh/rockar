var path = require('path');
var projectRoot = path.resolve(__dirname, '../../../../../');

var config = {
    brands: [
        'default/'
    ],

    aliases: {},

    configFiles: {
        scsslint: './default/config/_scss-lint.yml',
        eslint: './default/config/.eslintrc',
        webpackBase: './default/config/webpack.base.config',
        webpackProd: './default/config/webpack.prod.config'
    },

    eslintWarningsThreshhold: 30,

    reportsDirectory: projectRoot + '/var/report',

    client: {
        baseDirectory: './',
        vueDirectory: './default/vue/',
        customDirectory: './default/assets/js/',

        sprites: {
            name: 'sprites.png',
            prefix: 'sprite-',
            source: 'assets/sprites/**/*',
            buildSprites: '../../[name]spritesheets/',
            buildPath: 'spritesheets/',
            sass: '_sprites.scss',
            sassPath: '/assets/scss/utils'
        },

        customJS: {
            paths: [
                'assets/js/vendor/*',
                'assets/js/plugins/*',
                'assets/js/custom/*'
            ],
            destination: 'functions.js',
            babel: true,
            build: 'js/'
        },

        sass: {
            source: [
                'assets/scss/**/*.scss',
                '!assets/scss/utils/_sprites.scss',
                '!assets/scss/components/_highlight.scss',
                '!assets/scss/components/_postcodeanywhere.scss',
                '!assets/scss/components/_slider.scss',
                '!assets/scss/components/_slick.scss',
                '!assets/scss/components/_carousel.scss'
            ],
            entry: 'assets/scss/style.scss',
            build: 'css/',
            maxBuffer: 1228800,

            emails: {
                entry: 'assets/scss/email-inline.scss',
                build: 'css/'
            },

            emails_non_inline: {
                entry: 'assets/scss/email-non-inline.scss',
                build: 'css/'
            }
        },

        vueFiles: [
            'components/**',
            'filters/*',
            'js/*',
            'mixins/*',
            'transitions/*',
            'utils/*',
            'store/*'
        ],

        customFiles: [
            'main.js',
            'custom/**'
        ],

        buildToCleanDirectory: [
            'css/*',
            'spritesheets/*',
            'js/*'
        ]
    },

    getBrandsEntry() {
        var entries = {};

        config.brands.forEach((brand) => {
            var name = brand.slice(0, -1);

            entries[name] = [
                `./${name}/assets/js/main`,
                // `./${name}/assets/scss/style`
            ];
        });

        return entries;
    },

    getAliasEntry() {
        var aliases = {
            core: path.join(__dirname, '../../default/vue')
        };

        config.brands.forEach((brand) => {
            var name = brand.slice(0, -1);

            if (name !== 'default') {
                aliases[name] = path.join(__dirname, '../../', name, '/vue');
            }
        });

        return aliases;
    },

    prefixPath: function(listOfFiles, prefix, replaceIgnored) {
        prefix = (typeof prefix !== 'undefined') ? prefix : config.client.baseDirectory;
        replaceIgnored = (typeof replaceIgnored !== 'undefined') ? replaceIgnored : true;

        var updatedList = [];
        config.brands.forEach(function(brand) {

            listOfFiles.forEach(function(item) {
                if (!replaceIgnored && item.charAt(0) !== '!') {
                    updatedList.push(prefix + brand + item);
                }

                if (replaceIgnored) {
                    if (item.charAt(0) == '!') {
                        item = item.substring(1);
                        updatedList.push('!' + prefix + brand + item);
                    } else {
                        updatedList.push(prefix + brand + item);
                    }
                }
            });

        });

        return updatedList;
    },

    prefixVue: function(listOfFiles, prefix) {
        prefix = (typeof prefix !== 'undefined') ? prefix : config.client.vueDirectory;

        return listOfFiles.map(function(item) {
            if (item.charAt(0) !== '!') {
                return prefix + '/vue/' + item;
            }
        });
    },

    prefixCustom: function(listOfFiles, prefix) {
        prefix = (typeof prefix !== 'undefined') ? prefix : config.client.customDirectory;

        return listOfFiles.map(function(item) {
            if (item.charAt(0) !== '!') {
                return prefix + '/assets/js/' + item;
            }
        });
    },

    normalizeSourceFiles: function() {
        var base = config.client;

        config.entry = config.getBrandsEntry();
        config.aliases = config.getAliasEntry();
        base.buildToCleanDirectory = config.prefixPath(base.buildToCleanDirectory);
        base.sass.source = config.prefixPath(base.sass.source);
    }
};

config.normalizeSourceFiles();

module.exports = config;
