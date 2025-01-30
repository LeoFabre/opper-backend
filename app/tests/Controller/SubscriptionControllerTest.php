<?php

namespace App\Tests\Controller;

use App\Controller\SubscriptionController;
use App\Entity\Contact;
use App\Entity\Product;
use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;


#[CoversClass(SubscriptionController::class)]
class SubscriptionControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        // Supprimer et recréer les tables
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        if (!empty($metadata)) {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }

        // Charger les fixtures avant chaque test
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $command = $application->find('doctrine:fixtures:load');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--env' => 'test', '--no-interaction' => true]);

        $this->entityManager->clear();

        $contact = new Contact();
        $contact->setName('Doe')->setFirstname('John');
        $this->entityManager->persist($contact);

        $product = new Product();
        $product->setLabel('Test Product');
        $this->entityManager->persist($product);

        $subscription = new Subscription();
        $subscription->setContact($contact);
        $subscription->setProduct($product);
        $subscription->setBeginDate(new \DateTime('2024-01-01'));
        $subscription->setEndDate(new \DateTime('2024-12-31'));
        $this->entityManager->persist($subscription);

        $this->entityManager->flush();
    }

    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    public function testGetSubscriptionsByContactNotFound(): void
    {
        $this->ensureKernelShutdown();
        $client = static::createClient();

        $client->request('GET', '/subscription/999');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testCreateSubscription(): void
    {
        $this->ensureKernelShutdown();
        $client = static::createClient();

        $client->request('POST', '/subscription/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'contactName' => 'Doe',
            'contactFirstName' => 'John',
            'productId' => 1,
            'beginDate' => '2024-01-01',
            'endDate' => '2024-12-31'
        ]));

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testUpdateSubscription(): void
    {
        $this->ensureKernelShutdown();
        $client = static::createClient();

        $client->request('POST', '/subscription/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'contactName' => 'Doe',
            'contactFirstName' => 'John',
            'productId' => 1,
            'beginDate' => '2024-01-01',
            'endDate' => '2024-12-31'
        ]));
        $retrievedSubId = json_decode($client->getResponse()->getContent(), true)['subscriptionId'];
        $client->request('PUT', "/subscription/$retrievedSubId", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'beginDate' => '2024-02-01',
            'endDate' => '2024-12-31'
        ]));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testDeleteSubscription(): void
    {
        $this->ensureKernelShutdown();
        $client = static::createClient();

        $client->request('DELETE', '/subscription/1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
