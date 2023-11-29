<?php

namespace App\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;


trait DescriptionTrait
{


    #[ORM\ManyToOne(targetEntity: MediaInterface::class)]
    #[ORM\JoinColumn(onDelete :"SET NULL")]
    private ?MediaInterface $thumbnail = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of thumbnail
     *
     * @return  MediaInterface|null
     */ 
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function getThumbnailData(): ?array
    {
        if (!$this->thumbnail) {
            return null;
        }

        return [
            'id' => $this->thumbnail->getId(),
        ];
    }


    /**
     * Set the value of thumbnail
     *
     * @param  MediaInterface|null  $thumbnail
     *
     */ 
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
