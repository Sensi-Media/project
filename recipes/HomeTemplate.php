<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Codger\Generate\Language;
use Twig\{ Environment, Loader\FilesystemLoader, TwigFilter };

class HomeTemplate extends Recipe
{
    /** @var string */
    protected $_template = 'home.html.twig';

    public function __invoke() : void
    {
        $this->setTwigEnvironment(new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')));
        $this->output("Home/template.html.twig");
    }
}

