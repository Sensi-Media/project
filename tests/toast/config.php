<?php

use Gentry\Gentry\Wrapper;

putenv("CODGER_DRY=1");

/** Config recipe */
return function () : Generator {
    $recipe = include 'recipes/config/Recipe.php';
    /** generates a valid config */
    yield function () use ($recipe) {
        $result = $recipe('Foo')->render();
        assert(strpos($result, <<<EOT
{
    "Foo": "httpdocs"
}
EOT
        ) !== false);
    };
};

