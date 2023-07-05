<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Codger\Generate\Language;
use Twig\{ Environment, Loader\FilesystemLoader };

class Required extends Recipe
{
    public array $module = [];

    protected string $_template = 'sass.html.twig';

    public function __invoke() : void
    {
        $this->setTwigEnvironment(new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')));
        array_walk($this->module, function (&$module) {
            $module = Language::convert($module, Language::TYPE_PATH);
        });
        $this->set('modules', $this->module);
        $this->output('src/required.scss');
    }
}

