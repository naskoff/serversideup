<?php

declare(strict_types=1);

namespace App\Tests\Application\Controller\Api;

use App\Entity\Post;
use App\Entity\User;
use App\Tests\Application\ApplicationTestCase;
use App\Tests\Factory\PostFactory;
use App\Tests\Story\PostStory;
use App\Tests\Story\UserStory;

final class PostControllerTest extends ApplicationTestCase
{
    private User $user;
    private Post $post;
    private const API_URL = '/api/posts';
    private const EXPECTED_POST_RESPONSE = [
        'id' => '@integer@',
        'title' => '@string@',
        'content' => '@string@',
        'created_at' => '@datetime@.isInDateFormat(\'Y-m-d H:i:s\')',
        'updated_at' => '@datetime@.isInDateFormat(\'Y-m-d H:i:s\')',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = UserStory::get('main-author');
        $this->user = $user;

        /** @var Post $post */
        $post = PostStory::get('main-post');
        $this->post = $post;
    }

    public function testGetListPostWorks(): void
    {
        PostFactory::createOne(['author' => $this->user]);

        $this->client->jsonRequest('GET', self::API_URL, [], [
            'HTTP_X-AUTH-TOKEN' => $this->user->getToken(),
        ]);

        $response = $this->getJsonResponse();

        $expectedResponse = [
            self::EXPECTED_POST_RESPONSE,
            '@...@',
        ];

        $this->assertMatchesPattern($expectedResponse, $response);
    }

    public function testViewPostWithNonExistingPostReturn404(): void
    {
        $this->client->jsonRequest('GET', self::API_URL.'/404', [], [
            'HTTP_X-AUTH-TOKEN' => $this->user->getToken(),
        ]);

        $response = $this->getJsonResponse();

        $this->assertResponseStatusCodeSame(404);
        $this->assertSame(['code' => 404, 'message' => 'Project not found'], $response);
    }

    public function testViewPostReturnSuccess(): void
    {
        $this->client->jsonRequest('GET', self::API_URL.'/'.$this->post->getId(), [], [
            'HTTP_X-AUTH-TOKEN' => $this->user->getToken(),
        ]);

        $expectedResponse = array_merge(self::EXPECTED_POST_RESPONSE, [
            'id' => $this->post->getId(),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertMatchesPattern($expectedResponse, $this->getJsonResponse());
    }

    public function testCreatePostWorks(): void
    {
        $data = [
            'title' => 'test-title',
            'content' => 'test-content',
        ];

        $this->client->jsonRequest('POST', self::API_URL, $data, [
            'HTTP_X-AUTH-TOKEN' => $this->user->getToken(),
        ]);

        $expectedResponse = array_merge(self::EXPECTED_POST_RESPONSE, [
            'title' => 'test-title',
            'content' => 'test-content',
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertMatchesPattern($expectedResponse, $this->getJsonResponse());
    }

    /**
     * @dataProvider provideInvalidData
     *
     * @param array<mixed> $data
     * @param array<mixed> $expectedResponse
     */
    public function testCreatePostWithInvalidDataReturnErrors(
        array $data = [],
        array $expectedResponse = [],
        int $statusCode = 400,
    ): void {
        $this->client->jsonRequest('POST', self::API_URL, $data, [
            'HTTP_X-AUTH-TOKEN' => $this->user->getToken(),
        ]);

        $this->assertResponseStatusCodeSame($statusCode);
        $this->assertMatchesPattern($expectedResponse, $this->getJsonResponse());
    }

    /**
     * @depends testCreatePostWorks
     */
    public function testUpdatePostWorks(): void
    {
        $data = [
            'title' => 'update-test-title',
            'content' => 'update-test-content',
        ];

        $this->client->jsonRequest('PUT', self::API_URL.'/'.$this->post->getId(), $data, [
            'HTTP_X-AUTH-TOKEN' => $this->user->getToken(),
        ]);

        $expectedResponse = array_merge(self::EXPECTED_POST_RESPONSE, [
            'title' => 'update-test-title',
            'content' => 'update-test-content',
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertMatchesPattern($expectedResponse, $this->getJsonResponse());
    }

    public function testUpdatePostWithNonExistsPostReturn404(): void
    {
        $data = [
            'title' => 'update-test-title',
            'content' => 'update-test-content',
        ];

        $this->client->jsonRequest('PUT', self::API_URL.'/404', $data, [
            'HTTP_X-AUTH-TOKEN' => $this->user->getToken(),
        ]);

        $this->assertResponseStatusCodeSame(404);
        $this->assertMatchesPattern(['code' => 404, 'message' => 'Project not found'], $this->getJsonResponse());
    }

    /**
     * @dataProvider provideInvalidData
     *
     * @param array<mixed> $data
     * @param array<mixed> $expectedResponse
     */
    public function testUpdatePostWithInvalidDataReturnErrors(
        array $data = [],
        array $expectedResponse = [],
        int $statusCode = 400,
    ): void {
        $this->client->jsonRequest('PUT', self::API_URL.'/'.$this->post->getId(), $data, [
            'HTTP_X-AUTH-TOKEN' => $this->user->getToken(),
        ]);

        $this->assertResponseStatusCodeSame($statusCode);
        $this->assertMatchesPattern($expectedResponse, $this->getJsonResponse());
    }

    public function testDeletePostWorks(): void
    {
        $this->client->jsonRequest('DELETE', self::API_URL.'/'.$this->post->getId(), [], [
            'HTTP_X-AUTH-TOKEN' => $this->user->getToken(),
        ]);

        $this->assertResponseStatusCodeSame(204);
    }

    public function testDeleteWithNonExistingPostReturn404(): void
    {
        $this->client->jsonRequest('DELETE', self::API_URL.'/404', [], [
            'HTTP_X-AUTH-TOKEN' => $this->user->getToken(),
        ]);

        $this->assertResponseStatusCodeSame(404);
        $this->assertMatchesPattern(['code' => 404, 'message' => 'Project not found'], $this->getJsonResponse());
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function provideInvalidData(): iterable
    {
        yield 'empty body' => [
            [],
            [
                '[title]' => 'This field is missing.',
                '[content]' => 'This field is missing.',
            ],
        ];

        yield 'nullable content' => [
            [
                'title' => null,
                'content' => null,
            ],
            [
                '[title]' => 'This value should not be blank.',
                '[content]' => 'This value should not be blank.',
            ],
        ];

        yield 'empty content' => [
            [
                'title' => '',
                'content' => '',
            ],
            [
                '[title]' => 'This value is too short. It should have 3 characters or more.',
                '[content]' => 'This value is too short. It should have 3 characters or more.',
            ],
        ];

        yield 'min length body content' => [
            [
                'title' => 'a',
                'content' => 'a',
            ],
            [
                '[title]' => 'This value is too short. It should have 3 characters or more.',
                '[content]' => 'This value is too short. It should have 3 characters or more.',
            ],
        ];

        yield 'max length body content' => [
            [
                'title' => str_repeat('a', 256),
                'content' => str_repeat('a', 1001),
            ],
            [
                '[title]' => 'This value is too long. It should have 255 characters or less.',
                '[content]' => 'This value is too long. It should have 1000 characters or less.',
            ],
        ];
    }
}
