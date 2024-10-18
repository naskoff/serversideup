<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/comments')]
final class CommentController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly PostRepository $postRepository,
        private readonly CommentRepository $commentRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route(methods: [Request::METHOD_GET])]
    public function index(#[CurrentUser] User $user): JsonResponse
    {
        $comments = $this->commentRepository->findBy(['author' => $user]);

        $response = array_map(fn(Comment $comment) => [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'created_at' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $comment->getUpdatedAt()->format('Y-m-d H:i:s'),
        ], $comments);

        return $this->json($response, Response::HTTP_OK);
    }

    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function view(#[CurrentUser] User $user, int $id): JsonResponse
    {
        $comment = $this->commentRepository->findOneBy(['id' => $id, 'author' => $user]);

        $response = [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'created_at' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $comment->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return $this->json($response, Response::HTTP_OK);
    }

    #[Route(methods: [Request::METHOD_POST])]
    public function create(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Collection([
            'post_id' => [],
            'content' => [],
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (0 < $errors->count()) {
            $formattedErrors = [];
            foreach ($errors as $error) {
                $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json($formattedErrors, Response::HTTP_BAD_REQUEST);
        }

        $post = $this->postRepository->findOneBy(['id' => $data['post_id'], 'author' => $user]);
        if (null === $post) {
            return $this->json([
                'code' => 404,
                'message' => 'Post not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $comment = (new Comment($post, $user))
            ->setCOntent($data['content']);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $response = [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'created_at' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $comment->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return $this->json($response, Response::HTTP_CREATED);
    }

    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: [Request::METHOD_PUT])]
    public function update(#[CurrentUser] User $user, Request $request, int $id): JsonResponse
    {
        $data = $request->toArray();

        $comment = $this->commentRepository->findOneBy(['id' => $id, 'author' => $user]);
        if (null === $comment) {
            return $this->json([
                'code' => 404,
                'message' => 'Comment not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $constraints = new Collection([
            'post_id' => [],
            'content' => [],
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (0 < $errors->count()) {
            $formattedErrors = [];
            foreach ($errors as $error) {
                $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json($formattedErrors, Response::HTTP_BAD_REQUEST);
        }

        $post = $this->postRepository->findOneBy(['id' => $data['post_id'], 'author' => $user]);
        if (null === $post) {
            return $this->json([
                'code' => 404,
                'message' => 'Post not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $comment
            ->setContent($data['content']);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $response = [
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'created_at' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $comment->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        return $this->json($response, Response::HTTP_CREATED);
    }

    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function delete(#[CurrentUser] User $user, int $id): JsonResponse
    {
        $comment = $this->commentRepository->findOneBy(['id' => $id, 'author' => $user]);
        if (null === $comment) {
            return $this->json([
                'code' => 404,
                'message' => 'Comment not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
