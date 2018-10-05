<?php

use Gentry\Gentry\Wrapper;

putenv("CODGER_DRY=1");
$recipe = include 'recipes/grunt/aliases/Recipe.php';

/** Grunt recipe */
return function () use ($recipe) : Generator {
    /** generates valid grunt settings */
    yield function () use ($recipe) {
        $result = $recipe('Foo')->render();
        assert(strpos($result, <<<EOT
module.exports = {
    'default': [
        'prod'
    ],
    default_site: [
        'ngtemplates',
        'nggettext_compile',
        'browserify:site',
        'sass:site',
        'postcss:site',
        'copy:versions',
        'shell:versions'
    ],
    default_admin: [
        'ngtemplates',
        'nggettext_compile',
        'browserify:admin',
        'sass:admin',
        'postcss:admin'
    ],
    translate: [
        'twig_gettext',
        'nggettext_extract',
        'shell:php',
        'shell:gettext',
        'nggettext_compile'
    ],
    dev_site: [
        'default_site',
        'concurrent:watch_site'
    ],
    prod_site: [
        'default_site',
        'uglify:site'
    ],
    dev_admin: [
        'default_admin',
        'concurrent:watch_admin'
    ],
    prod_admin: [
        'default_admin',
        'uglify:admin'
    ]
};
EOT
        ) !== false);
    };
};

