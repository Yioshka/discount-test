<?php

declare(strict_types=1);

namespace App\Controller\Api\V1\Price;

use App\Service\CalculatePriceService;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/api/v1/')]
final class Action
{
    #[Route(path: 'price/calculate', name: 'api_v1_price_calculate', methods: ['POST'])]
    public function __invoke(
        CalculatePriceService $calculatePriceService,
        #[MapRequestPayload]
        Request $request,
    ): JsonResponse {
        $price = $calculatePriceService->calculateWithDiscount(
            price: $request->price,
            birthDate: Carbon::parse($request->birthDate),
            startDate: is_string($request->startDate) ? Carbon::parse($request->startDate) : Carbon::now()->startOfDay(),
            paymentDate: is_string($request->paymentDate) ? Carbon::parse($request->paymentDate) : null
        );

        return new JsonResponse([
            'success' => true,
            'data' => [
                'price' => $price,
            ],
            'message' => null,
        ], Response::HTTP_OK);
    }
}
