<?php

use Codger\Generate\Recipe;

return function () : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 3).'/templates'))) extends Recipe {
        protected $template = 'grunt/concurrent.html.twig';
    };
    $recipe->output('grunt/concurrent.js');
    return $recipe;
};

