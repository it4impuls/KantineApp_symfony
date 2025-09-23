<?php

namespace App\Validator;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Date;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class UniqueDateForCostumer extends Constraint
{
    public string $message = 'The costumer {{ Costumer }} already ordered at {{ date }}.';

    public function __construct(public string $datefield, public string $costumerfield, ?array $groups = null, $payload = null)
    {
        parent::__construct([], $groups, $payload);

        // $this->mode = $mode ?? $this->mode;
        // $this->message = $message ?? $this->message;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
