<?php

use Gentry\Gentry\Wrapper;

putenv("CODGER_DRY=1");
/** Routing recipe */
return function () : Generator {
    $recipe = include 'recipes/routing/Recipe.php';
    $bootstrap = new Codger\Generate\Bootstrap('routing');
    /** generates a valid router */
    yield function () use ($recipe, $bootstrap) {
        $result = $recipe->call($bootstrap, 'User')->render();
        assert(strpos($result, <<<EOT
use Monolyth\Reroute\Router;
use Monolyth\Disclosure\Container;

\$container = new Container;
\$env = \$container->get('env');
\$router = new Router(\$env->host);
\$router->when('/', null, function (Router \$router) : void {
    \$router->when('/', 'home')->get(Home\View::class);
    \$router->when('/user/', null, function (Router \$router) : void {
        \$router->when("/(?'id'\d+)/", 'user-details')
            ->get(function (int \$id) : View {
                return new User\Detail\View(\$id);
            });
        \$router->when('/', 'user-list')
            ->get(User\View::class);
    });
});
return \$router;
EOT
        ) !== false);
    };
};

