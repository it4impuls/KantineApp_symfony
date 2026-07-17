<?php


namespace Zeiterfassung\DTO;

use DateTime;
use Zeiterfassung\Entity\ScannerLogEntry;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints\Choice;

#[Map(target: ScannerLogEntry::class)]
class LogDTO
{
    // public function __construct() {
    //     $this->timeStamp = new DateTime();
    // }

    #[Choice(callback: [ScannerLogEntry::class, 'log_levels'], message: '{{ value }} not a valid level. Possible logLevels: {{ choices }}')]
    public ?string $level = null;
    public ?string $message = null;
    // public ?DateTime $timeStamp;


    public function setlevel(string $level): static
    {
        $this->level = strtolower($level);
        return $this;
    }
}
