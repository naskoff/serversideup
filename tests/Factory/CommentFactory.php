<?php

namespace App\Tests\Factory;

use App\Entity\Comment;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Comment>
 */
final class CommentFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Comment::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'content' => self::faker()->text(),
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'updatedAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }
}
