<?php

use Codger\Generate\Recipe;
use Codger\Generate\Language;
use Codger\Php\Composer;

/**
 * Kick off an entire Sensi project. Pass vendor, dbname, optional user
 * (defaults to dbname) and password to get started!
 *
 * Options: `api`
 */
return function (string $vendor, string $database, string $user, string $password = null, string ...$options) : Recipe {
    // Default assumption is username == dbname
    if (!isset($password)) {
        $password = $user;
        $user = $database;
    }
    $project = basename(dirname(__DIR__, 2));
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
    $recipe->delegate('config', null, $project);
    $recipe->delegate('environment', null, $project, $database, $user, $password);
    $recipe->delegate('index');
    $recipe->delegate('dependencies', null, $vendor, ...$modules);
    $recipe->delegate('routing', null, ...$modules);
    foreach ($modules as $module) {
        $recipe->delegate('sensi/codger-monolyth-module/module', null, $module, null, $vendor);
    }
    $recipe->delegate('sensi/codger-improse-view/view', null, 'global', 'Minimal\View', null, 'Sensi\Minimal');

    // Add Sensi-specific project repos
    $composer = new Composer;
    $composer->addVcsRepository('minimal', 'ssh://git@barabas.sensimedia.nl/home/git/libraries/sensi/minimal');
    $composer->addVcsRepository('fakr', 'ssh://git@barabas.sensimedia.nl/home/git/libraries/sensi/fakr');
    $composer->addVcsRepository('codein', 'ssh://git@barabas.sensimedia.nl/home/git/libraries/sensi/codein');

    // Add Sensi-specific packages
    $composer->addDependency('monolyth/monty');
    $composer->addDependency('ornament/json');
    $composer->addDependency('quibble/'.($vendor == 'pgsql' ? 'postgresql' : 'mysql'));
    $composer->addDependency('sensi/minimal=@dev');
    $composer->addDependency('sensi/fakr=@dev');
    $composer->addDependency('twig/extensions');
    $composer->addDependency("dbmover/$vendor", true);
    $composer->addDependency('gentry/gentry', true);
    $composer->addDependency('gentry/toast', true);
    $composer->addDependency('toast/acceptance', true);
    $composer->addDependency('toast/cache', true);
    $composer->addDependency('toast/unit', true);
    $composer->addDependency('sensi/codein=@dev', true);
    if ($this->askedFor('api')) {
        $composer->addDependency('monomelodies/monki');
    }
    return $recipe;
};

