<?php
namespace App\DataFixtures;

use App\Entity\Contact;
use App\Entity\Product;
use App\Entity\Subscription;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer des contacts
        $contacts = [];
        for ($i = 1; $i <= 5; $i++) {
            $contact = new Contact();
            $contact->setName("Name$i");
            $contact->setFirstname("Firstname$i");
            $manager->persist($contact);
            $contacts[] = $contact;
        }

        // Créer des produits
        $products = [];
        for ($i = 1; $i <= 5; $i++) {
            $product = new Product();
            $product->setLabel("Product$i");
            $manager->persist($product);
            $products[] = $product;
        }

        $manager->flush();

        // Créer des subscriptions (1 produit par contact)
        foreach ($contacts as $index => $contact) {
            // On attribue un produit unique à chaque contact
            $product = $products[$index % count($products)];
            $subscription = new Subscription();
            $subscription->setContact($contact);
            $subscription->setProduct($product);
            $subscription->setBeginDate(new DateTime('2025-01-01'));
            $subscription->setEndDate(new DateTime('2025-12-31'));
            $manager->persist($subscription);
        }

        $manager->flush();
    }
}
