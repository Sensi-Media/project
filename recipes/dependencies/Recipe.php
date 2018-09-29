<?php

use Codger\Generate\Recipe;
use Codger\Generate\Language;

return function (string ...$repositories) : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {
        protected $template = 'dependencies.html.twig';
    };
    $recipe->output('src/dependencies.php');
    array_walk($repositories, function (&$repository) {
        $repository = [
            'variable' => Language::convert($repository, Language::TYPE_VARIABLE),
            'namespace' => Language::convert($repository, Language::TYPE_NAMESPACE),
        ];
    });
    $recipe->set('repositories', $repositories);
    return $recipe;
};

