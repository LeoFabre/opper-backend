<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class SubscriptionDTO
{
    #[Assert\NotBlank]
    #[Assert\Type("string")]
    public string $contactName;

    #[Assert\NotBlank]
    #[Assert\Type("string")]
    public string $contactFirstName;

    #[Assert\NotBlank]
    #[Assert\Type("integer")]
    #[Assert\Positive]
    public int $productId;

    #[Assert\NotBlank]
    #[Assert\Date]
    public string $beginDate;

    #[Assert\NotBlank]
    #[Assert\Date]
    public string $endDate;

    public function __construct(array $data)
    {
        $this->contactName = $data['contactName'] ?? '';
        $this->contactFirstName = $data['contactFirstName'] ?? '';
        $this->productId = $data['productId'] ?? 0;
        $this->beginDate = $data['beginDate'] ?? '';
        $this->endDate = $data['endDate'] ?? '';
    }
}
