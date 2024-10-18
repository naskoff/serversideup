<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Post;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Post>
 */
final class PostFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Post::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'author' => UserFactory::new(),
            'slug' => self::faker()->slug(),
            'title' => self::faker()->sentence(),
            'content' => self::faker()->text(),
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'updatedAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }
}
