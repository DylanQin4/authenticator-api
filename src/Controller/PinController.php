<?php

namespace App\Controller;

use App\Service\PinService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PinController
{
    private $pinService;

    public function __construct(PinService $pinService)
    {
        $this->pinService = $pinService;
    }

    #[Route('/api/generate-pin', name: 'api_generate_pin', methods: ['GET'])]
    public function generatePin(): JsonResponse
    {
        // Appeler la méthode du service pour générer un PIN
        $pin = $this->pinService->generatePin();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Le code PIN a été généré avec succès.',
            'pin' => $pin->getCodePin(),
            'expiration' => $pin->getExpiratedAt()->format('Y-m-d H:i:s')
        ], Response::HTTP_OK);
    }
}
