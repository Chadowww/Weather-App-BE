<?php

namespace App\services\errors;

use AllowDynamicProperties;
use App\Dot\UserRegistrationDto;
use App\Exceptions\InvalidRequestException;
use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AllowDynamicProperties] class ErrorRegisterService
{
    public function __construct(
        UserRegistrationDto $userRegistrationDto,
        ValidatorInterface $validator,
        UserFactory $userFactory,
    )
    {
        $this->userRegistrationDto = $userRegistrationDto;
        $this->validator = $validator;
        $this->userFactory = $userFactory;
    }

    /**
     * @throws InvalidRequestException
     * @throws \JsonException
     */
    public function validateRegisterRequest(Request $request): UserRegistrationDto
    {
        $errors = [];

        $data = $request->request->all();
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