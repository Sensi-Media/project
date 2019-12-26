<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Codger\Generate\Language;

class Dependencies extends Recipe
{
    /** @var string */
    public $vendor;

    /** @var array */
    public $modules = [];

    /** @var string */
    protected $_template = 'dependencies.html.twig';

    public function __invoke(string $vendor, string $session) : void
    {
        $this->setTwigEnvironment(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates')));
        $this->output('src/dependencies.php');
        switch ($this->vendor) {
            case 'mysql': $this->set('vendor', 'Mysql'); break;
            case 'pgsql': $this->set('vendor', 'Postgresql'); break;
        }
        array_walk($this->modules, function (&$repository) {
            $repository = [
                'variable' => Language::convert($repository, Language::TYPE_VARIABLE),
                'namespace' => Language::convert($repository, Language::TYPE_NAMESPACE),
            ];
        });
        $this->set('repositories', $this->modules);
        $this->set('session', $session);
    }
}

