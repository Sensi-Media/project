<?php

use Codger\Generate\Recipe;
use Codger\Generate\Language;
use Codger\Php\Composer;
use Codger\Javascript\Npm;

/**
 * Kick off an entire Sensi project. Pass vendor, dbname, optional user
 * (defaults to dbname) and password to get started!
 *
 * Options: `api`
 */
return function (string $vendor, string $database, string $user, string $password = null, string ...$options) : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {};
    if (!file_exists(getcwd().'/composer.json')) {
        $recipe->error("Please run `composer init` first.\n");
        return $recipe;
    }
    if (!file_exists(getcwd().'/package.json')) {
        $recipe->error("Please run `npm init` first.\n");
        return $recipe;
    }
    // Default assumption is username == dbname
    if (!isset($password)) {
        $password = $user;
        $user = $database;
    }
    $project = basename(getcwd());
    $modules = [];
    $adapter = new PDO("$vendor:dbname=$database", $user, $password);
    $exists = $adapter->prepare(
        "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
            WHERE ((TABLE_CATALOG = ? AND TABLE_SCHEMA = 'public') OR TABLE_SCHEMA = ?)
                AND TABLE_TYPE = 'BASE TABLE'");
    $exists->execute([$database, $database]);
    while (false !== ($table = $exists->fetchColumn())) {
        if ($table == 'cesession_session') {
            continue;
        }
        $modules[] = Language::convert($table, Language::TYPE_NAMESPACE);
    }
    asort($modules);
    $recipe->delegate('sensi/codger-sensi-project@config', $project);
    $recipe->delegate('sensi/codger-sensi-project@environment', $project, $database, $user, $password);
    $recipe->delegate('sensi/codger-sensi-project@index');
    $recipe->delegate('sensi/codger-sensi-project@dependencies', $vendor, $project, ...$modules);
    $recipe->delegate('sensi/codger-sensi-project@routing', ...$modules);
    $recipe->delegate('sensi/codger-sensi-project@sass', ...$modules);
    foreach ($modules as $module) {
        $recipe->delegate('sensi/codger-monolyth-module@module', $module, null, $vendor, $database, $user, $password);
    }
    $recipe->delegate('sensi/codger-improse-view@base', $project, ...$modules);
    $recipe->delegate('sensi/codger-improse-view@view', 'Home', '\View', 'Home/template.html.twig');

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
    $composer->addDependency('gentry/gentry', true);
    $composer->addDependency('gentry/toast', true);
    $composer->addDependency('toast/acceptance', true);
    $composer->addDependency('toast/cache', true);
    $composer->addDependency('toast/unit', true);
    $composer->addDependency('sensi/codein=@dev', true);
    if ($this->askedFor('api')) {
        $composer->addDependency('monomelodies/monki');
        $composer->addVcsRepository('api', 'ssh://git@barabas.sensimedia.nl/home/git/libraries/sensi/api');
        $composer->addDependency('sensi/api=@dev');
    }

    // Add NPM packages
    $package = new Npm;
    foreach ([
        "browserify",
        "grunt",
        "grunt-browserify",
        "grunt-contrib-sass",
        "grunt-contrib-uglify",
        "grunt-contrib-watch",
        "@babel/core",
        "babel-plugin-transform-async-to-generator",
        "babel-plugin-transform-runtime",
        "babel-polyfill",
        "babel-preset-env",
        "babelify",
        "grunt-angular-gettext",
        "grunt-angular-templates",
        "grunt-babel",
        "grunt-browserify",
        "grunt-sass",
        "grunt-contrib-uglify",
        "grunt-contrib-watch",
        "grunt-shell",
        "grunt-twig-gettext",
        "jasmine-core",
        "karma",
        "karma-browserify",
        "karma-jasmine",
        "karma-phantomjs-launcher",
        "load-grunt-config",
        "load-grunt-tasks",
        "monad-cms",
        "monad-crud",
        "monad-navigation",
        "monad-theme-default",
        "time-grunt",
    ] as $name) {
        $package->addDependency($name, true);
    }
    $recipe->delegate('sensi/codger-sensi-project@grunt');
    $recipe->delegate('sensi/codger-sensi-project@grunt/aliases');
    $recipe->delegate('sensi/codger-sensi-project@grunt/browserify');
    $recipe->delegate('sensi/codger-sensi-project@grunt/sass');
    return $recipe;
};

