<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Tests\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * @codeCoverageIgnore
 */
class UserFixtures extends Fixture
{
    public function __construct(private readonly PasswordHasherFactoryInterface $hasherFactory)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $hasher = $this->hasherFactory->getPasswordHasher(User::class);
        $password = $hasher->hash('123');

        UserFactory::createSequence([
            ['email' => 'admin@domain.com', 'password' => $password, 'token' => 'qwerty'],
            [],
            [],
        ]);
    }
}
