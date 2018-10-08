<?php

use Codger\Generate\Recipe;

return function () : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {
        protected $template = 'versions.html.twig';
    };
    $recipe->output('bin/versions');
    exec('chmod a+x bin/versions');
    return $recipe;
};

