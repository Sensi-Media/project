<?php

namespace Codger\Sensi\Gruntl

use Codger\Generate\Recipe;
use Twig\{ Environment, Loader\FilesystemLoader };

class Aliases extends Recipe
{
    protected $_template = 'grunt/aliases.html.twig';

    public function __invoke() : void
    {
        $this->setTwigEnironment(new Environment(new FilesystemLoader(dirname(__DIR__, 2).'/templates')));
        $this->output('grunt/aliases.js');
    }
}

