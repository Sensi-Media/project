<?php

use Codger\Generate\Recipe;

return function () : Repice {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 3).'/templates'))) extends Recipe {
        protected $template = 'grunt/aliases.html.twig';
    };
    $recipe->output('grunt/aliases.js');
    return $recipe;
};

