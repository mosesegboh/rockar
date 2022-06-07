const path = require('path');
const projectRoot = path.resolve(process.cwd(), '../../../'); // step back from skin/frontend/rockar
const defaultTheme = 'default2'

const paths = {
    projectRoot,
    compilationRoot: process.cwd(),
    nodeModulesPath: path.resolve(process.cwd(), 'node_modules'),
    defaultThemePath: path.resolve(process.cwd(), defaultTheme),
    configPath: path.resolve(process.cwd(), defaultTheme, 'config'),
    vueDirectory: path.resolve(process.cwd(), defaultTheme, 'vue'),
    customDirectory: path.resolve(process.cwd(), defaultTheme, 'assets/js'),
    reportsDirectory: path.resolve(projectRoot, '/var/report'),
};

const config = {
    paths,

    brands: [
        'default2/',
        'bmw/',
        'mini/',
        'motorrad/',
        'bmw2/',
        'dsp2/',
        'mini2/',
        'motorrad2/'
    ],

    aliases: {},

    configFiles: {
        scsslint: path.resolve(paths.configPath, '.scss-lint.yml'),
        eslint: path.resolve(paths.configPath, '.eslintrc'),
        webpackBase: path.resolve(paths.configPath, 'webpack.base.config'),
        webpackProd: path.resolve(paths.configPath, 'webpack.prod.config')
    },

    eslintWarningsThreshhold: 30,

    client: {
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
                '!assets/scss/components/_carousel.scss',
                '!assets/scss/components/_scrollbar.scss'
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
        const entries = {};

        config.brands.forEach((brand) => {
            const name = brand.slice(0, -1);

            entries[name] = [
                path.resolve(paths.compilationRoot, name, 'assets/js/main'),
                // path.resolve(paths.compilationRoot, name, 'assets/scss/style')
            ];
        });

        return entries;
    },

    getAliasEntry() {
        const aliases = {
            core: paths.vueDirectory
        };

        config.brands.forEach((brand) => {
            const name = brand.slice(0, -1);

            if (name !== defaultTheme) {
                aliases[name] = path.resolve(paths.compilationRoot, name, 'vue');
            }
        });

        return aliases;
    },

    prefixPath: (listOfFiles, subPath = '') => {
        const updatedList = [];

        config.brands.forEach((brand) => {
            listOfFiles.forEach((item) => {
                if (item.charAt(0) !== '!') {
                    updatedList.push(path.resolve(paths.compilationRoot, brand, subPath, item));
                } else {
                    item = item.substring(1);
                    updatedList.push(`!${path.resolve(paths.compilationRoot, brand, subPath, item)}`);
                }
            });

        });

        return updatedList;
    },

    normalizeSourceFiles: () => {
        const base = config.client;

        config.entry = config.getBrandsEntry();
        config.aliases = config.getAliasEntry();
        base.buildToCleanDirectory = config.prefixPath(base.buildToCleanDirectory);
        base.vueFiles = config.prefixPath(base.vueFiles, 'vue');
        base.customFiles = config.prefixPath(base.customFiles, 'assets/js');
        base.sass.source = config.prefixPath(base.sass.source);
    }
};

config.normalizeSourceFiles();

module.exports = config;
