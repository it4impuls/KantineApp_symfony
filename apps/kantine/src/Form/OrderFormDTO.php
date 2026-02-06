<?php


namespace Kantine\Form;


class OrderFormDTO
{
    public ?int $id = null;
    public ?\DateTime $order_date = null;
    public ?string $Costumer = null;
    public ?string $ordered_item = null;
    public ?int $tax = null;
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

    public function getCostumer(): ?string
    {
        return $this->Costumer;
    }

    public function setCostumer(?string $Costumer): static
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
