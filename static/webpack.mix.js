const mix = require('laravel-mix');
const fs = require('fs');
const settings = JSON.parse(fs.readFileSync('./watch.json', 'utf8'));
const env = process.env.NODE_ENV || 'development';

mix
    .sass('src/required.scss', 'httpdocs/css/required.css').options({processCssUrls: false})
    .sass('src/optional.scss', 'httpdocs/css/optional.css').options({processCssUrls: false})
    .sass('src/admin.scss', 'httpdocs/admin/admin.css').options({processCssUrls: false})
    .sass('src/Invoice/pdf.scss', 'httpdocs/css/pdf.css').options({processCssUrls: false})
    .js('src/index.js', 'httpdocs/js/bundle.js')
    .js('src/admin.js', 'httpdocs/admin/bundle.js')
    .browserSync({
        proxy: settings.proxy,
        https: settings.https,
        files: ['httpdocs/css/*.css', 'httpdocs/js/*.js', 'src/**/*.php', 'src/**/*.twig'],
        open: false
    })
    ;

