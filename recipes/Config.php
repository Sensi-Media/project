<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;

class Config extends Recipe
{
    protected $_template = 'config.html.twig';

    public function __invoke(string $project) : void
    {
        $this->setTwigEnvironment(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates'));
        $this->set('project', $project);
        $this->output('ServerConfig.json');
    }
};

