<?php

use Codger\Generate\Recipe;
use Codger\Generate\Language;

return function (string $vendor, string $session, string ...$repositories) : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {
        protected $template = 'dependencies.html.twig';
    };
    $recipe->output('src/dependencies.php');
    switch ($vendor) {
        case 'mysql': $recipe->set('vendor', 'Mysql'); break;
        case 'pgsql': $recipe->set('vendor', 'Postgresql'); break;
    }
    array_walk($repositories, function (&$repository) {
        $repository = [
            'variable' => Language::convert($repository, Language::TYPE_VARIABLE),
            'namespace' => Language::convert($repository, Language::TYPE_NAMESPACE),
        ];
    });
    $recipe->set('repositories', $repositories);
    $recipe->set('session', $session);
    return $recipe;
};

