const MinifyPlugin = require('babel-minify-webpack-plugin');
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');

const conf = {
  entry: {
    vendor_hd: './resources/assets/backstage/js/webpack_hd.js',
    vendor_bd: './resources/assets/backstage/js/webpack_bd.js',
  },
  output: {
    filename: '[name].js',
  },
  module: {
    rules: [
      {
        test: require.resolve('pace-progress'),
        loader: 'imports-loader?define=>false',
      }
    ],
  }
};

module.exports = {
  dev: Object.assign({
    mode: 'development',
    plugins: [
      new MomentLocalesPlugin({
        localesToKeep: ['es-us', 'zh-cn'],
      }),
    ],
  }, conf),
  prod: Object.assign({
    mode: 'production',
    plugins: [
      new MomentLocalesPlugin({
        localesToKeep: ['es-us', 'zh-cn'],
      }),
      new MinifyPlugin({}, {
        comments: false,
      }),
    ]
  }, conf),
};
