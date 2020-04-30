const path = require('path');
const loaders = require('./loaders');
const plugins = require('./plugins');

const webpackDir = path.resolve(__dirname);
const rootDir = path.resolve(__dirname, '..');
const distDir = path.resolve(rootDir, 'dist');

module.exports = {
  entry: {
    svgSprite: path.resolve(webpackDir, 'svgSprite.js'),
    css: path.resolve(webpackDir, 'css.js'),
    scripts: path.resolve(rootDir, 'scripts/main.js')
  },
  module: {
    rules: [loaders.SVGSpriteLoader, loaders.CSSLoader, loaders.ImageLoader],
  },
  plugins: [
    plugins.ImageminPlugin,
    plugins.SpriteLoaderPlugin,
    plugins.MiniCssExtractPlugin,
    plugins.ProgressPlugin,
    plugins.CleanWebpackPlugin,
    plugins.BrowserSyncPlugin
  ],
  output: {
    path: distDir,
    filename: 'js/[name].js',
    sourceMapFilename: '[file].map'
  },
};
