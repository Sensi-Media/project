<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Codger\Generate\Language;
use Codger\Php\Composer;
use Codger\Javascript\Npm;
use Codger\Lodger\Module;

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
        $this->setTwigEnvironment(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates')));
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
            $modules[] = Language::convert($table, Language::TYPE_NAMESPACE);
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
            $this->delegate(Module::class, [$module, "--vendor=$vendor", "--database=$database", "--user=$user", "--pass=$password"]);
        }
        $this->delegate('sensi:improse-view:base', $project, ...$modules);
        $this->delegate('sensi:improse-view:view', 'Home', '\View', 'Home/template.html.twig');
        $this->delegate('sensi:monolyth-module:sass', 'Home');

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
        $this->delegate('sensi:project:versions');
        chmod(getcwd().'/bin/versions', 0755);
    }

    private function addComposerPackages() : void
    {
        $composer = new Composer;

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

