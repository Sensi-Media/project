<?php

use Codger\Generate\Recipe;

return function (int $api, ...$modules) : Recipe {
    $twig = new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'));
    $twig->addFilter('normalize', new Twig_SimpleFilter(function (string $module) : string {
        return strtolower(str_replace('\\', '-', $module));
    }));
    $twig->addFilter('fordb', new Twig_SimpleFilter(function (string $module) : string {
        return strtolower(str_replace('-', '_', $module));
    }));
    $recipe = new class($twig) extends Recipe {
        protected $template = 'routing.html.twig';
    };
    $recipe->set('modules', $modules);
    $recipe->set('api', $api);
    $recipe->output('src/routing.php');
    return $recipe;
};

