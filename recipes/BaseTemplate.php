<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Codger\Generate\Language;

class BaseTemplate extends Recipe
{
    /** @var string */
    public $module = [];

    /** @var string */
    protected $_template = 'base.html.twig';

    public function __invoke(string $project) : void
    {
        $twig = new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'));
        $twig->addFilter(new Twig_SimpleFilter('normalize', function (string $module) : string {
            return strtolower(str_replace('\\', '-', $module));
        }));
        $this->setTwigEnvironment($twig);
        $this->output(getcwd()."/src/template.html.twig");
        $this->set('project', $project);
        $this->set('modules', $modules);
    }
}

