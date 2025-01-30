<?php

namespace App\Controller;

use App\DTO\SubscriptionDTO;
use App\DTO\SubscriptionUpdateDTO;
use App\Service\SubscriptionService;
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
    public function getByContactID(
        int                 $idContact,
        SubscriptionService $subscriptionService
    ): JsonResponse
    {
        return $subscriptionService->findByContactID($idContact);
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

        return $subscriptionService->create($dto);
    }

    #[OA\Put(
        path: '/subscription/{idSubscription}',
        summary: 'Update an existing subscription',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: SubscriptionUpdateDTO::class), type: 'object')
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
        int                 $idSubscription,
        Request             $request,
        ValidatorInterface  $validator,
        SubscriptionService $subscriptionService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new SubscriptionUpdateDTO($data);

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        return $subscriptionService->update($idSubscription, $dto);
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
        int                 $idSubscription,
        SubscriptionService $subscriptionService
    ): JsonResponse
    {
        return $subscriptionService->delete($idSubscription);
    }
}
