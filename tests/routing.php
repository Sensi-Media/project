<?php

use Gentry\Gentry\Wrapper;

putenv("CODGER_DRY=1");
$recipe = include 'recipes/routing/Recipe.php';

/** Routing recipe */
return function () use ($recipe) : Generator {
    /** generates a valid router */
    yield function () use ($recipe) {
        $result = $recipe(1)->render();
        assert(strpos($result, <<<EOT
use Monolyth\Reroute\Router;
use Monolyth\Disclosure\Container;
use Monomelodies\Monki\{ Api, Handler\Crud };

\$container = new Container;
\$env = \$container->get('env');
\$router = new Router(\$env->host);
\$user = \$container->get('user');
\$router->when('/', null, function (\$router) {
    \$router->when('/')->get(Home::class);
    \$router->when('/api/', null, function (\$router) {
        \$monki = new Api(\$router);
    });
});
return \$router;
EOT
        ) !== false);
    };
};

