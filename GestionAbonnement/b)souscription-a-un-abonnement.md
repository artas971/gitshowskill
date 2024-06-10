#[Route('/modifier/abonnement/souscrire/{id}', name: 'profil_souscrire_abonnement')]
public function abonnement_souscrire(AbonnementRepository $abonnementRepository, UserRepository $userRepository, $id): Response
{
    // Récupération de l'abonnement à souscrire
    $abonnement = $abonnementRepository->find($id);
    
    // Récupération de l'utilisateur connecté
    $user = $this->getUser(); // Récupère l'utilisateur connecté grâce au cookie.

    // Vérification si l'abonnement existe
    if ($abonnement) {
        // Obtention de la date actuelle
        $dateActuelle = new \DateTime();
        
        // Récupération de la durée de l'abonnement en jours
        $dureeJours = $abonnement->getTemps(); // Supposons que cette méthode retourne la durée en jours

        // Calcul de la date de début et de la date de fin de l'abonnement
        $dateDebutAbonnement = $dateActuelle;
        $dateFinAbonnement = clone $dateDebutAbonnement;
        $dateFinAbonnement->add(new \DateInterval("P{$dureeJours}D"));

        // Association de l'abonnement à l'utilisateur
        $user->setAbonnement($abonnement);

        // Définition des propriétés dateDebut et dateFin dans l'entité User
        $user->setDateDebut($dateDebutAbonnement);
        $user->setDateFin($dateFinAbonnement);

        // Enregistrement des modifications dans la base de données
        $userRepository->save($user, true);
    }

    // Redirection vers la page de profil de l'utilisateur
    return $this->redirectToRoute('profil');
}
