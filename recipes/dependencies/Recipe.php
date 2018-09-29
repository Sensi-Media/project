<?php

use Codger\Generate\Recipe;

return function (string ...$dependencies) : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {
        protected $template = 'dependencies.html.twig';
    };
    $recipe->output('src/dependencies.php');
    return $recipe;
};

