<?php
namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail; // Import de la classe TemplatedEmail pour créer des emails avec Twig
use Symfony\Component\Mailer\MailerInterface; // Import de l'interface MailerInterface pour envoyer des emails

class SendMailService{
    private $mailer; // Déclaration de la propriété privée pour stocker l'instance du MailerInterface

    // Constructeur de la classe pour injecter l'instance de MailerInterface
    public function __construct(MailerInterface $mailer){
        $this->mailer = $mailer; // Initialisation de la propriété $mailer avec l'instance injectée
    }

    // Méthode pour envoyer un email
    public function send(
        string $from, // Adresse email de l'expéditeur
        string $to, // Adresse email du destinataire
        string $subject, // Sujet de l'email
        string $template, // Modèle de l'email (fichier Twig)
        array $context // Données passées au modèle Twig pour personnaliser l'email
    ): void {
        // Création de l'email en utilisant TemplatedEmail pour utiliser un modèle Twig
        $email = (new TemplatedEmail())
            ->from($from) // Adresse email de l'expéditeur
            ->to($to) // Adresse email du destinataire
            ->subject($subject) // Sujet de l'email
            ->htmlTemplate("email/$template.html.twig") // Modèle Twig utilisé pour le contenu de l'email
            ->context($context); // Données passées au modèle Twig

        // Envoi de l'email en utilisant l'instance de MailerInterface
        $this->mailer->send($email);
    }
}
