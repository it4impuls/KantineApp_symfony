<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Alert
{
    public string $message;

    public string $type = 'success';

    public bool $withCloseButton;

    public function disable()
    {
        $this->message = "";
    }
}
