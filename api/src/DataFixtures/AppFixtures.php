<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Story\DefaultStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        DefaultStory::load();
        $this->loadCategories($manager);
    }

    private function loadCategories(ObjectManager $manager): void
    {
        $categoryFixtures = new CategoryFixtures();
        $categoryFixtures->load($manager);
    }
}
