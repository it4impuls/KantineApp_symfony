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
use Symfony\Component\Validator\Constraints\Choice;

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

    public const DEPARTMENTS = ["IT" => "IT", "BüMa" => "BÜMA", "Media" => "MEDIA", "Service" => "SERVICE", "Tischlerei" => "TISCHLEREI"];

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $enddate = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'costumer')]
    private Collection $orders;

    protected File $Barcode;

    #[Choice(choices: Costumer::DEPARTMENTS, message: '{{ value }} not a valid department. Possible departments: {{ choices }}')]
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
        $dirname = 'barcodes';
        // look in public/barcodes/${id}.svg
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755);
        }

        // 
        $filename = join(DIRECTORY_SEPARATOR, [$dirname, (string)$this->getId() . '.svg']);
        if (!file_exists($filename)) {
            $barcode = SvgHelper::generateBarcode($this->getId());
            file_put_contents($filename, $barcode);
        }

        return $filename;
    }

    public function getDepartment(): ?string
    {
        return $this->Department;
    }

    public function setDepartment(?string $Department): static
    {
        $this->Department = trim(strtoupper($Department));

        return $this;
    }
}

class SvgHelper
{
    static public function generateBarcode(int $id): string
    {
        // save image
        $barcode = (new BarcodeGeneratorSVG())->getBarcode($id, BarcodeGeneratorSVG::TYPE_CODE_128);
        SvgHelper::addNumberBarcode($barcode, $id);
        return $barcode;
    }

    static public function getSvgValue(string $svg, string $name, string $class): string
    {
        // <$class**$name="$toGet">
        $pattern = sprintf('/(?<=<%s).+?\K(?<=%s=\").+?(?=")/', $class, $name);
        //'/(?<=%s=\\").+?(?=")/', $name);
        $matches = [];
        $entries = preg_match($pattern, $svg, $matches);
        assert($entries && sizeof($matches) >= 1, "No matches");
        return $matches[0];
    }

    static public function setSvgValue(string &$barcode, string $name, string $value, $class): string
    {
        // <$class**$name="$toReplace">
        $pattern = sprintf('/(?<=<%s).+?\K(?<=%s=\").+?(?=")/', $class, $name);
        // replace ** with $value
        $replaced = preg_replace($pattern, $value, $barcode, 1);
        assert($replaced, "error in the pattern");
        assert($replaced != $barcode, "No changes");
        $barcode = $replaced;
        return $barcode;
    }

    static public function setSvgValues(string &$barcode, array $replacements, string $class): string
    {
        foreach ($replacements as $key => $value) {
            $barcode = SvgHelper::setSvgValue($barcode, $key, $value, $class);
        }
        return $barcode;
    }

    static public function insertAfter(string &$barcode, string $toInsert, string $afterRegex): string
    {
        $inserted = str_replace($afterRegex, $afterRegex . $toInsert, $barcode);
        assert($inserted, "Error in regex expression");
        assert($inserted != $barcode, "No matches found");
        $barcode = $inserted;
        return $barcode;
    }

    static public function addNumberBarcode(string &$barcode, int $id, int $barcodeHeight = 30, string $color = 'black', int $fontheight = 10, $sep = 0): string
    {
        $width = SvgHelper::getSvgValue($barcode, "width", 'svg');
        assert(ctype_digit($width), "width must be a number");
        $height = $barcodeHeight + $fontheight + $sep;
        SvgHelper::setSvgValues($barcode, ["height" => $height, "viewBox" => sprintf("0 0 %u %u", $width, $height)], 'svg');

        //dominant-baseline="middle" text-anchor="middle"
        $numberSVG = sprintf(
            '<text x="50%%" y="%u" font-size="%u" fill="%s">%u</text>',
            $barcodeHeight + $fontheight,
            $fontheight,
            $color,
            $id
        );

        // assumes only one </g>
        SvgHelper::insertAfter($barcode, "\n\t" . $numberSVG, '</g>');
        return $barcode;
    }
}
