
const sass = require('node-sass');

module.exports = {
    site: {
        options: {
            implementation: sass,
            style: 'compressed',
            sourcemap: 'none'
        },
        files: {
            'tmp/required.css': 'src/required.scss',
            'tmp/optional.css': 'src/optional.scss'
        }
    },
    admin: {
        options: {
            implementation: sass,
            style: 'compressed',
            sourcemap: 'none',
            loadPath: ['node_modules/monad-cms/node_modules/bootstrap-sass/assets/stylesheets']
        },
        files: {'tmp/admin.css': 'src/admin.scss'}
    }
};

