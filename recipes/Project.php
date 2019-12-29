<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Codger\Generate\Language;
use Codger\Php\Composer;
use Codger\Javascript\Npm;
use Codger\Lodger\{ Module, View };
use Twig\{ Environment, Loader\FilesystemLoader };
use PDO;

/**
 * Kick off an entire Sensi project. Database credentials are taken from
 * `Envy.json`. Also, make sure to run `composer init` and `npm init` first.
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
    public $api = false;

    public function __invoke() : void
    {
        $this->setTwigEnvironment(new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')));
        if (!file_exists(getcwd().'/composer.json')) {
            $this->error("Please run `composer init` first.\n");
            return;
        }
        if (!file_exists(getcwd().'/package.json')) {
            $this->error("Please run `npm init` first.\n");
            return;
        }
        if (!file_exists(getcwd().'/Envy.json')) {
            $this->error("Please setup `Envy.json` first.\n");
            return;
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
            $vendor = 'mysql';
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
            $this->delegate(Module::class, [$module, '--output-dir=src', "--vendor=$vendor", "--database=$database", "--user=$user", "--pass=$password", '--ornament']);
        }
        $this->delegate(BaseTemplate::class, [$project, '--output-dir=src']);
        $this->delegate(View::class, ['Home', '--output-dir=src', '--extends=\View', '--template=Home/template.html.twig']);
        $this->delegate(HomeTemplate::class, ['--output-dir=src']);
        $this->delegate(Sass::class, ['Home', '--output-dir=src']);

        $this->addComposerPackages($vendor);
        $this->addNodePackages();

        if (isset($this->outputDir)) {
            copy(dirname(__DIR__).'/static/Gruntfile.js', 'Gruntfile.js');
            mkdir('grunt');
            copy(dirname(__DIR__).'/static/grunt-aliases.js', 'grunt/aliases.js');
            copy(dirname(__DIR__).'/static/grunt-ngtemplates.js', 'grunt/ngtemplates.js');
            copy(dirname(__DIR__).'/static/grunt-browserify.js', 'grunt/browserify.js');
            copy(dirname(__DIR__).'/static/grunt-sass.js', 'grunt/sass.js');
            copy(dirname(__DIR__).'/static/grunt-postcss.js', 'grunt/postcss.js');
            copy(dirname(__DIR__).'/static/grunt-concurrent.js', 'grunt/concurrent.js');
            copy(dirname(__DIR__).'/static/grunt-copy.js', 'grunt/copy.js');
            copy(dirname(__DIR__).'/static/grunt-shell.js', 'grunt/shell.js');
            copy(dirname(__DIR__).'/static/grunt-uglify.js', 'grunt/uglify.js');
            copy(dirname(__DIR__).'/static/grunt-watch.js', 'grunt/watch.js');
        }
    }

    private function addComposerPackages(string $vendor) : void
    {
        $composer = new Composer;

        // Add Sensi-specific packages
        $composer->addDependency('monolyth/monty');
        $composer->addDependency('ornament/json');
        $composer->addDependency('quibble/'.($vendor == 'pgsql' ? 'postgresql' : 'mysql'));
        $composer->addDependency('sensimedia/minimal');
        $composer->addDependency('sensimedia/fakr');
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
    }
}

