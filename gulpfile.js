const { series, parallel, src, dest,task } = require('gulp');
const rename = require("gulp-rename");
const uglify = require('gulp-uglify');
const clean = require('gulp-clean');
const imagemin = require('gulp-imagemin');
const beautify = require('gulp-beautify');
const sass = require('gulp-sass');
const cleanCSS = require('gulp-clean-css');
const phplint = require('gulp-phplint');
// import {phpMinify, TransformMode} from '@cedx/gulp-php-minify';
const htmlmin = require('gulp-htmlmin');
// sass.compiler = require('sass');
const through2 = require('through2');
const UglifyPHP = require('uglify-php');
let UglifyPHP_opt = {
  "excludes": [
    '$GLOBALS',
     '$_SERVER',
     '$_GET',
     '$_POST',
     '$_FILES',
     '$_REQUEST',
     '$_SESSION',
     '$_ENV',
     '$_COOKIE',
     '$php_errormsg',
     '$HTTP_RAW_POST_DATA',
     '$http_response_header',
     '$argc',
     '$argv',
     '$this',
     '$CSP',
     '$VPORT',
  ],
  "minify": {
     "replace_variables": true,
     "remove_whitespace": true,
     "remove_comments": true,
     "minify_html": true
  },
  // "output": "C:/web/file_min.php" // If it's empty the promise will return the minified source code
}

function cssProcess(cb) {
  return src('gulp-src/sass/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(beautify.css({ indent_size: 2 }))
    .pipe(dest('gulp-build/css/'))
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(rename({ extname: '.min.css' }))
    .pipe(dest('gulp-build/css/'));
}

function phpLint(cb) {
  return src('php-src/**/*.php')
    .pipe(phplint('', { /*opts*/ }))
    .pipe(phplint.reporter(function(file){
      var report = file.phplintReport || {};
      if (report.error) {
        console.error(report.message+' on line '+report.line+' of '+report.filename);
      }
    }));
}

// function phpProcess(cb) {
//   let phpMinify;
//   import('@cedx/gulp-php-minify').then(mod => phpMinify = mod.phpMinify);
//   return src('php-src/public/**/*.php')
//     .pipe(phpMinify)
//     .pipe(dest('./'));
// }

let phpMinify;
task('phpMinify:import', () => import('@cedx/gulp-php-minify').then(mod => phpMinify = mod.phpMinify));
task('phpMinify:run', () => src(['php-src/**/*.php','php-build/**/*.phtml']).pipe(phpMinify()).pipe(dest('php-build')));
task('phpProcess', series('phpMinify:import', 'phpMinify:run'));

function phtmlProcess(cb) {
  return src('php-src/**/*.phtml')
    .pipe(htmlmin({ collapseWhitespace: true, ignoreCustomFragments: [/<\?[\s\S]*?(?:\?>|$)/] }))
    .pipe(dest('php-build'));
}

function uglifyPhp(cb) {
  return src(['php-build/**/*.php','php-build/**/*.phtml'])
    // Instead of using gulp-uglify, you can create an inline plugin
    .pipe(through2.obj(function(file, _, cb) {
      if (file.isBuffer()) {
        // console.log(file.isBuffer());
        UglifyPHP.minify(file.contents.toString(), UglifyPHP_opt).then(function (source) {
          // console.log(file);
          // console.log(source);
          file.contents = Buffer.from(source);
          cb(null, file);
        });
        // const code = uglify.minify(file.contents.toString())
        // file.contents = Buffer.from(code.code)
      }
    }))
    .pipe(dest('php-build'));
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

function publishCss(cb) {
  return src('gulp-build/css/**/*.min.css')
    .pipe(dest('public/dist/css/'));
}

function publishImage(cb) {
  return src('gulp-build/img/**/*')
    .pipe(dest('public/dist/img/'));
}

function publishBowerJs(cb) {
  return src('js-lib/bower/**/*.js')
    .pipe(dest('public/bower/'));
}

function publishBowerCss(cb) {
  return src('js-lib/bower/**/*.css')
    .pipe(dest('public/bower/'));
}

function publishBowerJpg(cb) {
  return src('js-lib/bower/**/*.jpg')
    .pipe(dest('public/bower/'));
}

function publishBowerJpeg(cb) {
  return src('js-lib/bower/**/*.jpeg')
    .pipe(dest('public/bower/'));
}

function publishBowerPng(cb) {
  return src('js-lib/bower/**/*.png')
    .pipe(dest('public/bower/'));
}

function publishPhp(cb) {
  return src('php-build/**/*.php')
    .pipe(dest('./'));
}

function publishPhtml(cb) {
  return src('php-build/**/*.phtml')
    .pipe(dest('./'));
}

exports.build = series(
  parallel(
    imageProcess,
    cssProcess,
    jsProcess
  )
);

exports.php = series(
  phpLint,
  phtmlProcess,
  'phpProcess',
  uglifyPhp,
  parallel(
    publishPhp,
    publishPhtml
  ),
);

exports.bower = series(
  parallel(
    publishBowerJs,
    publishBowerCss,
    publishBowerJpg,
    publishBowerJpeg,
    publishBowerPng
  )
);

exports.css = series(
  cssProcess,
  publishCss
);

exports.js = series(
    jsProcess,
    publishJs
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
    publishCss
  )
);