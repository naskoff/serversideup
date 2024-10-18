<?php

namespace App\Tests\Story;

use App\Tests\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class UserStory extends Story
{
    public function build(): void
    {
        $this->addState('main-author', UserFactory::createOne([
            'email' => 'admin@domain.com',
            'password' => 'admin',
            'token' => 'qwerty',
        ]));
    }
}
