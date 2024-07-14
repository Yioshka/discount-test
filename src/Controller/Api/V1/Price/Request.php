<?php

declare(strict_types=1);

namespace App\Controller\Api\V1\Price;

use Symfony\Component\Validator\Constraints as Assert;

final class Request
{
    public function __construct(
        #[Assert\NotBlank(message: 'price cannot be blank')]
        #[Assert\Positive(message: 'price must be positive')]
        #[Assert\Type(type: 'float', message: 'price must be a number')]
        public float $price = 0.0,
        #[Assert\NotBlank(message: 'birthdate cannot be blank')]
        #[Assert\Regex(pattern: '/^\d{2}\.\d{2}\.\d{4}$/', message: 'birthdate must be a valid date. Format: dd.mm.yyyy')]
        public ?string $birthDate = null,
        #[Assert\Regex(pattern: '/^\d{2}\.\d{2}\.\d{4}$/', message: 'startDate must be a valid date. Format: dd.mm.yyyy')]
        public ?string $startDate = null,
        #[Assert\Regex(pattern: '/^\d{2}\.\d{2}\.\d{4}$/', message: 'paymentDate must be a valid date. Format: dd.mm.yyyy')]
        public ?string $paymentDate = null
    ) {
    }
}
