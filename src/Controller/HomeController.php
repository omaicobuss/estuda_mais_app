<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController
{
    #[Route('/', methods: ['GET'])]
    public function index(Request $request): RedirectResponse
    {
        return new RedirectResponse($this->appPath($request));
    }

    #[Route('/app', methods: ['GET'])]
    public function appEntry(Request $request): RedirectResponse
    {
        return new RedirectResponse($this->appPath($request));
    }

    private function appPath(Request $request): string
    {
        $basePath = rtrim($request->getBasePath(), '/');

        return $basePath . '/app/';
    }
}
