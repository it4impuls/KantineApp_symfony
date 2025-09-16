<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $order_date = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Costumer $Costumer = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2)]
    private ?string $ordered_item = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $tax = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderDate(): ?\DateTime
    {
        return $this->order_date;
    }

    public function setOrderDate(\DateTime $order_date): static
    {
        $this->order_date = $order_date;

        return $this;
    }

    public function getCostumer(): ?Costumer
    {
        return $this->Costumer;
    }

    public function setCostumer(?Costumer $Costumer): static
    {
        $this->Costumer = $Costumer;

        return $this;
    }

    public function getOrderedItem(): ?string
    {
        return $this->ordered_item;
    }

    public function setOrderedItem(string $ordered_item): static
    {
        $this->ordered_item = $ordered_item;

        return $this;
    }

    public function getTax(): ?int
    {
        return $this->tax;
    }

    public function setTax(int $tax): static
    {
        $this->tax = $tax;

        return $this;
    }
}
