   #[Route(path: '/mot-de-passe-oublie', name: 'forgotten_password')]
    public function forgottenPassword( Request $request, UserRepository$userRepository,TokenGeneratorInterface $tokenGeneratorInterface,EntityManagerInterface$entityManager,SendMailService $sms): response {
        // Création du formulaire de demande de réinitialisation de mot de passe
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        // Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() and $form->isValid()) {
            // Vérification que l'adresse mail correspond à un utilisateur
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            // Affichage d'un message flash indiquant que l'email de réinitialisation est envoyé
            $this->addFlash(
                'warning',
                'Si l\'adresse mail saisie correspond à un utilisateur vous recevrez un email afin de modifier votre mot de passe'
            );

            if ($user) {
                // Génération d'un token de réinitialisation de mot de passe
                $token = $tokenGeneratorInterface->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                // Génération d'un lien de réinitialisation du mot de passe
                $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // Création des données pour le mail de réinitialisation du mot de passe
                $context = [
                    'url' => $url,
                    'user' => $user,
                ];

                // Envoi du mail
                $sms->send(
                    'no-reply@karakanou.fr',
                    $user->getEmail(),
                    'Demande de réinitialisation de mot de passe',
                    'reset_password',
                    $context,
                );

                // Affichage d'un message flash indiquant que l'email a été envoyé
                $this->addFlash(
                    'success',
                    'L\'email à bien été envoyé'
                );
            }

            // Redirection vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Affichage du formulaire de demande de réinitialisation de mot de passe
        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView()
        ]);
    }

    #[Route(path: '/mot-de-passe-oublie/{token}', name: 'reset_password')]
    public function resetPassword(string $token,Request $request,UserRepository $userRepository,EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasherInterface): Response {
        // Vérification si l'utilisateur est autorisé à accéder à cette page via le token
        $user = $userRepository->findOneByResetToken($token);

        if ($user) {
            // Création du formulaire de réinitialisation de mot de passe
            $form = $this->createForm(ResetPasswordFormType::class);
            $form->handleRequest($request);

            // Vérification si le formulaire est soumis et valide
            if ($form->isSubmitted() and $form->isValid()) {
                // Suppression du token de réinitialisation
                $user->setResetToken('');

                // Hashage et définition du nouveau mot de passe
                $user->setPassword(
                    $userPasswordHasherInterface->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                // Enregistrement des modifications dans la base de données
                $entityManager->persist($user);
                $entityManager->flush();

                // Affichage d'un message flash de confirmation
                $this->addFlash(
                    'success',
                    'Votre mot de passe a bien été modifié'
                );

                // Redirection vers la page de connexion
                return $this->redirectToRoute('app_login');
            }

            // Affichage du formulaire de réinitialisation de mot de passe
            return $this->render('security/reset_password.html.twig', [
                'passwordform' => $form->createView()
            ]);
        }

        // Affichage d'un message flash d'accès non autorisé et redirection vers la page de connexion
        $this->addFlash(
            'danger',
            'Accès non autorisé'
        );
        return $this->redirectToRoute('app_login');
    }

