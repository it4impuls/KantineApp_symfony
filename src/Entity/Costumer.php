<?php

namespace App\Entity;

use App\Repository\CostumerRepository;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use Picqer\Barcode\Types\TypeCode128;
use Picqer\Barcode\Renderers\HtmlRenderer;
use Picqer\Barcode\Renderers\SvgRenderer;
use Symfony\Component\AssetMapper\AssetMapper;

#[ORM\Entity(repositoryClass: CostumerRepository::class)]
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

    public function __tostring()
    {
        return (string)$this->id;
    }

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $now = new DateTime();
        $this->enddate = $now->add(new DateInterval("P4Y"));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return trim($this->firstname);
    }

    public function setFirstname(string $firstname): static
    {
        trim($this->firstname = $firstname);

        return $this;
    }

    public function getLastname(): ?string
    {
        return  trim($this->lastname);
    }

    public function setLastname(string $lastname): static
    {
        trim($this->lastname = $lastname);

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

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
}
