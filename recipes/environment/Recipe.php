<?php

use Codger\Generate\Recipe;

return function (string $project, string $database, string $user, string $pass) : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {
        protected $template = 'environment.html.twig';
    };
    $recipe->output('Envy.json');
    $recipe->set('project', $project);
    $recipe->set('database', $database);
    $recipe->set('user', $user);
    $recipe->set('pass', $pass);
    // This can easily be changed.
    $recipe->set('host', "$project.nl");
    return $recipe;
};

