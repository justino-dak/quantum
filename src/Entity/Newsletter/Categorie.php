<?php

namespace App\Entity\Newsletter;

use App\Entity\Newsletter\User;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Newsletter\Newsletter;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\Newsletter\CategorieRepository;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    const RESOURCE_KEY = 'newslettercategories';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column( length:255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity:User::class,inversedBy:"categories")]
    private $users;

    #[ORM\ManyToMany(targetEntity:Newsletter::class,inversedBy:"categories", orphanRemoval:true)]
    private $newsletters;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->newsletters = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Users>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, Newsletters>
     */
    public function getNewsletters(): Collection
    {
        return $this->newsletters;
    }

    public function addNewsletter(Newsletter $newsletter): self
    {
        if (!$this->newsletters->contains($newsletter)) {
            $this->newsletters[] = $newsletter;
            $newsletter->setCategorie($this);
        }

        return $this;
    }

    public function removeNewsletter(Newsletter $newsletter): self
    {
        if ($this->newsletters->removeElement($newsletter)) {
            // set the owning side to null (unless already changed)
            if ($newsletter->getCategorie() === $this) {
                $newsletter->setCategorie(null);
            }
        }

        return $this;
    }
}
