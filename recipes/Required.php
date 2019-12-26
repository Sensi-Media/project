<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Codger\Generate\Language;

class Required extends Recipe
{
    /** @var array */
    public $module = [];

    /** @var string */
    protected $_template = 'sass.html.twig';

    public function __invoke() : void
    {
        $this->setTwigEnvironment(new Twig_Environment(new Twig_Loader_Filesystem(dirname(__DIR__).'/templates')));
        array_walk($this->module, function (&$module) {
            $module = Language::convert($module, Language::TYPE_PATH);
        });
        $this->set('modules', $this->module);
        $this->output('src/required.scss');
    }
}

