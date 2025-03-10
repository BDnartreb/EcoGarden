<?php

namespace App\Entity;

use App\Repository\TipRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TipRepository::class)]
class Tip
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:'Le conseil doit comporter un titre.')]
    #[Groups(['getMonthTips', 'getTipList', 'getDetailTip', 'createTip'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message:'Le conseil est visiblement confidentiel. Rien n\'a été renseigné.')]
    #[Groups(['getMonthTips', 'getTipList', 'getDetailTip', 'createTip'])]
    private ?string $content = null;

    /**
     * @var Collection<int, Month>
     */
    //#[ORM\ManyToMany(targetEntity: Month::class, inversedBy: 'tips', cascade: ['persist'])]
    #[ORM\ManyToMany(targetEntity: Month::class, inversedBy: 'tips')]
    //#[Groups(['createTip'])]
    private Collection $months;

    public function __construct()
    {
        $this->months = new ArrayCollection();
    }

   /* #[ORM\Column]
    #[Assert\NotBlank(message:'Un conseil doit être rattaché à un mois.')]
    #[Assert\Range(min:1, max:12, notInRangeMessage:"Le mois doit être indiqué par un entier compris entre {{ min }} et {{ max }}.")]
    private ?int $month = null;
    */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection<int, Month>
     */
    public function getMonths(): Collection
    {
        return $this->months;
    }

    public function addMonth(Month $month): static
    {
        if (!$this->months->contains($month)) {
            $this->months->add($month);
        }

        return $this;
    }

    public function removeMonth(Month $month): static
    {
        $this->months->removeElement($month);

        return $this;
    }

}
