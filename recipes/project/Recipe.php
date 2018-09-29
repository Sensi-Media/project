<?php

use Codger\Generate\Recipe;
use Codger\Generate\Language;

/**
 * Kick off an entire Sensi project. Pass vendor, dbname, optional user
 * (defaults to dbname) and password to get started!
 */
return function (string $vendor, string $database, string $user, string $password = null) : Recipe {
    // Default assumption is username == dbname
    if (!isset($password)) {
        $password = $user;
        $user = $database;
    }
    $modules = [];
    $adapter = new PDO("$vendor:dbname=$database", $user, $password);
    $exists = $adapter->prepare(
        "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
            WHERE ((TABLE_CATALOG = ? AND TABLE_SCHEMA = 'public') OR TABLE_SCHEMA = ?)
                AND TABLE_TYPE = 'BASE TABLE'");
    $exists->execute([$database, $database]);
    while (false !== ($table = $exists->fetchColumn())) {
        $modules[] = Language::convert($table, Language::TYPE_NAMESPACE);
    }
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {};
    $recipe->delegate('environment', dirname(__DIR__, 2));
    $recipe->delegate('index', dirname(__DIR__, 2));
    $recipe->delegate('dependencies', dirname(__DIR__, 2));
    $recipe->delegate('routing', dirname(__DIR__, 2));
    return $recipe;
};

