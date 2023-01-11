<?php 

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameType;
use App\Repository\GameRepository;
use App\Security\Voter\GameVoter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Définie un prefix pour toutes les routes de ce controller
 * 
 */
#[Route("/game")]
class GameController extends AbstractController
{
    #[Route("/admin")]
    #[IsGranted("ROLE_USER")]
    public function admin(GameRepository $gameRepository, Request $request): Response
    {
        $author = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) { // Affiche tous les jeux si l'user est admin
            $author = null;
        }

        $p = $request->get('p', 1); // Page 1 par défaut
        $itemCount = 20;
        $search = $request->get('s', '');
        $entities = $gameRepository->findData($itemCount, $p, $search, $author);

        $pageCount = ceil($entities->count() / $itemCount);

        return $this->render('game/admin.html.twig', [
            'entities' => $entities,
            'pageCount' => max($pageCount, 1),
        ]);
    }

    /**
     * On utilise l'injection de dépendance (Dependency Injection) pour obtenir un objet EntityManager
     */
    #[Route("/new")]
    #[IsGranted("ROLE_USER")]
    public function new(EntityManagerInterface $em, Request $request, TranslatorInterface $translator): Response
    {
        // Créer une nouvelle entrée dans la table "game"
        // $newGame = new Game;
        // $newGame->setTitle("Super Mario Bros.");
        // $newGame->setEnabled(true);

        // $em->persist($newGame); // Prepare la requête, prêt à ajouter un nouvel objet en db

        // $em->flush(); // Execute la requête

        // Ancienne methode
        //$em = $this->getDoctrine()->getManager();

        $entity = new Game;
        $entity->setAuthor($this->getUser());
        // Crée un formulaire à partir de la classe "GameType" et envois l'entity en tant que données 
        $form = $this->createForm(GameType::class, $entity);

        // Cherche des données _POST pour les envoyer au formulaire
        $form->handleRequest($request);

        // Test si le formulaire a bien été envoyé ET s'il est valide
        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', $translator->trans('game.new.success', [
                '%title%' => $entity->getTitle(),
            ]));

            // Redirection vers la liste des jeux
            return $this->redirectToRoute('app_game_admin');
        }

        return $this->render('game/new.html.twig', [
            'form' => $form->createView(), // Créé la vue du formulaire
            'entity' => $entity,
        ]);
    }

    /**
     * Route avec un paramètre "id", "\d+" indique que l'id est un nombre entier de 1 ou plusieurs chiffres
     * Grâce au "Param Converter", Sf va récupérer l'objet Game par rapport au paramètre id
     * 
     */
    #[Route("/{id}/edit", requirements:["id" => "\d+"])]
    #[IsGranted("ROLE_USER")]
    public function edit(EntityManagerInterface $em, Request $request, Game $entity, TranslatorInterface $translator): Response 
    {
        /*if ($this->getUser() !== $entity->getAuthor() && !$this->isGranted('ROLE_ADMIN')) {
            // ERROR
        }*/

        // Crée une erreur si l'user n'a pas le droit de modifier le jeu
        $this->denyAccessUnlessGranted(GameVoter::EDIT, $entity);

        if ($entity->getAuthor() === null) {
            $entity->setAuthor($this->getUser());
        }
        $form = $this->createForm(GameType::class, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', $translator->trans('game.edit.success', [
                '%title%' => $entity->getTitle(),
            ]));

            return $this->redirectToRoute('app_game_admin');
        }

        return $this->render('game/edit.html.twig', [
            'form' => $form->createView(),
            'entity' => $entity,
        ]);
    }

    #[Route("/{id}/delete", requirements:["id" => "\d+"])]
    #[IsGranted("ROLE_USER")]
    public function delete(EntityManagerInterface $em, Request $request, Game $entity, TranslatorInterface $translator): Response 
    {
        $this->denyAccessUnlessGranted(GameVoter::EDIT, $entity);

        if ($this->isCsrfTokenValid('delete_game_'.$entity->getId(), $request->get('token'))) {
            $em->remove($entity);
            $em->flush();

            $this->addFlash('success', $translator->trans('game.delete.success', [
                '%title%' => $entity->getTitle(),
            ]));

            return $this->redirectToRoute('app_game_admin');
        }
        return $this->render('game/delete.html.twig', [
            'entity' => $entity,
        ]);
    }

    #[Route("/{id}/show", requirements:["id" => "\d+"])]
    public function show(Game $entity): Response
    {
        $this->denyAccessUnlessGranted(GameVoter::VIEW, $entity);

        return $this->render('game/show.html.twig', [
            'entity' => $entity,
        ]);
    }
}