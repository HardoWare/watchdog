<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WatcherApiController extends AbstractController
{
    #[Route('/watcher/api', name: 'app_watcher_api')]
    public function index(Request $request): JsonResponse
    {
        
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/WatcherApiController.php',
        ]);
    }
}
