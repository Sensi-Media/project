<?php

use Codger\Generate\Recipe;

return function (string $project) : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {
        protected $template = 'config.html.twig';
    };
    $recipe->output('ServerConfig.json');
    return $recipe;
};

