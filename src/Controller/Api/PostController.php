<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\String\ByteString;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/posts')]
final class PostController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly PostRepository $postRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route(methods: [Request::METHOD_GET])]
    public function index(#[CurrentUser] User $user): JsonResponse
    {
        $posts = $this->postRepository->findBy(['author' => $user]);

        $response = array_map(fn(Post $post) => [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $post->getUpdatedAt()->format('Y-m-d H:i:s'),
        ], $posts);

        return $this->json($response, Response::HTTP_OK);
    }

    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function view(#[CurrentUser] User $user, int $id): JsonResponse
    {
        $post = $this->postRepository->findOneBy(['id' => $id, 'author' => $user]);
        if (null === $post) {
            return $this->json([
                'code' => 404,
                'message' => 'Project not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $response = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $post->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return $this->json($response, Response::HTTP_CREATED);
    }

    #[Route(methods: [Request::METHOD_POST])]
    public function create(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Collection([
            'title' => [
                new NotBlank(),
                new Length(min: 3, max: 255),
            ],
            'content' => [
                new NotBlank(),
                new Length(min: 3, max: 1000),
            ],
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (0 < $errors->count()) {
            $formattedErrors = [];
            foreach ($errors as $error) {
                $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json($formattedErrors, Response::HTTP_BAD_REQUEST);
        }

        $slugger = new AsciiSlugger();
        $slugBase = $slugger->slug($data['title'])->toString();

        do {
            $slug = implode('-', [$slugBase, ByteString::fromRandom(4)->toString()]);
        } while (null !== $this->postRepository->findOneBy(['slug' => $slug]));

        $post = (new Post($user, $slug))
            ->setTitle($data['title'])
            ->setContent($data['content']);

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $response = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $post->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return $this->json($response, Response::HTTP_CREATED);
    }

    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: [Request::METHOD_PUT])]
    public function update(#[CurrentUser] User $user, Request $request, int $id): JsonResponse
    {
        $data = $request->toArray();

        $post = $this->postRepository->findOneBy(['id' => $id, 'author' => $user]);
        if (null === $post) {
            return $this->json([
                'code' => 404,
                'message' => 'Project not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $constraints = new Collection([
            'title' => [
                new NotBlank(),
                new Length(min: 3, max: 255),
            ],
            'content' => [
                new NotBlank(),
                new Length(min: 3, max: 1000),
            ],
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (0 < $errors->count()) {
            $formattedErrors = [];
            foreach ($errors as $error) {
                $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json($formattedErrors, Response::HTTP_BAD_REQUEST);
        }

        $post
            ->setTitle($data['title'])
            ->setContent($data['content']);

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $response = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $post->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return $this->json($response, Response::HTTP_CREATED);
    }

    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function delete(#[CurrentUser] User $user, int $id): JsonResponse
    {
        $post = $this->postRepository->findOneBy(['id' => $id, 'author' => $user]);
        if (null === $post) {
            return $this->json([
                'code' => 404,
                'message' => 'Project not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
