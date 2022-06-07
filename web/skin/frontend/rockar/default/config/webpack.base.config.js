const CONFIG = require('./global.config.js');

const webpack = require('webpack');
const path = require('path');
const chalk = require('chalk');
const argv = require('yargs').argv;
const eslintStatus = argv.hardLint ? true : false;

const ProgressBarPlugin = require('progress-bar-webpack-plugin');
const FriendlyErrorsPlugin = require('friendly-errors-webpack-plugin');
const notifier = require('node-notifier');
const HappyPack = require('happypack');

const nodeModulesPath = path.resolve(__dirname, '../../node_modules');
const rootPath = path.resolve(__dirname, '../../');
const configPath = path.resolve(__dirname);

process.noDeprecation = true;

module.exports = {
    devtool: 'eval',
    context: rootPath,
    entry: CONFIG.entry,

    output: {
        filename: './[name]/js/application.js'
    },

    resolve: {
        alias: CONFIG.aliases,
        extensions: ['.js', '.vue'],
        modules: [nodeModulesPath],
        symlinks: false
    },

    resolveLoader: {
        modules: [nodeModulesPath]
    },

    module: {
        rules: [
            {
                test: /\.(js|vue)$/,
                use: 'happypack/loader?id=eslint',
                enforce: 'pre',
                exclude: [
                    /node_modules/,
                    /vendor/
                ]
            },

            {
                test: /\.vue$/,
                use: 'happypack/loader?id=vue',
            },

            {
                test: /\.js$/,
                use: 'happypack/loader?id=babel',
                exclude: [
                    /node_modules/,
                    /vendor/
                ]
            }
        ]
    },

    plugins: [
        new webpack.LoaderOptionsPlugin({
            debug: true
        }),

        new webpack.ProvidePlugin({
            Vue: 'core/vendor/vue'
        }),

        new ProgressBarPlugin({
            format: '[:bar] ' + chalk.green.bold(':percent') + ' :msg',
            clear: true
        }),

        new FriendlyErrorsPlugin({
            clearConsole: false,
            onErrors: (severity, errors) => {
                var files = '';
                errors.forEach(error => {
                    files += error.file + '\r';
                });
                notifier.notify({
                    title: 'JS compilation ' + severity,
                    message: files.length ? files : ''
                });
            }
        }),

        new HappyPack({
            verbose: false,
            threads: 1,
            id: 'eslint',
            loaders: [{
                loader: 'eslint-loader',
                options: {
                    configFile: `${configPath}/.eslintrc.js`,
                    emitError: eslintStatus,
                    emitWarning: !eslintStatus,
                    failOnWarning: eslintStatus,
                    failOnError: eslintStatus
                }
            }]
        }),

        new HappyPack({
            verbose: false,
            threads: 1,
            id: 'vue',
            loaders: [{
                loader: 'vue-loader'
            }]
        }),

        new HappyPack({
            verbose: false,
            threads: 1,
            id: 'babel',
            loaders: [{
                loader: 'babel-loader',
                options: {
                    presets: ['es2015', 'stage-2'],
                    plugins: ['transform-runtime'],
                    comments: false
                }
            }]
        })
    ]
}
