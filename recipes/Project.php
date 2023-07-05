<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Codger\Generate\Language;
use Codger\Php\Composer;
use Codger\Javascript\Npm;
use Codger\Lodger\{ Module, View };
use Twig\{ Environment, Loader\FilesystemLoader };
use PDO;
use Dotenv\Dotenv;
use stdClass;

/**
 * Kick off an entire Sensi project. Database credentials are taken from
 * `.env`. Also, make sure to run `composer init` and `yarn init` first.
 *
 * Options: `api` to add an api.
 */
class Project extends Recipe
{
    /**
     * Add an API.
     *
     * @var bool
     */
    public bool $api = false;

    public function __invoke() : void
    {
        $this->setTwigEnvironment(new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')));
        if (!file_exists(getcwd().'/composer.json')) {
            $this->error("Please run `composer init` first.\n");
            return;
        }
        if (!file_exists(getcwd().'/package.json')) {
            $this->error("Please run `yarn init` first.\n");
            return;
        }
        $json = json_decode(file_get_contents(getcwd().'/package.json'), false);
        if (!isset($json->scripts)) {
            $json->scripts = new stdClass;
        }
        foreach ([
            "dev" => "npm run development",
            "development" => "NODE_ENV=development node_modules/webpack/bin/webpack.js --progress --config=node_modules/laravel-mix/setup/webpack.config.js",
            "watch" => "npm run development -- --watch",
            "watch-poll" => "npm run watch -- --watch-poll",
            "hot" => "NODE_ENV=development node_modules/webpack-dev-server/bin/webpack-dev-server.js --inline --hot --config=node_modules/laravel-mix/setup/webpack.config.js",
            "prod" => "npm run production",
            "production" => "NODE_ENV=production node_modules/webpack/bin/webpack.js --no-progress --config=node_modules/laravel-mix/setup/webpack.config.js"
        ] as $name => $value) {
            if (!isset($json->scripts->$name)) {
                $json->scripts->$name = $value;
            }
        }
        file_put_contents(getcwd().'/package.json', json_encode($json, JSON_PRETTY_PRINT));
        if (!file_exists(getcwd().'/.env')) {
            $this->error("Please setup you `.env` file first.\n");
            return;
        }
        $dotenv = Dotenv::createImmutable(getcwd());
        $dotenv->load();
        $project = basename(getcwd());
        $modules = [];
        // We prefer postgresql, but some projects are stuck on mysql.
        $vendor = $_ENV['DB_VENDOR'] ?? 'pgsql';
        try {
            $adapter = new PDO("$vendor:dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASS']);
        } catch (PDOException $e) {
            $this->error("Could not connect to the specified database; please make sure it exists "
                ." and define it in `.env` using DB_NAME, DB_USER and DB_PASS (and optionally DB_VENDOR).");
            return;
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
            $modules[] = Language::convert($table, Language::TYPE_PHP_NAMESPACE);
        }
        asort($modules);
        $this->delegate(Config::class, [$project, '--output-dir=.']);
        $this->delegate(Index::class, ['--output-dir=httpdocs']);
        $options = [$project, "--vendor=$vendor", '--output-dir=src'];
        $modoptions = [];
        foreach ($modules as $module) {
            $modoptions[] = "--module=$module";
        }
        $this->delegate(Dependencies::class, array_merge($options, $modoptions));
        $this->delegate(Routing::class, array_merge(['--output-dir=src'], $modoptions));
        $this->delegate(Required::class, ['--output-dir=src', '--module=Home']);
        $this->delegate(Optional::class, array_merge(['--output-dir=src'], $modoptions));
        foreach ($modules as $module) {
            $this->delegate(
                Module::class,
                [$module, '--output-dir=src', "--vendor=$vendor", "--database=$database", "--user=$user", "--pass=$password", '--ornament']
            );
            $this->delegate(Repository::class, [$module, '--output-dir=src']);
        }
        $this->delegate(BaseTemplate::class, [$project, '--output-dir=src']);
        $this->delegate(View::class, ['Home', '--output-dir=src', '--extends=\View', '--template=Home/template.html.twig']);
        $this->delegate(HomeTemplate::class, ['--output-dir=src']);
        $this->delegate(Sass::class, ['Home', '--output-dir=src']);

        $this->addComposerPackages($vendor);
        $this->addNodePackages();

        if (isset($this->outputDir)) {
            copy(dirname(__DIR__).'/static/webpack.mix.js', 'webpack.mix.js');
        }
    }

    private function addComposerPackages(string $vendor) : void
    {
        $composer = new Composer;

        // Add Sensi-specific packages
        $composer->addDependency('monolyth/monty');
        $composer->addDependency('ornament/json');
        $composer->addDependency('quibble/'.($vendor == 'pgsql' ? 'postgresql' : 'mysql'));
        $composer->addDependency('quibble/query');
        $composer->addDependency('sensimedia/minimal');
        $composer->addDependency('sensimedia/fakr');
        $composer->addDependency('sensimedia/supportery');
        $composer->addDependency('twig/extensions');
        $composer->addDependency('gentry/gentry', true);
        $composer->addDependency('gentry/toast', true);
        $composer->addDependency('toast/acceptance', true);
        $composer->addDependency('toast/cache', true);
        $composer->addDependency('toast/unit', true);
        $composer->addDependency('sensimedia/codein', true);

        if ($this->api) {
            $composer->addDependency('monomelodies/monki');
            $composer->addDependency('sensimedia/api');
        }
    }

    private function addNodePackages() : void
    {
        // Add NPM packages
        $package = new Npm;
        foreach ([
            "laravel-mix",
            "jasmine-core",
            "karma",
            "karma-browserify",
            "karma-jasmine",
            "karma-phantomjs-launcher",
            "monad-cms",
            "monad-crud",
            "monad-navigation",
            "monad-theme-default",
            "node-sass",
            "postcss-preset-env",
            "postcss",
            "postcss-css-variables",
            "postcss-nested",
            "precss"
        ] as $name) {
            $package->addDependency($name, true);
        }
    }
}

