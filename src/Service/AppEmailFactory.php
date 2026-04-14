<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class AppEmailFactory
{
    public function __construct(
        private readonly string $fromEmail,
        private readonly string $fromName,
    ) {
    }

    public function createTemplatedEmail(string $subject): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->subject($subject);
    }

    public function getFromAddress(): Address
    {
        return new Address($this->fromEmail, $this->fromName);
    }
}
