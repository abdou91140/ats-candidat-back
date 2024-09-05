<?php
namespace App\Service;
use App\Entity\User;
use Knp\Snappy\Pdf;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

class Mailer
{
    private $mailer;
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;

    }
    public function sendWelcomeMessage(User $user)
    {
        $email = (new TemplatedEmail())
            ->from('ne-repond-pas-stp@taf-hunt.com')
            ->to($user->getEmail())
            ->subject('The hunt is on !')
            ->htmlTemplate('email/_email.html.twig')
            ->context([
                'user' => $user,
            ]);
        $this->mailer->send($email);
    }
}