/**
 * @file
 * This file contains Gulp configuration for CSS compilation of
 * Shoreditch-only styling for this extension.
 *
 * Tasks
 * - default: Runs "sass" task
 * - sass: Compiles a CSS file for Shoreditch-only styling
 * - watch: Watches for *.scss files changes and runs "sass" task
 *
 * @NOTE Shoreditch must be installed on the site the extension is being developed at
 */
'use strict';

const gulp = require('gulp');
const sass = require('gulp-sass');
const civicrmScssRoot = require('civicrm-scssroot')();
const stripCssComments = require('gulp-strip-css-comments');
const cssmin = require('gulp-cssmin');
const rename = require('gulp-rename');

/**
 * Compiles a CSS file for Shoreditch-only styling
 */
gulp.task('sass', () => {
  return civicrmScssRoot.update().then(() => {
    return gulp.src('scss/shoreditch-only.scss')
      .pipe(sass({
        outputStyle: 'compressed',
        includePaths: civicrmScssRoot.getPath(),
        precision: 10
      }).on('error', sass.logError))
      .pipe(stripCssComments({ preserve: false }))
      .pipe(cssmin())
      .pipe(rename({ suffix: '.min' }))
      .pipe(gulp.dest('css/'));
  });
});

/**
 * Watch task
 */
gulp.task('watch', () => {
  gulp.watch('scss/**/*.scss', gulp.series(['sass']));
});

/**
 * Default task
 */
gulp.task('default', gulp.parallel(['sass']));
