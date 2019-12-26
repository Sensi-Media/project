<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;

class Routing extends Recipe
{
    /** @var array */
    public $module = [];

    /** @var bool */
    public $api = false;

    /** @var string */
    protected $_template = 'routing.html.twig';

    public function __invoke() : void
    {
        $twig = new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'));
        $twig->addFilter(new Twig_SimpleFilter('normalize', function (string $module) : string {
            return strtolower(str_replace('\\', '-', $module));
        }));
        $twig->addFilter(new Twig_SimpleFilter('fordb', function (string $module) : string {
            return strtolower(str_replace('-', '_', $module));
        }));
        $this->setTwigEnvironment($this);
        $this->set('modules', $this->module);
        $this->set('api', $this->api);
        $this->output('src/routing.php');
    }
}

