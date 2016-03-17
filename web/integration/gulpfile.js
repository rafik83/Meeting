var gulp = require('gulp');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var concat = require('gulp-concat');
var imageop = require('gulp-image-optimization');
var clean = require('gulp-clean');

var autoprefixerOptions = {
  browsers: ['last 2 versions', '> 5%', 'Firefox ESR']
};

gulp.task('sass', function () {
  return gulp.src('src/styles/main.scss')
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(autoprefixer(autoprefixerOptions))
    .pipe(concat('style.css'))
    .pipe(gulp.dest('dist'));
});

gulp.task('fonts', function () {
  return gulp.src('src/fonts/**/*')
    .pipe(gulp.dest('dist/fonts'));
});

gulp.task('images', function () {
  return gulp.src('src/images/**/*.*')
    .pipe(imageop({
      optimizationLevel: 5,
      progressive: true,
      interlaced: true
    }))
    .pipe(gulp.dest('dist/images'));
});

gulp.task('build', ['sass', 'images', 'fonts']);

gulp.task('default', ['build'], function () {
  gulp.watch('./src/styles/**/*', ['sass']);
});
