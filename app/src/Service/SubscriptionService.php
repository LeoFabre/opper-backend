<?php
namespace App\Service;

use App\DTO\SubscriptionDTO;
use App\Entity\Contact;
use App\Entity\Subscription;
use App\Repository\ContactRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionService
{
    public function __construct(
        private ContactRepository $contactRepository,
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function createSubscription(SubscriptionDTO $dto): JsonResponse
    {
        $contact = $this->contactRepository->findOneBy([
            'name' => $dto->contactName,
            'firstname' => $dto->contactFirstName,
        ]);

        if (!$contact) {
            $contact = new Contact();
            $contact->setName($dto->contactName);
            $contact->setFirstname($dto->contactFirstName);
            $this->entityManager->persist($contact);
        }

        $product = $this->productRepository->find($dto->productId);
        if (!$product) {
            return new JsonResponse(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $subscription = new Subscription();
        $subscription->setContact($contact);
        $subscription->setProduct($product);
        $subscription->setBeginDate(\DateTime::createFromFormat('Y-m-d', $dto->beginDate));
        $subscription->setEndDate(\DateTime::createFromFormat('Y-m-d', $dto->endDate));

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Subscription created successfully',
            'subscriptionId' => $subscription->getId(),
            'userId' => $contact->getId(),
        ], Response::HTTP_CREATED);
    }

    public function updateSubscription(Subscription $subscription, SubscriptionDTO $dto): JsonResponse
    {
        $beginDate = \DateTime::createFromFormat('Y-m-d', $dto->beginDate);
        $endDate = \DateTime::createFromFormat('Y-m-d', $dto->endDate);

        if (!$beginDate || !$endDate) {
            return new JsonResponse(['message' => 'Invalid date format (expected Y-m-d)'], Response::HTTP_BAD_REQUEST);
        }

        $subscription->setBeginDate($beginDate);
        $subscription->setEndDate($endDate);

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Subscription updated successfully']);
    }
}
