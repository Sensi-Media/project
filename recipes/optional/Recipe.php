<?php

use Codger\Generate\Recipe;
use Codger\Generate\Language;

return function (...$modules) : Recipe {
    $twig = new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'));
    $recipe = new class($twig) extends Recipe {
        protected $template = 'sass.html.twig';
    };
    array_walk($modules, function (&$module) {
        $module = Language::convert($module, Language::TYPE_PATH);
    });
    $recipe->set('modules', $modules);
    $recipe->output('src/optional.scss');
    return $recipe;
};

