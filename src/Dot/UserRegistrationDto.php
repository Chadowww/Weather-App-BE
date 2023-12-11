<?php

namespace App\Dot;

use Symfony\Component\Validator\Constraints as Assert;
class UserRegistrationDto
{
    #[Assert\Type('string', 'The email should be of type string')]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email')]
    private string $email;

    #[Assert\Type('array', 'The roles should be of type array')]
    private array $roles = [];

    #[Assert\Type('string', 'The password should be of type string')]
    #[Assert\Length(min: 6, max: 255, minMessage: 'The password should be at least {{ limit }} characters long')]
    #[Assert\NotBlank(message: 'The password should not be blank')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/',
        message: 'The password must contain at least 8 characters, one uppercase letter, one lowercase letter and one number'
    )]
    private ?string $password = null;

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }


}