<?php 

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\GameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Query\AST\Join;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game 
{
    #[ORM\Id]
    #[ORM\GeneratedValue()]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank()]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description;

    #[ORM\Column]
    private bool $enabled = false;

    /*
    Annotation pour PHP < 8
    @ORM\ManyToOne(targetEntity=Editor::class, inversedBy="games")
    */
    #[ORM\ManyToOne(inversedBy: 'games')]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    private ?Editor $editor = null;

    /*#[ORM\Column]
    private int $releaseYear;*/

    #[ORM\Column]
    private \DateTime $releaseDate;

    // orphanRemoval: Supprime automatiquement l'image si elle n'a plus de lien avec le jeu
    #[ORM\OneToOne(cascade: ['persist', 'remove'], orphanRemoval:true)]
    private ?Image $mainImage = null;

    private bool $deleteMainImage;

    #[ORM\ManyToOne(inversedBy: 'games')]
    private ?User $author = null;

    public function __construct()
    {
        $this->releaseDate = new \DateTime;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title ?? ''; // $this->title != null ? $this->title : ''
    }

    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription($description): self
    {
        $this->description = strip_tags($description, ['div', 'p', 'strong', 'a']);

        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getEditor(): ?Editor
    {
        return $this->editor;
    }

    public function setEditor(?Editor $editor): self
    {
        $this->editor = $editor;

        return $this;
    }

    public function getReleaseDate(): \DateTime
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTime $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getMainImage(): ?Image
    {
        return $this->mainImage;
    }

    public function setMainImage(?Image $mainImage): self
    {
        if ($mainImage !== null && $mainImage->getPath() !== null) {
            $this->mainImage = $mainImage;
        }
        
        return $this;
    }

    public function getDeleteMainImage(): bool
    {
        return $this->deleteMainImage ?? false;
    }

    public function setDeleteMainImage(bool $deleteMainImage): self
    {
        $this->deleteMainImage = $deleteMainImage;

        if ($this->deleteMainImage) {
            $this->mainImage = null; // Supprime l'objet Image
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}