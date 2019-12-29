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
     * The database vendor to be used. Currently supported are `pgsql` for
     * PostgreSQL (best support) and `mysql` for MySQL (slightly sketchy).
     *
     * @var string
     */
    public $vendor;

    /**
     * The name of the database to use.
     *
     * @var string
     */
    public $database;

    /**
     * Database username.
     *
     * @var string
     */
    public $user;

    /**
     * Database password.
     *
     * @var string
     */
    public $pass;

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
            $modules[] = Language::convert($table, Language::TYPE_PHP_NAMESPACE);
        }
        asort($modules);
        $this->delegate(Config::class, [$project]);
        $this->delegate(Index::class);
        $options = [$project, "--vendor=$vendor"];
        $modoptions = [];
        foreach ($modules as $module) {
            $modoptions[] = "--module=$module";
        }
        $this->delegate(Dependencies::class, array_merge($options, $modoptions));
        $this->delegate(Routing::class, $modoptions);
        $this->delegate(Required::class, ['--module=Home']);
        $this->delegate(Optional::class, $modoptions);
        foreach ($modules as $module) {
            $this->delegate(Module::class, [$module, "--vendor=$vendor", "--database=$database", "--user=$user", "--pass=$password", '--ornament']);
        }
        $this->delegate(BaseTemplate::class, [$project]);
        $this->delegate(View::class, ['Home', '--extends=\View', '--template=Home/template.html.twig']);
        $this->delegate(HomeTemplate::class, [$project]);
        $this->delegate(Sass::class, 'Home');

        $this->addComposerPackages();
        $this->addNodePackages();

        $this->delegate('sensi:project:grunt');
        $this->delegate('sensi:project:grunt-aliases');
        $this->delegate('sensi:project:grunt-ngtemplates');
        $this->delegate('sensi:project:grunt-browserify');
        $this->delegate('sensi:project:grunt-sass');
        $this->delegate('sensi:project:grunt-postcss');
        $this->delegate('sensi:project:grunt-concurrent');
        $this->delegate('sensi:project:grunt-copy');
        $this->delegate('sensi:project:grunt-shell');
        $this->delegate('sensi:project:grunt-uglify');
        $this->delegate('sensi:project:grunt-watch');
    }

    private function addComposerPackages() : void
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

