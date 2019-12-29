
module.exports = {
    watch_site: {
        tasks: [
            'watch:versioning',
            'watch:templates',
            'watch:sass_site',
            'watch:postcss',
{% if i18n %}
            'watch:gettextcompile',
{% endif %}
            'watch:livereload'
        ],
        options: { logConcurrentOutput: true, limit: 6 }
    },
    watch_admin: {
        tasks: [
            'watch:sass_admin',
            'watch:postcss',
{% if i18n %}
            'watch:gettextcompile',
{% endif %}
            'watch:templates'
        ],
        options: { logConcurrentOutput: true, limit: 5 }
    }
};
    
