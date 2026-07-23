<?php

namespace Zeiterfassung\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zeiterfassung\Repository\ScannerClientRepository;

#[ORM\Entity(repositoryClass: ScannerClientRepository::class)]
class ScannerClient
{
    public function __toString()
    {
        return $this->getUname();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $uname = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $location;

    #[ORM\Column]
    private ?\DateTime $lastOnline = null;

    /**
     * @var Collection<int, ScannerLogEntry>
     */
    #[ORM\OneToMany(targetEntity: ScannerLogEntry::class, mappedBy: 'scanner')]
    private Collection $logs;

    public function __construct()
    {
        $this->logs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUname(): ?string
    {
        return $this->uname;
    }

    public function setUname(string $uname): static
    {
        $this->uname = $uname;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getLastOnline(): ?\DateTime
    {
        return $this->lastOnline;
    }

    public function setLastOnline(\DateTime $lastOnline): static
    {
        $this->lastOnline = $lastOnline;

        return $this;
    }

    /**
     * @return Collection<int, ScannerLogEntry>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(ScannerLogEntry $log): static
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
            $log->setScanner($this);
        }

        return $this;
    }

    public function getLatestLog():?ScannerLogEntry {
        $ret = $this->getLogs()->last();
        if ($ret) return $ret;
        else return null;
    }

    public function getLogsToday():string {
        $today = new DateTime();
        $logs = $this->getLogs()->filter(fn(ScannerLogEntry $val) => $val->getTimeStamp()->diff($today)->days < 1);
        return implode("\n", array_reverse($logs->toArray())); 
    }

    public function removeLog(ScannerLogEntry $log): static
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getScanner() === $this) {
                $log->setScanner(null);
            }
        }

        return $this;
    }
}
