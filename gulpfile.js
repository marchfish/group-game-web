const exec = require('child_process').exec;
const minimist = require('minimist');
const gulp = require('gulp');
const plumber = require('gulp-plumber');
const concat = require('gulp-concat');
const ifelse = require('gulp-if-else');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const compiler = require('webpack');
const webpack = require('webpack-stream');

const conf = minimist(process.argv.slice(2), {
  default: {
    env: 'dev',
    autoprefixer: {},
    cssnano: {
      preset: [
        'default',
        {
          discardComments: {
            removeAll: true,
          },
        },
      ]
    },
    webpack: require('./webpack.config.js'),
  },
});

const admin = {
  clear: function (cb) {
    exec('rm -fr public/backstage/css/* public/backstage/js/* public/backstage/fonts/* public/backstage/plugins/*', cb);
  },
  css: function () {
    return gulp
      .src([
        'node_modules/bootstrap/dist/css/bootstrap.css',
        'resources/assets/backstage/css/pace.css',
        'resources/assets/backstage/css/spacing.css',
        'node_modules/admin-lte/dist/css/AdminLTE.css',
        'node_modules/admin-lte/dist/css/skins/skin-blue.css',
        'node_modules/font-awesome/css/font-awesome.css',
        'node_modules/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css',
        'resources/assets/backstage/css/main.css',
      ])
      .pipe(plumber())
      .pipe(concat('vendor.css'))
      .pipe(ifelse(conf.env == 'prod', function() {
        return postcss([
          autoprefixer(conf.autoprefixer),
          cssnano(conf.cssnano),
        ]);
      }, function() {
        return postcss([
          autoprefixer(conf.autoprefixer),
        ]);
      }))
      .pipe(gulp.dest('public/backstage/css/'))
    ;
  },
  js: function () {
    return gulp
      .src('./webpack.config.js')
      .pipe(plumber())
      .pipe(ifelse(conf.env == 'dev', function() {
        return webpack(conf.webpack.dev, compiler);
      }, function() {
        return webpack(conf.webpack.prod, compiler);
      }))
      .pipe(gulp.dest('public/backstage/js/'))
    ;
  },
  fonts: function () {
    return gulp
      .src([
        'node_modules/font-awesome/fonts/*',
        'node_modules/bootstrap/dist/fonts/*',
      ])
      .pipe(plumber())
      .pipe(gulp.dest('public/backstage/fonts/'))
    ;
  },
  plugins: function (cb) {
    cb();
  },
};

exports.buildAdmin = gulp.series(admin.clear, gulp.parallel(admin.css, admin.js, admin.fonts, admin.plugins));
