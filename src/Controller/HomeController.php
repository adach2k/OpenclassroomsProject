<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/', name: 'app.home')]
    public function index()
    {
        $repository = $this->doctrine->getRepository(Item::class);
        $posts = $repository->findAll();

        return $this->render('home.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/new', name: 'app.new')]
    public function new(Request $request)
    {
        $item = new Item();

        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->doctrine->getManager();
            $em->persist($item);
            $em->flush();

            return $this->redirectToRoute('app.home');
        }

        return $this->render('new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('item/{id<\d+>}', name: 'app.show')]
    public function show(int $id, ItemRepository $repo)
    {
        $item = $repo->find($id);
        
        if (!$item) {
            throw $this->createNotFoundException(
                "Il n'y a pas un article avec cet identifiant !"
            );
        }

        return $this->render('show.html.twig', [
            'item' => $item,
        ]);
    }

    #[Route('item/edit/{id<\d+>}', name: 'app.edit')]
    public function edit(int $id, Request $request)
    {
        $item = $this->doctrine->getRepository(Item::class)->find($id);

        if (!$item) {
            throw $this->createNotFoundException(
                "Il n'y a pas un article avec cet identifiant !"
            );
        }

        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $this->doctrine->getManager()->flush();

            return $this->redirectToRoute('app.show', [
                'id' => $item->getId(),
            ]);
        }
        
        return $this->render('edit.html.twig', [
            'form' => $form->createView(),
        ]);
        
    }

    #[Route('item/delete/{id<\d+>}', name: 'app.delete', methods: "POST")]
    public function delete(Item $item)
    {
        $em = $this->doctrine->getManager();
        $em->remove($item);
        $em->flush();

        return $this->redirectToRoute('app.home');
    }
}