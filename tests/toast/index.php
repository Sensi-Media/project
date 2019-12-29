<?php

use Gentry\Gentry\Wrapper;

putenv("CODGER_DRY=1");

/** Index recipe */
return function () : Generator {
    $recipe = include 'recipes/index/Recipe.php';
    /** generates a valid index */
    yield function () use ($recipe) {
        $result = $recipe('Foo')->render();
        assert(strpos($result, <<<EOT
use Improse\Json;
use Monolyth\Frontal\Controller;
use Monolyth\Frontal\Exception;
use User\Model;
use Monolyth\Cesession\Session;
use Monolyth\Cesession\Handler;
use Zend\Diactoros\Response\SapiEmitter;
use Monolyth\Disclosure\Container;
use Monolyth\Plumber\Normalize;

\$base = dirname(__DIR__);
\$autoloader = require_once "\$base/vendor/autoload.php";

Normalize::requestVariables();

require_once "\$base/src/dependencies.php";

\$router = \$container->get('router');
\$env = \$container->get('env');

\$debug = function (Throwable \$e) use (\$env) {
    if (!(\$e instanceof Exception) || \$e->getCode() != 404) {
        if (\$env->prod) {
            mail('marijn@sensimedia.nl', 'debug', \$e->getMessage()."\\n".\$e->getFile()."\\n".\$e->getLine()."\\n".print_r(\$_POST, true)."\\n".print_r(\$_SERVER, true));
        } else {
            var_dump(\$e->getMessage(), \$e->getFile(), \$e->getLine(), print_r(\$_POST, true), print_r(\$_SERVER, true));
        }
    }
};

try {
    \$controller = new Controller;
    \$controller->pipe(\$router);
    \$controller->run();
} catch (Throwable \$e) {
    \$debug(\$e);
    \$e = new \Exception(\$e->getMessage(), \$e->getCode(), \$e);
    \$emitter = new SapiEmitter;
    \$emitter->emit((new Error\View(\$e))->__invoke());
}
EOT
        ) !== false);
    };
};

