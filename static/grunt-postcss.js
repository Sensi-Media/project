
module.exports = {
    site: {
        options: {
            processors: [
                require('autoprefixer')(),
                require('postcss-preset-env')(),
                require('precss')(),
                require('cssnano')({reduceIdents: false})
            ]
        },
        files: {
            'httpdocs/css/required.css': 'tmp/required.css',
            'httpdocs/css/optional.css': 'tmp/optional.css'
        }
    },
    admin: {
        options: {
            processors: [
                require('autoprefixer')(),
                require('postcss-preset-env')(),
                require('precss')(),
                require('cssnano')({reduceIdents: false})
            ]
        },
        files: {
            'httpdocs/admin/admin.css': 'tmp/admin.css'
        }
    }
};

