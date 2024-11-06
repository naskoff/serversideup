<?php

namespace App\Tests\Story;

use App\Tests\Factory\PostFactory;
use Zenstruck\Foundry\Story;

final class PostStory extends Story
{
    public function build(): void
    {
        $this->addState('main-post', PostFactory::createOne(['author' => UserStory::get('main-author')]));
    }
}
