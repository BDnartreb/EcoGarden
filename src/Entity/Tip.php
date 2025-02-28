<?php

namespace App\Entity;

use App\Repository\TipRepository;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\PseudoTypes\IntegerValue;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TipRepository::class)]
class Tip
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:'Le conseil doit comporter un titre.')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message:'Le conseil est visiblement confidentiel. Rien n\'a été renseigné.')]
    private ?string $content = null;

    #[ORM\Column]
    #[Assert\NotBlank(message:'Un conseil doit être rattaché à un mois.')]
    #[Assert\Range(min:1, max:12, notInRangeMessage:"Le mois doit être indiqué par un entier compris entre {{ min }} et {{ max }}.")]

    private ?int $month = null;

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

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(int $month): static
    {
        $this->month = $month;

        return $this;
    }
}
