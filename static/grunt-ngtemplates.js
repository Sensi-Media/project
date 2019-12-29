
module.exports = {
    siteTemplates: {
        cwd: 'src',
        src: ['**/*.html', '!**/*.admin.html'],
        dest: 'tmp/templates.js',
        options: {
            htmlmin: {
                collapseBooleanAttributes: true,
                collapseWhitespace: false,
                removeAttributeQuotes: true,
                removeComments: true,
                removeEmptyAttributes: true,
                removeRedundantAttributes: true,
                removeScriptTypeAttributes: true,
                removeStyleLinkTypeAttributes: true
            },
            standalone: true
        }
    },
    adminTemplates: {
        options: {
            htmlmin: {
                collapseBooleanAttributes: true,
                collapseWhitespace: false,
                removeAttributeQuotes: true,
                removeComments: true,
                removeEmptyAttributes: true,
                removeRedundantAttributes: true,
                removeScriptTypeAttributes: true,
                removeStyleLinkTypeAttributes: true
            },
            standalone: true
        },
        cwd: 'src',
        src: ['**/*.admin.html'],
        dest: 'tmp/templates.admin.js'
    }
};

