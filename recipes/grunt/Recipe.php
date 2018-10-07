<?php

use Codger\Generate\Recipe;

return function () : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {
        protected $template = 'grunt.html.twig';
    };
    $recipe->output('Gruntfile.js');
    return $recipe;
};
