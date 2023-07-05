<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Codger\Generate\Language;
use Twig\{ Environment, Loader\FilesystemLoader, TwigFilter };

class BaseTemplate extends Recipe
{
    public array $module = [];

    protected string $_template = 'base.html.twig';

    public function __invoke(string $project) : void
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__).'/templates'));
        $twig->addFilter(new TwigFilter('normalize', function (string $module) : string {
            return strtolower(str_replace('\\', '-', $module));
        }));
        $this->setTwigEnvironment($twig);
        $this->output(getcwd()."/src/template.html.twig");
        $this->set('project', $project);
        $this->set('modules', $this->module);
    }
}

