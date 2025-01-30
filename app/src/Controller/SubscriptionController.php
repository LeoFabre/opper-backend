<?php

namespace App\Controller;

use App\DTO\SubscriptionDTO;
use App\Repository\ContactRepository;
use App\Repository\SubscriptionRepository;
use App\Service\SubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

final class SubscriptionController extends AbstractController
{
    #[OA\Get(
        path: '/subscription/{idContact}',
        summary: 'Get subscriptions by contact ID',
        tags: ['subscriptions'],
        parameters: [
            new OA\Parameter(
                name: 'idContact',
                description: 'The ID of the contact',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of subscriptions',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'subscriptionId', type: 'integer'),
                            new OA\Property(property: 'subscriptionProductId', type: 'integer'),
                            new OA\Property(property: 'subscriptionProductLabel', type: 'string'),
                            new OA\Property(property: 'subscriptionStartDate', type: 'string', format: 'date'),
                            new OA\Property(property: 'subscriptionEndDate', type: 'string', format: 'date')
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Contact not found'
            )
        ]
    )]
    #[Route('/subscription/{idContact}', name: 'get_subscriptions_by_contact', methods: ['GET'])]
    public function getByContactID(int $idContact, ContactRepository $contactRepository): JsonResponse
    {
        $contact = $contactRepository->find($idContact);

        if (!$contact) {
            return new JsonResponse(['message' => 'Contact not found'], Response::HTTP_NOT_FOUND);
        }

        $subscriptions = $contact->getSubscriptions();
        $subscriptionsArray = $subscriptions->map(fn($subscription) => [
            'subscriptionId' => $subscription->getId(),
            'subscriptionProductId' => $subscription->getProduct()->getId(),
            'subscriptionProductLabel' => $subscription->getProduct()->getLabel(),
            'subscriptionStartDate' => $subscription->getBeginDate()->format('Y-m-d'),
            'subscriptionEndDate' => $subscription->getEndDate()->format('Y-m-d'),
        ])->toArray();

        return $this->json($subscriptionsArray);
    }

    #[OA\Post(
        path: '/subscription/',
        summary: 'Create a new subscription',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: SubscriptionDTO::class), type: 'object')
        ),
        tags: ['subscriptions'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Subscription created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Subscription created successfully'),
                        new OA\Property(property: 'subscriptionId', type: 'integer', example: 123)
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'string'))
                    ]
                )
            )
        ]
    )]
    #[Route('/subscription/', name: 'add_subscription', methods: ['POST'])]
    public function post(
        Request             $request,
        ValidatorInterface  $validator,
        SubscriptionService $subscriptionService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new SubscriptionDTO($data);

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        return $subscriptionService->createSubscription($dto);
    }

    #[OA\Put(
        path: '/subscription/{idSubscription}',
        summary: 'Update an existing subscription',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: SubscriptionDTO::class), type: 'object')
        ),
        tags: ['subscriptions'],
        parameters: [
            new OA\Parameter(
                name: 'idSubscription',
                description: 'The ID of the subscription to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Subscription updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Subscription updated successfully')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 404,
                description: 'Subscription not found'
            )
        ]
    )]
    #[Route('/subscription/{idSubscription}', name: 'update_subscription', methods: ['PUT'])]
    public function update(
        int                    $idSubscription,
        Request                $request,
        SubscriptionRepository $subscriptionRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface     $validator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $subscription = $subscriptionRepository->find($idSubscription);
        if (!$subscription) {
            return new JsonResponse(['message' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        $dto = new SubscriptionDTO($data);

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $beginDate = \DateTime::createFromFormat('Y-m-d', $dto->beginDate);
        $endDate = \DateTime::createFromFormat('Y-m-d', $dto->endDate);

        if (!$beginDate || !$endDate) {
            return new JsonResponse(['message' => 'Invalid date format (expected Y-m-d)'], Response::HTTP_BAD_REQUEST);
        }

        $subscription->setBeginDate($beginDate);
        $subscription->setEndDate($endDate);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Subscription updated successfully']);
    }

    #[OA\Delete(
        path: '/subscription/{idSubscription}',
        summary: 'Delete an existing subscription',
        tags: ['subscriptions'],
        parameters: [
            new OA\Parameter(
                name: 'idSubscription',
                description: 'The ID of the subscription to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Subscription deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Subscription deleted successfully')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Subscription not found'
            )
        ]
    )]
    #[Route('/subscription/{idSubscription}', name: 'delete_subscription', methods: ['DELETE'])]
    public function delete(
        int                    $idSubscription,
        SubscriptionRepository $subscriptionRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $subscription = $subscriptionRepository->find($idSubscription);
        if (!$subscription) {
            return new JsonResponse(['message' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($subscription);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Subscription deleted successfully']);
    }

}
