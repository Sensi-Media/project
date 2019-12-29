<?php

namespace Codger\Sensi\Grunt;

use Codger\Generate\Recipe;
use Twig\{ Environment, Loader\FilesystemLoader };

class NgTemplates extends Recipe
{
    /** @var string */
    protected $_template = 'grunt/ngtemplates.html.twig';

    public function __invoke() : void
    {
        $this->setTwigEnvironment(new Environment(new FilesystemLoader(dirname(__DIR__, 2).'/templates')));
        $this->output('grunt/ngtemplates.js');
    }
}

