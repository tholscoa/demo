<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Enum\BookCondition;
use App\Enum\PromotionStatus;
use App\Repository\BookRepository;
use App\State\Processor\BookPersistProcessor;
use App\State\Processor\BookRemoveProcessor;
use App\Validator\BookUrl;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A book.
 *
 * @see https://schema.org/Book
 */
#[ApiResource(
    uriTemplate: '/admin/books{._format}',
    types: ['https://schema.org/Book', 'https://schema.org/Offer'],
    operations: [
        new GetCollection(
            itemUriTemplate: '/admin/books/{id}{._format}',
            paginationClientItemsPerPage: true
        ),
        new Post(
            processor: BookPersistProcessor::class,
            itemUriTemplate: '/admin/books/{id}{._format}'
        ),
        new Get(
            uriTemplate: '/admin/books/{id}{._format}'
        ),
        // https://github.com/api-platform/admin/issues/370
        new Put(
            uriTemplate: '/admin/books/{id}{._format}',
            processor: BookPersistProcessor::class
        ),
        new Delete(
            uriTemplate: '/admin/books/{id}{._format}',
            processor: BookRemoveProcessor::class
        ),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Book:read:admin', 'Enum:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Book:write'],
    ],
    collectDenormalizationErrors: true,
    security: 'is_granted("OIDC_ADMIN")',
    mercure: [
        'topics' => [
            '@=iri(object, ' . UrlGeneratorInterface::ABS_URL . ', get_operation(object, "/admin/books/{id}{._format}"))',
            '@=iri(object, ' . UrlGeneratorInterface::ABS_URL . ', get_operation(object, "/books/{id}{._format}"))',
        ],
    ]
)]
#[ApiResource(
    types: ['https://schema.org/Book', 'https://schema.org/Offer'],
    operations: [
        new GetCollection(
            itemUriTemplate: '/books/{id}{._format}'
        ),
        new Get(),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Book:read', 'Enum:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    mercure: [
        'topics' => [
            '@=iri(object, ' . UrlGeneratorInterface::ABS_URL . ', get_operation(object, "/admin/books/{id}{._format}"))',
            '@=iri(object, ' . UrlGeneratorInterface::ABS_URL . ', get_operation(object, "/books/{id}{._format}"))',
        ],
    ]
)]
#[ORM\Entity(repositoryClass: BookRepository::class)]
#[UniqueEntity(fields: ['book'])]
class Book
{
    /**
     * @see https://schema.org/identifier
     */
    #[ApiProperty(identifier: true, types: ['https://schema.org/identifier'])]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    private ?Uuid $id = null;

    /**
     * @see https://schema.org/itemOffered
     */
    #[ApiProperty(
        types: ['https://schema.org/itemOffered', 'https://purl.org/dc/terms/BibliographicResource'],
        example: 'https://openlibrary.org/books/OL2055137M.json'
    )]
    #[Assert\NotBlank(allowNull: false)]
    #[Assert\Url(protocols: ['https'], requireTld: true)]
    #[BookUrl]
    #[Groups(groups: ['Book:read', 'Book:read:admin', 'Bookmark:read', 'Book:write'])]
    #[ORM\Column(unique: true)]
    public ?string $book = null;

    /**
     * @see https://schema.org/name
     */
    #[ApiFilter(OrderFilter::class)]
    #[ApiFilter(SearchFilter::class, strategy: 'i' . SearchFilterInterface::STRATEGY_PARTIAL)]
    #[ApiProperty(
        iris: ['https://schema.org/name'],
        example: 'Hyperion'
    )]
    #[Groups(groups: ['Book:read', 'Book:read:admin', 'Bookmark:read', 'Review:read:admin'])]
    #[ORM\Column(type: Types::TEXT)]
    public ?string $title = null;

    /**
     * @see https://schema.org/author
     */
    #[ApiFilter(SearchFilter::class, strategy: 'i' . SearchFilterInterface::STRATEGY_PARTIAL)]
    #[ApiProperty(
        types: ['https://schema.org/author'],
        example: 'Dan Simmons'
    )]
    #[Groups(groups: ['Book:read', 'Book:read:admin', 'Bookmark:read', 'Review:read:admin'])]
    #[ORM\Column(nullable: true)]
    public ?string $author = null;

    /**
     * @see https://schema.org/OfferItemCondition
     */
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[ApiProperty(
        types: ['https://schema.org/OfferItemCondition'],
        example: BookCondition::NewCondition->value
    )]
    #[Assert\NotNull]
    #[Groups(groups: ['Book:read', 'Book:read:admin', 'Bookmark:read', 'Book:write'])]
    #[ORM\Column(name: '`condition`', type: 'string', enumType: BookCondition::class)]
    public ?BookCondition $condition = null;

    /**
     * An IRI of reviews.
     *
     * @var Collection<int, Review>
     *
     * @see https://schema.org/reviews
     */
    #[ApiProperty(
        types: ['https://schema.org/reviews'],
        example: '/books/6acacc80-8321-4d83-9b02-7f2c7bf6eb1d/reviews',
        uriTemplate: '/books/{bookId}/reviews{._format}'
    )]
    #[Groups(groups: ['Book:read', 'Bookmark:read'])]
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'book')]
    public Collection $reviews;

    /**
     * The overall rating, based on a collection of reviews or ratings, of the item.
     *
     * @see https://schema.org/aggregateRating
     */
    #[ApiProperty(
        types: ['https://schema.org/aggregateRating'],
        example: 1
    )]
    #[Groups(groups: ['Book:read', 'Book:read:admin', 'Bookmark:read'])]
    public ?int $rating = null;

    //add field is_promoted.
    #[ApiProperty(
        example: false,
        description: 'add field book is_promoted'
    )]
    #[Groups(groups: ['Book:read', 'Book:read:admin', 'Book:write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isPromoted = false;

    // // Change the field name and type to use a string enum-like approach
    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={PromotionStatus::None, PromotionStatus::Basic, PromotionStatus::Pro})
     * @ApiProperty(
     *     description="The promotion status of the book",
     *     example="Pro"
     * )
     * @Groups(groups={"Book:read", "Book:read:admin", "Book:write"})
     * @Security("is_granted('ROLE_OIDC_ADMIN')")
     * */
    #[ORM\Column(type: 'string', enumType: PromotionStatus::class)] // Define enum column type
    private PromotionStatus $promotionStatus;

    //add field slug.
    /**
     * @Assert\NotBlank(message="Slug cannot be blank")
     * @Assert\Length(min=5, minMessage="Slug must be at least 5 characters long")
     * @Assert\Regex(
     *     pattern="/^[a-z0-9-]+$/",
     *     message="Slug must contain only lowercase letters, numbers, and hyphens"
     * )
     * @ApiProperty(
     *     description="SEO-friendly URL slug for the book",
     *     example="book-1",
     * )
     * @Groups(groups={"Book:read", "Book:write"})
     */
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="books")
     * @ORM\JoinTable(name="book_categories")
     */
    private $categories;
    private $bookmarks;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->bookmarks = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    // Getter and setter for `author`
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    // Getter and setter for `title`
    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    // Getter and setter for `reviews`
    public function getReviews(): ?collection
    {
        return $this->reviews;
    }

    public function setReviews(collection $reviews): self
    {
        $this->reviews = $reviews;

        return $this;
    }

    // Getter and setter for `isPromoted`
    public function getIsPromoted(): bool
    {
        return $this->isPromoted;
    }

    public function setIsPromoted(bool $isPromoted): self
    {
        $this->isPromoted = $isPromoted;
        $this->updatePromotionStatus(); // Automatically update `promotionStatus`

        return $this;
    }

    // Getter and setter for `promotionStatus`
    public function getPromotionStatus(): PromotionStatus
    {
        return $this->promotionStatus;
    }

    public function setPromotionStatus(PromotionStatus $promotionStatus): self
    {
        $this->promotionStatus = $promotionStatus;

        return $this;
    }

    // Helper method to convert isPromoted to promotionStatus
    private function updatePromotionStatus(): void
    {
        if ($this->isPromoted) {
            $this->promotionStatus = 'Basic';
        } else {
            $this->promotionStatus = 'None';
        }
    }

    // Getter and setter for `slug`
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    // generate a slug from the book ID
    public function generateSlug(): void
    {
        $this->slug = 'book-' . $this->getId();
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getBookmarks(): Collection
    {
        return $this->bookmarks;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

}
