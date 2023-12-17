<?php

namespace App\services\errors;

use App\Dot\UserRegistrationDto;
use App\Exceptions\InvalidRequestException;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ErrorRegisterService
{
    private UserRegistrationDto $userRegistrationDto;
    private ValidatorInterface $validator;
    private UserRepository $userRepository;

    public function __construct(
        UserRegistrationDto $userRegistrationDto,
        ValidatorInterface $validator,
        UserRepository $userRepository,
    )
    {
        $this->userRegistrationDto = $userRegistrationDto;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws InvalidRequestException
     * @throws \JsonException
     */
    public function validateRegisterRequest(Request $request): UserRegistrationDto
    {
        $errors = [];

        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if ($this->userRepository->findOneBy(['email' => $data['email']])) {
            $errors[] = [
                'field' => 'email',
                'message' => 'The email already exists',
                'passedValue' => $data['email']
            ];
            throw new InvalidRequestException(json_encode($errors, JSON_THROW_ON_ERROR), 400);
        }

        $this->userRegistrationDto->setEmail($data['email']);
        $this->userRegistrationDto->setPassword($data['password']);
        $this->userRegistrationDto->setRoles(array('ROLE_USER'));

        if ($this->validator->validate($this->userRegistrationDto)->count() > 0) {
            foreach ($this->validator->validate($this->userRegistrationDto) as $error) {
                $errors[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                    'passedValue' => $error->getInvalidValue()
                ];
            }
            throw new InvalidRequestException(json_encode($errors, JSON_THROW_ON_ERROR), 400);
        }

        return $this->userRegistrationDto;
    }
}