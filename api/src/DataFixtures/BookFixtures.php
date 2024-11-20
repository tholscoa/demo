<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Enum\PromotionStatus;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory as Faker;

class BookFixtures extends Fixture
{
    private $faker;

    public function __construct()
    {
        $this->faker = Faker::create();  // Create a Faker instance to generate fake data
    }
    
    public function load(ObjectManager $manager)
    {
        // Update with new fields
        $book = new Book();
        $book->setPromotionStatus(PromotionStatus::None);
        $book->setSlug('book-' . $book->getId());
        $manager->persist($book);
    }

}
