<?php

use Gentry\Gentry\Wrapper;

putenv("CODGER_DRY=1");

/** Dependencies recipe */
return function () : Generator {
    $recipe = include 'recipes/dependencies/Recipe.php';
    /** generates valid dependencies */
    yield function () use ($recipe) {
        $result = $recipe('pgsql', 'Foo', 'Bar')->render();
        assert(strpos($result, <<<EOT
use Monolyth\Disclosure\Container;
use Monolyth\Envy\Environment;
use Monolyth\Cesession\{ Session, Handler };
use Quibble\Postgresql\Adapter;
use Quibble\Query\Buildable;

\$container = new Container;

// Default routing
\$container->register(function (&\$router) : void {
    \$router = require __DIR__.'/routing.php';
});
\$container->register(function (&\$env) : void {
    \$env = new Environment(dirname(__DIR__).'/Envy.json', function (\$env) : array {
        \$envs = ['ft'];
        if (isset(\$_SERVER['HTTP_HOST'])) {
            \$envs[] = 'web';
            if (preg_match(
                '@\.dev\.sensimedia\.nl\$@',
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

\$env = \$container->get('env');
// Default Twig environment
\$container->register(function (&\$twig) use (\$container, \$env) : void {
    \$router = \$container->get('router');
    \$loader = new Twig_Loader_Filesystem(__DIR__);
    \$e = \$container->get('env');
    \$twig = new Twig_Environment(\$loader, [
        'cache' => dirname(__DIR__).'/.twig-cache/'.get_current_user(),
        'auto_reload' => \$e->dev,
        'debug' => \$e->dev,
    ]);
    \$url = function (\$name, array \$args = []) use (\$router, \$e) {
        \$args['language'] = \$args['language'] ?? \$e->language;
        try {
            return \$router->generate(\$name, \$args)
                .(isset(\$_GET['if']) ? '?if' : '');
        } catch (DomainException \$e) {
            return \$e->getMessage(); 
        }
    };
    \$twig->addFunction(new Twig_SimpleFunction('url', \$url));
    \$twig->addFunction(new Twig_SimpleFunction('version', function (\$file) use (\$env) {
        if (!\$env->prod) {
            return \$file;
        }
        static \$versions;
        if (!isset(\$versions)) {
            \$versions = json_decode(file_get_contents(dirname(__DIR__).'/Versions.json'), true);
        }
        \$file = preg_replace('@^/@', '', \$file);
        return preg_replace('@\.(css|js)\$@', ".{\$versions[\$file]}.\\\\1", "/\$file");
    }));
});
\$container->register(function (&\$adapter) use (\$container) : void {
    \$env = \$container->get('env');
    \$port = \$env->prod ? ";port=6432" : '';
    \$adapter = new class(
        "dbname={\$env->db['name']}\$port",
        \$env->db['user'],
        \$env->db['pass'],
        [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => true,
        ]
    ) extends Adapter {
        use Buildable;
    };
});
if (!(\$env->cli || \$env->test)) {
    \$session = new Session('Foo');
    \$session->registerHandler(new Handler\Pdo(\$container->get('adapter')));
    session_start();
}

\$container->register(function (&\$barRepository) : void {
    \$barRepository = new Bar\Repository;
});
EOT
        ) !== false);
    };
};

