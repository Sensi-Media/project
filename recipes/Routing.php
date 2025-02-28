<?php

namespace Codger\Sensi;

use Codger\Generate\Recipe;
use Twig\{ Environment, Loader\FilesystemLoader, TwigFilter };

class Routing extends Recipe
{
    public array $module = [];

    public bool $api = false;

    protected string $_template = 'routing.html.twig';

    public function __invoke() : void
    {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__).'/templates'));
        $twig->addFilter(new TwigFilter('normalize', function (string $module) : string {
            return strtolower(str_replace('\\', '-', $module));
        }));
        $twig->addFilter(new TwigFilter('fordb', function (string $module) : string {
            return strtolower(str_replace('-', '_', $module));
        }));
        $this->setTwigEnvironment($twig);
        $this->set('modules', $this->module);
        $this->set('api', $this->api);
        $this->output('routing.php');
    }
}

