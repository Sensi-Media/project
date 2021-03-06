<?php

$recipe = new Codger\Sensi\Project(['--api', '--output-dir=.']);
$inout = new Codger\Generate\FakeInOut;
Codger\Generate\Recipe::setInOut($inout);

/** Project recipe */
return function () use ($recipe, $inout) : Generator {

    $path = getcwd();
    $this->beforeEach(function () use ($recipe, $inout, &$result) {
        exec('rm -rf tmp/project');
        exec('cp -r tests/init tmp/project');
        chdir('tmp/project');
        $recipe->execute();
        $inout->flush();
    });
    $this->afterEach(function () use ($path) {
        chdir($path);
    });

    /** Generating all files */
    yield function () : Generator {

        /** generates ServerConfig.json */
        yield function () {
            assert(file_exists('tmp/project/ServerConfig.json'));
            $result = file_get_contents('tmp/project/ServerConfig.json');
            assert(strpos($result, <<<EOT
    {
        "codger-sensi-project": "httpdocs"
    }
EOT
            ) !== false);
        };
        
        /** generates Envy.json */
        yield function () use ($result) {
            assert(strpos($result, 'Envy.json') !== false);
        };
        
        /** generates index */
        yield function () use ($result) {
            assert(strpos($result, 'httpdocs/index.php') !== false);
        };
        
        /** generates dependencies */
        yield function () use ($result) {
            assert(strpos($result, 'src/dependencies.php') !== false);
        };
        
        /** generates routing */
        yield function () use ($result) {
            assert(strpos($result, 'src/routing.php') !== false);
        };
        
        /** generates required.scss */
        yield function () use ($result) {
            assert(strpos($result, 'src/required.scss') !== false);
        };
        
        /** generates required.scss */
        yield function () use ($result) {
            assert(strpos($result, 'src/optional.scss') !== false);
        };
        
        /** generates Users repository */
        yield function () use ($result) {
            assert(strpos($result, 'src/Users/Repository.php') !== false);
        };
        
        /** generates Users model */
        yield function () use ($result) {
            assert(strpos($result, 'src/Users/Model.php') !== false);
        };
        
        /** generates Users template */
        yield function () use ($result) {
            assert(strpos($result, 'src/Users/template.html.twig') !== false);
        };
        
        /** generates Users view */
        yield function () use ($result) {
            assert(strpos($result, 'src/Users/View.php') !== false);
        };
        
        /** generates Users\Detail template */
        yield function () use ($result) {
            assert(strpos($result, 'src/Users/Detail/template.html.twig') !== false);
        };
        
        /** generates Users view */
        yield function () use ($result) {
            assert(strpos($result, 'src/Users/Detail/View.php') !== false);
        };
        
        /** generates Users controller */
        yield function () use ($result) {
            assert(strpos($result, 'src/Users/Controller.php') !== false);
        };
        
        /** generates Users stylesheet */
        yield function () use ($result) {
            assert(strpos($result, 'src/Users/_style.scss') !== false);
        };
        
        /** generates a base template */
        yield function () use ($result) {
            assert(strpos($result, 'src/template.html.twig') !== false);
        };
        
        /** generates a home template */
        yield function () use ($result) {
            assert(strpos($result, 'src/Home/template.html.twig') !== false);
        };
        
        /** generates a home view */
        yield function () use ($result) {
            assert(strpos($result, 'src/Home/View.php') !== false);
        };
        
        /** generates a home stylesheet */
        yield function () use ($result) {
            assert(strpos($result, 'src/Home/_style.scss') !== false);
        };
        
        /** generates a gruntfile */
        yield function () use ($result) {
            assert(strpos($result, 'Gruntfile.js') !== false);
        };
        
        /** generates aliases */
        yield function () use ($result) {
            assert(strpos($result, 'grunt/aliases.js') !== false);
        };
        
        /** generates browserify */
        yield function () use ($result) {
            assert(strpos($result, 'grunt/browserify.js') !== false);
        };
        
        /** generates sass */
        yield function () use ($result) {
            assert(strpos($result, 'grunt/sass.js') !== false);
        };
        
        /** generates postcss */
        yield function () use ($result) {
            assert(strpos($result, 'grunt/postcss.js') !== false);
        };
    };
};

