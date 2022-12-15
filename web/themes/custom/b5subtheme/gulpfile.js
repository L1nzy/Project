import gulp from "gulp";
import {path} from "./gulp/config/path.js";
import {scss} from './gulp/task/scss.js'
import {server} from './gulp/task/server.js'
import {plugins} from './gulp/config/plugins.js';
import reload from "gulp-livereload";

global.app = {
  path,
  gulp,
  plugins
}

function watcher () {
  reload.listen();
  gulp.watch(path.watch.scss,scss)
}

const mainTask = gulp.parallel(scss)
const dev = gulp.series(mainTask,gulp.parallel(watcher,server))
gulp.task('default',dev)
