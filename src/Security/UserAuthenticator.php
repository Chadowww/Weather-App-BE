<?php

namespace App\Security;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class UserAuthenticator extends AbstractAuthenticator implements UserLoaderInterface
{
    private UserRepository $userRepository;
    private $jwtTokenManager;
    protected SerializerInterface $serializer;

    public function __construct(
        UserRepository $userRepository,
        JWTTokenManagerInterface $jwtTokenManager,
        SerializerInterface $serializer
    )
    {
        $this->userRepository = $userRepository;
        $this->jwtTokenManager = $jwtTokenManager;
        $this->serializer = $serializer;
    }

    /**
     * @throws \JsonException
     */
    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/api/login' && $request->isMethod('POST');
    }

    /**
     * @throws \JsonException
     */
    public function authenticate(Request $request): Passport
    {
        $credentials = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if ($credentials['email'] === null || $credentials['password'] === null) {
            throw new AuthenticationException('Email or password incorrect');
        }

        $user = $this->loadUserByIdentifier($credentials['email']);
        if (!$user) {
            throw new AuthenticationException('Email incorrect');
        }

        if (!$user->isVerified()) {
            error_log('not verified');
            throw new AuthenticationException('Your account is not verified. Please check your emails.');
        }

        return new Passport(
            new UserBadge(
                $credentials['email'],
                function ($email) {
                    $user = $this->userRepository->findOneBy(['email' => $email]);

                    if (!$user) {
                        throw new UserNotFoundException();
                    }

                    return $user;
                }
            ),
            new PasswordCredentials($credentials['password'])
        );
    }

    /**
     * @throws \JsonException
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?JsonResponse
    {
        $user = $token->getUser();
        $userData = $this->serializer->serialize($user, 'json');
        /** @var string $token */
        $token = $this->jwtTokenManager->create($user);

        return new JsonResponse(['token' => $token, 'user' => $userData]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => $exception->getMessageKey()
        ], Response::HTTP_UNAUTHORIZED);
    }

//    public function start(Request $request, AuthenticationException $authException = null): Response
//    {
//        /*
//         * If you would like this class to control what happens when an anonymous user accesses a
//         * protected page (e.g. redirect to /login), uncomment this method and make this class
//         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
//         *
//         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
//         */
//    }
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return $this->userRepository->findOneBy(['email' => $identifier]);
    }
}
