import datrtSass from 'sass';
import gulpSass from 'gulp-sass'

const sass = gulpSass(datrtSass)

export const scss = ()=> {
  return app.gulp.src(app.path.src.scss,{sourcemaps:true})
    .pipe(sass({
      outputStyle:'expanded'
    }))
    .pipe(app.gulp.dest(app.path.build.css))
    .pipe(app.plugins.reload())
    .pipe(app.plugins.browsersync.stream())
}
