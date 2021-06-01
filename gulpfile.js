/**
 * Variables
 */

var gulp = require('gulp'),
	sass = require('gulp-sass'),
	postcss = require('gulp-postcss'),
	autoprefixer = require('autoprefixer'),
	cssnano = require('cssnano'),
	sourcemaps = require('gulp-sourcemaps'),
	browserify = require('gulp-browserify'),
	plumber = require('gulp-plumber'),
	jshint = require('gulp-jshint'),
	concat = require('gulp-concat'),
	babel = require('gulp-babel'),
	livereload = require('gulp-livereload'),
	uglify = require('gulp-uglify');

var theme = 'igniterate';

var path = {
	src: 'themes/' + theme + '/src/',
	dest: 'themes/' + theme + '/assets/'
};

/**
 * Tasks
 */
function style() {
	return gulp
		.src(path.src + 'scss/*.scss')
		.pipe(sass())
		.pipe(sourcemaps.init())
		.on('error', sass.logError)
		.pipe(postcss([ autoprefixer(), cssnano() ]))
		.pipe(sourcemaps.write())
		.pipe(gulp.dest(path.dest))
		.pipe(livereload());
}

function scripts() {
	return (
		gulp
			.src([ path.src + 'js/script.js' ])
			.pipe(plumber())
			.pipe(jshint())
			.pipe(concat('script.js'))
			.pipe(babel({ presets: [ '@babel/preset-env' ] }))
			.pipe(browserify())
			// .pipe(uglify())
			.pipe(gulp.dest(path.dest))
			.pipe(livereload())
	);
}

function reload() {
	return(
		gulp
			.src('index.php')
			.pipe(livereload())
	);
}

function watch() {
	livereload.listen();
	gulp.watch(path.src + 'js/**/*.js', scripts);
	gulp.watch(path.src + 'scss/**/*', style);
	gulp.watch('**/*.html', reload);
	gulp.watch('**/*.php', reload);
}

exports.scripts = scripts;
exports.style = style;
exports.watch = watch;

exports.default = watch;