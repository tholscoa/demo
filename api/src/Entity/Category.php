<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * 
 * @ApiResource(
 *     security="is_granted('ROLE_ADMIN')"
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */

class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
