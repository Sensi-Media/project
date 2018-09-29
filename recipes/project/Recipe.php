<?php

use Codger\Generate\Recipe;

return function () : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {};
    $recipe->delegate('environment', dirname(__DIR__, 2));
    $recipe->delegate('index', dirname(__DIR__, 2));
    $recipe->delegate('dependencies', dirname(__DIR__, 2));
    $recipe->delegate('routing', dirname(__DIR__, 2));
    return $recipe;
};

