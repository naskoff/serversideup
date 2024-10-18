<?php

namespace App\Tests\Story;

use App\Tests\Factory\CommentFactory;
use Zenstruck\Foundry\Story;

final class CommentStory extends Story
{
    public function build(): void
    {
        $this->addState('comment', CommentFactory::createOne());
    }
}
