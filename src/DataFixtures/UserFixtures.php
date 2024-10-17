<?php

namespace App\DataFixtures;

use App\Tests\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = UserFactory::new()->createOne(['token' => 'qwerty']);
        $manager->persist($user);

        UserFactory::createMany(2);
    }
}
