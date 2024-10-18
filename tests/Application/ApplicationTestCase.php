<?php

declare(strict_types=1);

namespace App\Tests\Application;

use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

abstract class ApplicationTestCase extends WebTestCase
{
    use Factories;
    use PHPMatcherAssertions;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
    }

    protected function getJsonResponse(): array
    {
        /** @var string $content */
        $content = $this->client->getResponse()->getContent();

        return json_decode($content, true);
    }
}