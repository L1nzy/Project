export const server = (done)=>{
  app.plugins.browsersync.init({
    proxy: 'http://drupal.docker.localhost:8000/',
    notify:false
  })
}
