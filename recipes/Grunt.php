<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Twig\{ Environment, Loader\FilesystemLoader };

class Grunt extends Recipe
{
    protected $_template = 'grunt.html.twig';

    public function __invoke()
    {
        $this->setTwigEnvironment(new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')));
        $this->output('Gruntfile.js');
    }
}

