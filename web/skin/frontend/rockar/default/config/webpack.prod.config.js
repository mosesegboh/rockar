var webpack = require('webpack');
var config = require('./webpack.base.config');
var ProgressBarPlugin = require('progress-bar-webpack-plugin');
var chalk = require('chalk');

config.devtool = false;
config.plugins = [
    new webpack.DefinePlugin({
        PRODUCTION: true
    }),

    new webpack.optimize.UglifyJsPlugin({
        comments: false
    }),

    new webpack.LoaderOptionsPlugin({
        debug: false
    })
].concat(config.plugins || []);

module.exports = config;
