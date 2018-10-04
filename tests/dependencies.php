<?php

use Gentry\Gentry\Wrapper;

putenv("CODGER_DRY=1");
$recipe = include 'recipes/dependencies/Recipe.php';

/** Dependencies recipe */
return function () use ($recipe) : Generator {
    /** generates valid dependencies */
    yield function () use ($recipe) {
        $result = $recipe('Foo')->render();
        assert(strpos($result, <<<EOT
use Monolyth\Disclosure\Container;
use Monolyth\Envy\Environment;
use Monolyth\Cesession\{ Session, Handler };
use Quibble\\\Adapter;
use Quibble\Query\Buildable;

\$container = new Container;

// Default routing
\$container->register(function (&\$router) {
    \$router = require __DIR__.'/routing.php';
});
\$container->register(function (&\$env) {
    \$env = new Environment(dirname(__DIR__).'/Envy.json', function (\$env) {
        \$envs = ['ft'];
        if (isset(\$_SERVER['HTTP_HOST'])) {
            \$envs[] = 'web';
            if (preg_match(
                '@\.dev\.sensimedia\.nl$@',
                \$_SERVER['HTTP_HOST']
            )) {
                \$envs[] = 'dev';
            } else {
                \$envs[] = 'prod';
            }
        } else {
            if (getenv('TOAST')) {
                \$envs[] = 'test';
            } else {
                \$envs[] = 'cli';
            }
            if (in_array(get_current_user(), ['sensi'])) {
                \$envs[] = 'prod';
            } else {
                \$envs[] = 'dev';
            }
        }
        if (in_array('dev', \$envs)) {
            \$usr = get_current_user();
            if (\$usr == 'monomelodies') {
                \$usr = 'marijn';
            }
            \$env->current_user = get_current_user();
            \$env->user = \$usr;
        }
        return \$envs;
    });
});
EOT
        ) !== false);
    };
};

