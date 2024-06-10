<?php
namespace App\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;

// Service pour gérer les abonnements des utilisateurs
class AbonnementService
{
    // Déclaration des variables de service pour la sécurité et l'Entity Manager
    private $security;
    private $entityManager;

    // Le constructeur reçoit le service de sécurité et l'Entity Manager via l'injection de dépendances
    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    // Méthode pour vérifier et gérer l'état de l'abonnement d'un utilisateur
    public function checkAndHandleAbonnement(?User $user = null): array
    {
        // Initialisation de $flashMessages avec un tableau vide
        $flashMessages = [];
        // Si aucun utilisateur n'est passé en paramètre, on récupère l'utilisateur actuellement connecté
        if ($user === null) {
            $user = $this->security->getUser();
        }

        // Vérification si un utilisateur est authentifié
        if ($user) {
            // Obtention de la date actuelle en utilisant DateTimeImmutable pour éviter les modifications accidentelles
            $actualDate = new \DateTimeImmutable();

            // Récupération de la date de fin de l'abonnement de l'utilisateur
            // Assurez-vous que votre entité User possède une méthode getDateFin()
            $subscriptionEndDate = $user->getDateFin();

            // Comparaison de la date de fin de l'abonnement avec la date actuelle
            if ($subscriptionEndDate < $actualDate) {
                // Si l'abonnement est expiré, retirer les rôles d'abonnement de l'utilisateur
                // On retire l'abonnement de l'utilisateur.
                $user->setAbonnement(null);
                // Persist et flush pour sauvegarder les changements dans la base de données
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                // Prepare une reponse annonçant que l'abonnement à expiré
                $flashMessages['danger'] = 'Votre abonnement a expiré.';
            } else {
                // Calcul du temps restant jusqu'à l'expiration de l'abonnement
                $interval = $actualDate->diff($subscriptionEndDate);
                $hoursRemaining = $interval->h + ($interval->d * 24); // Convertit les jours en heures

                // Préparation des messages d'alerte en fonction du temps restant
                if ($hoursRemaining < 1) {
                    $flashMessages['warning'] = 'Votre abonnement expire dans moins d\'une heure';
                } elseif ($hoursRemaining < 24) {
                    $flashMessages['warning'] = 'Votre abonnement expire dans aujourd\'hui.';
                } elseif ($interval->d < 7) {
                    $flashMessages['warning'] = 'Votre abonnement expire dans ' . $interval->days . ' jours.';
                }
            }
        }
        return $flashMessages;
    }
}
