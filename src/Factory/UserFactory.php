<?php

namespace App\Factory;

use AllowDynamicProperties;
use App\Entity\User;
use App\Exceptions\DatabaseException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class UserFactory
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws DatabaseException
     */
    public function createUser(string $email, string $password): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setRoles(array('ROLE_USER'));

        $this->entityManager->persist($user);
        try {
            $this->entityManager->flush();
            return $user;
        } catch (Exception $e) {
            throw new DatabaseException($e->getMessage(), 500);
        }
    }
}