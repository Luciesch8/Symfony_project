<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
// Indique à Doctrine que cette entitée posséde des événements (cycle de vie)
#[ORM\HasLifecycleCallbacks]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(length: 80)]
    private ?string $name = null;

    #[Assert\Image(maxSize: '3M')]
    private UploadedFile $file;

    private string $oldPath = "";

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
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

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file): self
    {
        $this->file = $file;
        $this->oldPath = $this->path ?? ''; // Sauvegarde le chemin de l'ancien fichier pour le supprimer lors de l'upload du nouveau
        $this->path = ''; // Modifier cette valeur pour forcer Doctrine à modifier l'entité

        return $this;
    }

    /**
     * Génération d'un nom de fichier pour éviter les doublons
     * Cette méthode est appelée automatiquement avant un persist et un update
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function generatePath(): self 
    {
        // Si un fichier a bien été envoyé
        if ($this->file instanceof UploadedFile) {
            // Génére un chemin 
            $this->path = uniqid("img_"); //.'.'.$this->file->guessExtension();
            $this->name = $this->file->getClientOriginalName();
        }

        return $this;
    }

    /**
     * Retourne le lien absolu vers le dossier d'upload
     */
    public static function getPublicRootDir(): string 
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
    }

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function upload(): void 
    {
        // Supprimer l'ancien fichier
        if (is_file(self::getPublicRootDir().$this->oldPath)) {
            unlink(self::getPublicRootDir().$this->oldPath);
        }

        if ($this->file instanceof UploadedFile) {
            $this->file->move(self::getPublicRootDir(), $this->path);
        }
    }

    public function getWebPath(): string 
    {
        return '/uploads/'. $this->path;
    }

    // Supprime le fichier avant de supprimer l'entitée de la db
    #[ORM\PreRemove]
    public function removeFile(): void 
    {
        if (is_file(self::getPublicRootDir().$this->path)) {
            unlink(self::getPublicRootDir().$this->path);
        }
    }
}
