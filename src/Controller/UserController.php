<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    public function user(): Response
    {
        error_log('user() called');
        $user = $this->getUser();

        if (!$user) {
            // Handle the case where there is no authenticated user
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // User is authenticated, return their info
        return $this->json(['email' => $user->getEmail()]);
    }
}