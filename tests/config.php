<?php

use Gentry\Gentry\Wrapper;

putenv("CODGER_DRY=1");
$recipe = include 'recipes/config/Recipe.php';

/** Config recipe */
return function () use ($recipe) : Generator {
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

