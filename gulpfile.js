const { series, parallel, src, dest } = require('gulp');
const rename = require("gulp-rename");
const uglify = require('gulp-uglify');
const clean = require('gulp-clean');
const imagemin = require('gulp-imagemin');

function cssProcess(cb) {
  // body omitted
  cb();
}

// function cssMinify(cb) {
//   // body omitted
//   cb();
// }

function imageProcess(cb) {
  return src('gulp-src/img/**/*')
    .pipe(imagemin())
    .pipe(dest('gulp-build/img'));
}

function jsProcess(cb) {
  return src('gulp-src/js/**/*.js')
    // .pipe(babel())
    // .pipe(clean({force: true}))
    .pipe(beautify.js({ indent_size: 2 }))
    .pipe(dest('gulp-build/js/'))
    .pipe(uglify())
    .pipe(rename({ extname: '.min.js' }))
    .pipe(dest('gulp-build/js/'));
}

// function jsBundle(cb) {
//   // body omitted
//   cb();
// }

// function jsMinify(cb) {
//   // body omitted
//   cb();
// }

function publishJs(cb) {
  return src('gulp-build/js/**/*.min.js')
    // .pipe(clean({force: true}))
    .pipe(dest('public/dist/js/'));
}

function publishImage(cb) {
  return src('gulp-build/img/**/*')
    .pipe(dest('public/dist/img/'));
}

exports.build = series(
  parallel(
    imageProcess,
    cssProcess,
    jsProcess
  )
);

exports.default = series(
  parallel(
    imageProcess,
    cssProcess,
    jsProcess
  ),
  parallel(
    publishImage,
    publishJs,
    jsProcess
  )
);