<?php

use Gentry\Gentry\Wrapper;

putenv("CODGER_DRY=1");
$recipe = include 'recipes/environment/Recipe.php';

/** Environment recipe */
return function () use ($recipe) : Generator {
    /** generates a valid environment */
    yield function () use ($recipe) {
        $result = $recipe('foo', 'codger_test', 'codger_test', 'blarps')->render();
        assert(strpos($result, <<<EOT
{
    "web": {
        "db": {
            "name": "codger_test",
            "user": "codger_test",
            "pass": "blarps"
        }
    },
    "cli": {
        "db": {
            "name": "codger_test",
            "user": "codger_test",
            "pass": "blarps"
        }
    },
    "test": {
        "db": {
            "name": "<% current_user %>_foo"
        }
    },
    "prod": {
        "host": "foo.nl"
    },
    "dev": {
        "email": "<% user %>@sensimedia.nl",
        "host": "http://<% user %>.foo.dev.sensimedia.nl",
        "lrport": 8304
    }
}
EOT
        ) !== false);
    };
};

