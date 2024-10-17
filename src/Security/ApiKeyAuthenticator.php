<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private const HEADER_NAME = 'x-auth-token';

    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has(self::HEADER_NAME);
    }

    public function authenticate(Request $request): Passport
    {
         $apiToken = $request->headers->get('X-AUTH-TOKEN');
         if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
         }

         $user = $this->userRepository->findOneBy(['token' => $apiToken]);
         if (null === $user) {
             throw new CustomUserMessageAuthenticationException('Token not found');
         }

        return new SelfValidatingPassport(
            userBadge: new UserBadge(
                userIdentifier: $user->getUserIdentifier(),
            ),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
