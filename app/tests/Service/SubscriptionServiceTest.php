<?php

namespace App\Tests\Service;

use App\Repository\ContactRepository;
use App\Repository\ProductRepository;
use App\Repository\SubscriptionRepository;
use App\Service\SubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

#[CoversClass(SubscriptionService::class)]
class SubscriptionServiceTest extends TestCase
{
    private ContactRepository $contactRepository;
    private ProductRepository $productRepository;
    private SubscriptionRepository $subscriptionRepository;
    private EntityManagerInterface $entityManager;
    private SubscriptionService $subscriptionService;

    protected function setUp(): void
    {
        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->subscriptionService = new SubscriptionService(
            $this->contactRepository,
            $this->productRepository,
            $this->subscriptionRepository,
            $this->entityManager
        );
    }

    public function testFindByContactIDNotFound(): void
    {
        $this->contactRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $response = $this->subscriptionService->findByContactID(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
