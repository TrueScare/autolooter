<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailService
{
    private string $infoMail;
    private string $infoMailName;
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(
        #[Autowire('%info_mail%')] string      $infoMail,
        #[Autowire('%info_mail_name%')] string $infoMailName,
        MailerInterface $mailer,
        LoggerInterface $logger
    )
    {
        $this->infoMail = $infoMail;
        $this->infoMailName = $infoMailName;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function notifyAdmin(Email $email): void
    {
        $address = $this->getInfoMailAddress();
        $email->to($address);
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e);
        }
    }

    public function getInfoMailAddress(): Address
    {
        return new Address($this->infoMail,$this->infoMailName);
    }
}