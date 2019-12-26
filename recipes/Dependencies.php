<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Codger\Generate\Language;
use Twig\{ Environment, Loader\FilesystemLoader };

class Dependencies extends Recipe
{
    /** @var string */
    public $vendor;

    /** @var array */
    public $module = [];

    /** @var string */
    protected $_template = 'dependencies.html.twig';

    public function __invoke(string $session) : void
    {
        $this->setTwigEnvironment(new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')));
        $this->output('src/dependencies.php');
        if (isset($this->vendor)) {
            switch ($this->vendor) {
                case 'mysql': $this->set('vendor', 'Mysql'); break;
                case 'pgsql': $this->set('vendor', 'Postgresql'); break;
            }
        }
        array_walk($this->module, function (&$repository) {
            $repository = [
                'variable' => Language::convert($repository, Language::TYPE_VARIABLE),
                'namespace' => Language::convert($repository, Language::TYPE_PHP_NAMESPACE),
            ];
        });
        $this->set('repositories', $this->module);
        $this->set('session', $session);
    }
}

