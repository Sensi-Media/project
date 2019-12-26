<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Twig\{ Environment, Loader\FilesystemLoader };

class Config extends Recipe
{
    protected $_template = 'config.html.twig';

    public function __invoke(string $project) : void
    {
        $this->setTwigEnvironment(new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')));
        $this->set('project', $project);
        $this->output('ServerConfig.json');
    }
}

