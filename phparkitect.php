<?php

declare(strict_types=1);

use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\Expression\ForClasses\HaveNameMatching;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\Rules\Rule;

/**
 * @codeCoverageIgnore
 */
return static function (Config $config): void {
    $rules = [];
    $classSet = ClassSet::fromDir(__DIR__.'/src');

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
        ->should(new HaveNameMatching('*Controller'))
        ->because('Symfony rules!');

    $config->add($classSet, ...$rules);
};
