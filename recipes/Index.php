<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;

class Index extends Recipe
{
    protected $_template = 'index.html.twig';
    public function __invoke() : void
    {
        $this->setTwigEnvironment(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__, 2).'/templates')));
        $this->output('httpdocs/index.php');
    }
}

