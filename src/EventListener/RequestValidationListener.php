<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestValidationListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->getPathInfo() === '/api/login_check' && $request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['password']) || $data['password'] === '') {
                $event->setResponse(new JsonResponse([
                    'status' => 'error',
                    'message' => 'Le mot de passe est requis',
                ], Response::HTTP_BAD_REQUEST));
                return;
            }

            if (!isset($data['email']) || $data['email'] === '') {
                $event->setResponse(new JsonResponse([
                    'status' => 'error',
                    'message' => 'L\'email est requis',
                ], Response::HTTP_BAD_REQUEST));
                return;
            }
        }
    }
}
