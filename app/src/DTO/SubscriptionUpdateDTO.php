<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class SubscriptionUpdateDTO
{
    #[Assert\Type("string")]
    public string $contactName;

    #[Assert\Type("string")]
    public string $contactFirstName;

    #[Assert\Type("integer")]
    #[Assert\GreaterThanOrEqual(0)]
    public int $productId;

    #[Assert\Date]
    public string $beginDate;

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
