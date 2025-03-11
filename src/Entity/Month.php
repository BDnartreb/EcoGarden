<?php

namespace App\Entity;

use App\Repository\MonthRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MonthRepository::class)]
class Month
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['getMonthTips', 'getTipList', 'getDetailTip'])]
    private ?int $month = null;

    /**
     * @var Collection<int, Tip>
     */
    #[ORM\ManyToMany(targetEntity: Tip::class, mappedBy: 'months')]
    #[Groups(['getMonthTips'])]
    private Collection $tips;

    public function __construct()
    {
        $this->tips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Tip>
     */
    public function getTips(): Collection
    {
        return $this->tips;
    }

    public function addTip(Tip $tip): static
    {
        if (!$this->tips->contains($tip)) {
            $this->tips->add($tip);
        }

        return $this;
    }

    public function removeTip(Tip $tip): static
    {
        $this->tips->removeElement($tip);

        return $this;
    }
}
