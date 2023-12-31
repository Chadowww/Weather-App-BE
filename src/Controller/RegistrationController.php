<?php

namespace App\Controller;

use App\Entity\User;
use App\Exceptions\{DatabaseException, InvalidRequestException};
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\services\errors\ErrorRegisterService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private UserFactory $userFactory;

    public function __construct(EmailVerifier $emailVerifier, UserFactory $userFactory)
    {
        $this->emailVerifier = $emailVerifier;
        $this->userFactory = $userFactory;
    }

    /**
     * @throws \JsonException
     * @throws DatabaseException
     * @throws InvalidRequestException
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        ErrorRegisterService $errorRegisterService,
    ): JsonResponse
    {
        $userDot = $errorRegisterService->validateRegisterRequest($request);

        $user = $this->userFactory->createUser(
            $userDot->getEmail(),
            $userPasswordHasher->hashPassword(new User(), $userDot->getPassword()),
        );

        $this->sendEmail($user);

        return new JsonResponse(['201' => 'new user created'], Response::HTTP_CREATED);
    }

    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }

    public function sendEmail(User $user): void
    {
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('contact@weather-app.fr', 'l\'équipe de Weather-App'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
}
