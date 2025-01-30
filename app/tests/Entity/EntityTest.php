<?php

namespace App\Tests\Entity;

use App\Entity\Contact;
use App\Entity\Product;
use App\Entity\Subscription;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Subscription::class)]
#[CoversClass(Contact::class)]
#[CoversClass(Product::class)]
class EntityTest extends TestCase
{

    public function testContactEntity(): void
    {
        $contact = new Contact();
        $contact->setName('Doe');
        $contact->setFirstname('John');

        $this->assertEquals('Doe', $contact->getName());
        $this->assertEquals('John', $contact->getFirstname());
    }

    public function testProductEntity(): void
    {
        $product = new Product();
        $product->setLabel('Test Product');

        $this->assertEquals('Test Product', $product->getLabel());
    }

    public function testSubscriptionEntity(): void
    {
        $contact = new Contact();
        $contact->setName('Doe')->setFirstname('John');

        $product = new Product();
        $product->setLabel('Test Product');

        $subscription = new Subscription();
        $subscription->setContact($contact);
        $subscription->setProduct($product);
        $subscription->setBeginDate(new \DateTime('2024-01-01'));
        $subscription->setEndDate(new \DateTime('2024-12-31'));

        $this->assertEquals($contact, $subscription->getContact());
        $this->assertEquals($product, $subscription->getProduct());
        $this->assertEquals('2024-01-01', $subscription->getBeginDate()->format('Y-m-d'));
        $this->assertEquals('2024-12-31', $subscription->getEndDate()->format('Y-m-d'));
    }
}
