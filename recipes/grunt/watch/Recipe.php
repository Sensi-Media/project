<?php

use Codger\Generate\Recipe;

return function () : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 3).'/templates'))) extends Recipe {
        protected $template = 'grunt/watch.html.twig';
    };
    $recipe->output('grunt/watch.js');
    return $recipe;
};

