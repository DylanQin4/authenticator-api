<?php

namespace App\Controller;

use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleAuthController extends AbstractController
{
    private Auth $firebaseAuth;

    public function __construct(Auth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    #[Route('/api/google-login', name: 'api_google_login', methods: ['POST'])]
    public function googleLogin(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['idToken'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'ID token requis',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($data['idToken']);
            $uid = $verifiedIdToken->claims()->get('sub');
            $user = $this->firebaseAuth->getUser($uid);

            // insertion de l'utilisateur, si l'utilisateur n'existe pas
            // $userService->createUser($user);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Utilisateur authentifié avec succès',
                'data' => [
                    'uid' => $user->uid,
                    'email' => $user->email,
                    'displayName' => $user->displayName,
                ],
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid ID token',
                'error' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}