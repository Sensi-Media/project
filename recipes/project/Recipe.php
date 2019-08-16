<?php

use Codger\Generate\Recipe;
use Codger\Generate\Language;
use Codger\Php\Composer;
use Codger\Javascript\Npm;

/**
 * Kick off an entire Sensi project. Database credentials are taken from
 * `Envy.json`. Also, make sure to run `composer init` and `npm init` first.
 *
 * Options: `api` to add an api.
 */
return function (string ...$options) : Recipe {
    $recipe = new class(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'))) extends Recipe {};
    if (!file_exists(getcwd().'/composer.json')) {
        $recipe->error("Please run `composer init` first.\n");
        return $recipe;
    }
    if (!file_exists(getcwd().'/package.json')) {
        $recipe->error("Please run `npm init` first.\n");
        return $recipe;
    }
    if (!file_exists(getcwd().'/Envy.json')) {
        $recipe->error("Please setup `Envy.json` first.\n");
        return $recipe;
    }
    $config = json_decode(file_get_contents(getcwd().'/Envy.json'));
    foreach (['web', 'cli'] as $key) {
        if (isset($config->$key->db)) {
            $database = $config->$key->db->name;
            $user = $config->$key->db->user;
            $password = $config->$key->db->pass;
            break;
        }
    }
    $project = basename(getcwd());
    $modules = [];
    $vendor = 'pgsql';
    try {
        $adapter = new PDO("$vendor:dbname=$database", $user, $password);
    } catch (PDOException $e) {
        $vender = 'mysql';
        $adapter = new PDO("$vendor:dbname=$database", $user, $password);
    }
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
    $recipe->delegate('sensi/codger-sensi-project@index');
    $recipe->delegate('sensi/codger-sensi-project@dependencies', $vendor, $project, ...$modules);
    $recipe->delegate('sensi/codger-sensi-project@routing', ...$modules);
    $recipe->delegate('sensi/codger-sensi-project@required', 'Home');
    $recipe->delegate('sensi/codger-sensi-project@optional', ...$modules);
    foreach ($modules as $module) {
        $recipe->delegate('sensi/codger-monolyth-module@module', $module, null, $vendor, $database, $user, $password);
    }
    $recipe->delegate('sensi/codger-improse-view@base', $project, ...$modules);
    $recipe->delegate('sensi/codger-improse-view@view', 'Home', '\View', 'Home/template.html.twig');
    $recipe->delegate('sensi/codger-monolyth-module@sass', 'Home');

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
        "@babel/plugin-transform-async-to-generator",
        "@babel/plugin-transform-runtime",
        "@babel/polyfill",
        "@babel/preset-env",
        "babelify",
        "grunt-angular-templates",
        "grunt-babel",
        "grunt-browserify",
        "grunt-sass",
        "grunt-concurrent",
        "grunt-contrib-copy",
        "grunt-contrib-uglify",
        "grunt-contrib-watch",
        "grunt-shell",
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
        "node-sass",
        "time-grunt",
        "watchify",
        "grunt-postcss",
        "autoprefixer",
        "postcss-preset-env",
        "precss",
        "cssnano",
        "md5"
    ] as $name) {
        $package->addDependency($name, true);
    }
    $recipe->delegate('sensi/codger-sensi-project@grunt');
    $recipe->delegate('sensi/codger-sensi-project@grunt/aliases');
    $recipe->delegate('sensi/codger-sensi-project@grunt/ngtemplates');
    $recipe->delegate('sensi/codger-sensi-project@grunt/browserify');
    $recipe->delegate('sensi/codger-sensi-project@grunt/sass');
    $recipe->delegate('sensi/codger-sensi-project@grunt/postcss');
    $recipe->delegate('sensi/codger-sensi-project@grunt/concurrent');
    $recipe->delegate('sensi/codger-sensi-project@grunt/copy');
    $recipe->delegate('sensi/codger-sensi-project@grunt/shell');
    $recipe->delegate('sensi/codger-sensi-project@grunt/uglify');
    $recipe->delegate('sensi/codger-sensi-project@grunt/watch');
    $recipe->delegate('sensi/codger-sensi-project@versions');
    chmod(getcwd().'/bin/versions', 0755);
    return $recipe;
};

