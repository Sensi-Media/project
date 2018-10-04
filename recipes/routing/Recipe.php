<?php

use Codger\Generate\Recipe;

return function (int $api, ...$modules) : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {
        protected $template = 'routing.html.twig';
    };
    $recipe->set('modules', $modules);
    $recipe->set('api', $api);
    $recipe->output('src/routing.php');
    return $recipe;
};

