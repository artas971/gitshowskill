    #[Route('/new', name: 'app_abonnement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AbonnementRepository $abonnementRepository): Response
    {
        $abonnement = new Abonnement();
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { 
            // Récupérer la valeur saisie dans le champ "temps"
            //l'utilisateur  defini le nomnbre de jour de l'abonnement qu'il souhaite créer
            $jours = $form->get('temps')->getData();
            
            // Transformer le nombre de jours en nombre d'heures
            $heures = $jours * 24;
        
            // Enregistrer le nombre d'heures dans l'entité
            $abonnement->setTemps($heures);
        
            // Enregistrer l'entité en base de données
            $abonnementRepository->save($abonnement, true);
        
            // Rediriger l'utilisateur
            return $this->redirectToRoute('app_abonnement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('abonnement/new.html.twig', [
            'abonnement' => $abonnement,
            'form' => $form,
        ]);
    }