public function searchByNomOrCodePostal($term)
{
    // Création d'une requête de sélection sur l'entité Ville (v)
    return $this->createQueryBuilder('v')
        // Ajout d'une condition : le nom de la ville ou le code postal doit correspondre au terme fourni
        ->andWhere('v.villeCode LIKE :term OR v.codePostal LIKE :term')
        // Définition du paramètre 'term' utilisé dans la requête, en recherchant le terme partiellement dans le nom de la ville ou le code postal
        ->setParameter('term', '%' . $term . '%')
        // Exécution de la requête et récupération des résultats
        ->getQuery()
        ->getResult();
}
