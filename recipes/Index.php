<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Twig\{ Environment, Loader\FilesystemLoader };

class Index extends Recipe
{
    protected string $_template = 'index.html.twig';

    public function __invoke() : void
    {
        $this->setTwigEnvironment(new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')));
        $this->output('index.php');
    }
}

