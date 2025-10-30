<?php

namespace App\Entity;

use App\Repository\CostumerRepository;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CostumerRepository::class)] #
#[UniqueEntity(
    fields: ['firstname', 'lastname'],
    message: "A costumer with this name already exists"
)]
class Costumer
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    private ?string $lastname = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $enddate = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'costumer')]
    private Collection $orders;

    protected File $Barcode;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Department = null;

    public function __tostring()
    {
        return (string)$this->id;
    }

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $now = new DateTime();
        $this->enddate = $now->add(new DateInterval("P4Y")); // 4 Years
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        // trimmed and 1st letter capitalized
        return ucwords(trim($this->firstname));
    }

    public function setFirstname(string $firstname): static
    {
        // trimmed and lowercase
        strtolower(trim($this->firstname = $firstname));

        return $this;
    }

    public function getLastname(): ?string
    {
        // trimmed and 1st letter capitalized
        return  ucwords(trim($this->lastname));
    }

    public function setLastname(string $lastname): static
    {
        // trimmed and lowercase
        strtolower(trim($this->lastname = $lastname));

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        $now = new DateTime();
        // if set to inactive, set enddate to now, if set to active set to 4Y from now
        $this->enddate = $active ? $now->add(new DateInterval("P4Y")) : $now;
        return $this;
    }

    public function getEnddate(): ?\DateTime
    {
        return $this->enddate;
    }

    public function setEnddate(\DateTime $enddate): static
    {
        $this->enddate = $enddate;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setCostumer($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getCostumer() === $this) {
                $order->setCostumer(null);
            }
        }

        return $this;
    }

    public function getBarcode(): string
    {
        if (!$this->getId()) {
            return '';
        }
        $dir = 'barcodes';
        // look in public/barcodes/${id}.svg
        if (!is_dir($dir)) {
            mkdir($dir, 0755);
        }
        $filename = join(DIRECTORY_SEPARATOR, [$dir, (string)$this->getId() . '.svg']);
        if (!file_exists($filename)) {
            // save image
            $barcode = (new BarcodeGeneratorSVG())->getBarcode($this->getId(), BarcodeGeneratorSVG::TYPE_CODE_128);
            file_put_contents($filename, $barcode);
        }

        // $renderer = new SvgRenderer();
        // $renderer->setSvgType($renderer::TYPE_SVG_STANDALONE);
        // $renderer->setForegroundColor([255, 0, 0]);
        // $renderer->setBackgroundColor([255, 255, 255]);
        // return $renderer->render($barcode, 400, 30);
        return $filename;
    }

    public function getDepartment(): ?string
    {
        return $this->Department;
    }

    public function setDepartment(?string $Department): static
    {
        $this->Department = $Department;

        return $this;
    }
}
