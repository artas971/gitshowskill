public function findMaxPositionForLieu($lieuId)
{
    return $this->createQueryBuilder('s')
        // Sélectionne la position maximale parmi tous les souhaits pour un lieu donné
        ->select('MAX(s.position)')
        ->andWhere('s.lieu = :lieuId')
        ->setParameter('lieuId', $lieuId)
        ->getQuery()
        // Récupère le résultat sous forme scalaire, c'est-à-dire un seul résultat
        ->getSingleScalarResult();
}

public function updateSouhaitsPosition(array $updatePlaylist): void
{
    $entityManager = $this->getEntityManager();

    foreach ($updatePlaylist as $update) {
        $dataId = $update['dataId'];
        $dataPosition = $update['dataPosition'];

        // Trouver le Souhait correspondant à $dataId
        $souhait = $this->findOneBy(['id' => $dataId]);

        if ($souhait) {
            // Mettre à jour la position du Souhait avec la nouvelle position spécifiée
            $souhait->setPosition($dataPosition);

            // Persister les changements
            $entityManager->persist($souhait);
        } else {
            // Si le Souhait n'est pas trouvé, il est ignoré
            continue;
        }
    }

    // Exécuter les changements persistés
    $entityManager->flush();
}
