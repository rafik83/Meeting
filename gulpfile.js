var
    gulp   = require('gulp'),
    del    = require('del'),
    concat = require('gulp-concat'),
    sass   = require('gulp-sass'),
    assets = require('elao-assets-gulp');
    gutil  = require('gulp-util');

/************************/
/* Assets Configuration */
/************************/

assets.config({
    header: [
        '/*',
        ' * =============================================================',
        ' * <%= name %>',
        ' *',
        ' * (c) <%= date.getFullYear() %> <%= author.name %> <<%= author.email %>>',
        ' * =============================================================',
        ' */\n\n'
    ].join('\n'),
    autoprefixer: {
        browsers: ['> 1%', 'last 2 versions', 'Firefox ESR', 'Opera 12.1']
    },
    assets: {
        fonts: {
            groups: {
                'bootstrap-sass': {src: 'bootstrap-sass/assets/fonts/bootstrap/**', dest: 'bootstrap'}
            }
        }
    }
});

/*********/
/* Tasks */
/*********/

gulp.task('default', ['js', 'sass', 'css', 'images', 'fonts', 'swf', 'files']);
gulp.task('watch',   ['watch:js', 'watch:sass', 'watch:css', 'watch:images', 'watch:files']);
gulp.task('clean',   function(cb) {
    del(assets.getDest() + '/*', cb);
});
gulp.task('event-sass', function () {
    return gulp.src(gutil.env.srcFile)
        .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
        .pipe(concat(gutil.env.buildFile))
        .pipe(gulp.dest(gutil.env.destination));
});
