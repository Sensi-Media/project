<?php

use Codger\Generate\Recipe;

return function (string $vendor, string $database, string $user, string $password = null) : Recipe {
    // Default assumption is username == dbname
    if (!isset($password)) {
        $password = $user;
        $user = $database;
    }
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {};
    $recipe->delegate('environment', dirname(__DIR__, 2));
    $recipe->delegate('index', dirname(__DIR__, 2));
    $modules = [];
    $exists = $adapter->prepare(
        "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
            WHERE ((TABLE_CATALOG = ? AND TABLE_SCHEMA = 'public') OR TABLE_SCHEMA = ?)
                AND TABLE_TYPE = 'BASE TABLE'");
    $exists->execute([]);
    $recipe->delegate('dependencies', dirname(__DIR__, 2));
    $recipe->delegate('routing', dirname(__DIR__, 2));
    return $recipe;
};

