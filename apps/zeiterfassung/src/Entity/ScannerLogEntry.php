<?php

namespace Zeiterfassung\Entity;

use DateTime;
use Doctrine\DBAL\Schema\DefaultExpression\CurrentTimestamp;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LogLevel;
use Symfony\Component\Validator\Constraints\Choice;
use Zeiterfassung\Repository\ScannerLogEntryRepository;



#[ORM\Entity(repositoryClass: ScannerLogEntryRepository::class)]
class ScannerLogEntry
{

    public function __toString()
    {
        return sprintf("%s | %s | %s", $this->getTimeStamp()->format("Y-m-d H:i:s"), $this->getLevel(), $this->getMessage());
    }

    public function __construct()
    {
        $this->timeStamp = new DateTime();
    }

    // public const LOG_LEVELS = ;
    public static function log_levels(){
        return (new \ReflectionClass(LogLevel::class))->getConstants();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'logs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScannerClient $scanner = null;

    #[Choice(callback: 'log_levels', message: '{{ value }} not a valid level. Possible logLevels: {{ choices }}')]
    #[ORM\Column(length: 255)]
    private ?string $level = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column(options: ['default' => new CurrentTimestamp()])]
    private ?\DateTime $timeStamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScanner(): ?ScannerClient
    {
        return $this->scanner;
    }

    public function setScanner(?ScannerClient $scanner): static
    {
        $this->scanner = $scanner;

        return $this;
    }

    public function getLevel(): ?string
    {
        return strtoupper($this->level??"");
    }

    public function setLevel(string $level): static
    {
        strtolower($this->level = $level);

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getTimeStamp(): ?\DateTime
    {
        return $this->timeStamp;
    }

    public function setTimeStamp(\DateTime $timeStamp): static
    {
        $this->timeStamp = $timeStamp;

        return $this;
    }
}
