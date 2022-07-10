<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController extends AbstractController implements
    TokenAuthenticatedController
{
    #[Route('/api/', name: 'api_home', methods: 'GET')]
    public function index(
        Request $request
    ): JsonResponse
    {
        return new JsonResponse(
            [
                'userId' => $request->attributes->get('userId'),
                'userName' => $request->attributes->get('userName')
            ],
            Response::HTTP_OK
        );
    }
}