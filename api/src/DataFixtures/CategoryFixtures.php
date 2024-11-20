<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CategoryFixtures extends Fixture
{
        
    public function load(ObjectManager $manager): void
    {
        // Creating sample categories
        $category1 = new Category();
        $category1->setName('News');
        $manager->persist($category1);

        $category2 = new Category();
        $category2->setName('Science');
        $manager->persist($category2);

        $category3 = new Category();
        $category3->setName('History');
        $manager->persist($category3);

        $manager->flush();
    }

}
