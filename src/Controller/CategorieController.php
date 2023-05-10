<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CategorieController extends AbstractController
{

    #[Route("/displayjsonCategorie", name: "app_Categorie_displayjson1")]

    public function displayjson1Categorie(CategorieRepository $repo, SerializerInterface $serializer)
    {
        $categorie = $repo->findAll();

        $json = $serializer->serialize($categorie, 'json', ['Groups' => "categorie"]);

        return new Response($json);
    }


    #[Route("/addCategorieJSON", name: "addCategJSON")]
    public function addrecJSON(Request $req, NormalizerInterface $Normalizer,ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $categorie = new categorie();
        $categorie->setCatlib($req->get('Libelle'));

        $em->persist($categorie);
        $em->flush();
        $jsonContent = $Normalizer->normalize($categorie, 'json', ['Groups' => 'categorie']);
        return new Response(json_encode($jsonContent));
    }


    #[Route("/updateCategoriejson/{Catid}", name: "updateCategoryJSON")]
    public function updatereclamationJSON(Request $req, $Catid, NormalizerInterface $Normalizer,ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $categorie = $em->getRepository(Categorie::class)->find($Catid);
        $categorie->setCatlib($req->get('Libelle'));
        $em->flush();
        $jsonContent = $Normalizer->normalize($categorie, 'json', ['Groups' => 'categorie']);
        return new Response("Catégorie modifié avec succès ! " . json_encode($jsonContent));
    }




    #[Route("/deletejsoncat/{Catid}", name: "deletecattJSON")]
    public function deleteCategorieJSON(Request $req, $Catid, NormalizerInterface $Normalizer)
    {
        $em = $this->getDoctrine()->getManager();
        $categorie = $em->getRepository(categorie::class)->find($Catid);
        $em->remove($categorie);
        $em->flush();
        $jsonContent = $Normalizer->normalize($categorie, 'json', ['Groups' => 'categorie']);
        return new Response(" Catégorie supprimé avec succès !" . json_encode($jsonContent));
    }




    #[Route('/categorie', name: 'app_category')]
    public function affichageCategories(Request $request): Response
    {
        $session=  $request->getSession();
        $usersession=$session->get('user');
        if($usersession==null)
        {
            return $this->redirectToRoute("app_login");
        }

        $em = $this->getDoctrine()->getManager()->getRepository(Categorie::class);

        $categories = $em->findAll();
        return $this->render('categorie/index.html.twig', ['cat' => $categories]);
    }

    #[Route('/ajoutCategorie', name: 'ajout_category')]
    public function ajoutCategorie(Request $request): Response
    {
        $session=  $request->getSession();
        $usersession=$session->get('user');
        if($usersession==null)
        {
            return $this->redirectToRoute("app_login");
        }

        $category = new Categorie();
        $form = $this->createForm(CategorieType::class, $category);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->addFlash(
                'notice', 'Catégorie a été bien ajoutée !'
            );
            return $this->redirectToRoute('app_category');

        }

        return $this->render('categorie/ajoutCategorie.html.twig',
            ['f' => $form->createView()]
        );
    }

    #[Route('/modifierCategorie/{catid}', name: 'modifier_category')]
    public function modifierCategory(Request $request, $catid): Response
    {$session=  $request->getSession();
        $usersession=$session->get('user');
        if($usersession==null)
        {
            return $this->redirectToRoute("app_login");
        }

        $prod = $this->getDoctrine()->getManager()->getRepository(Categorie::class)->find($catid);


        $form = $this->createForm(CategorieType::class, $prod);


        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($prod);//ajout
            $em->flush();// commit
            $this->addFlash(
                'notice', 'Catégorie a été bien modifiée ! '
            );
            return $this->redirectToRoute('app_category');

        }

        return $this->render('categorie/modifierCategorie.html.twig',
            ['f' => $form->createView()]
        );
    }

    #[Route('/supprimerCategorie/{catid}', name: 'supprimerCategory')]
    public function supprimerCategory(Request $request,Categorie $category): Response
    {
        $session=  $request->getSession();
        $usersession=$session->get('user');
        if($usersession==null)
        {
            return $this->redirectToRoute("app_login");
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        //MISE A JOUR
        $em->flush();//commit
        $this->addFlash(
            'noticedelete', 'Catégorie a été bien supprimé '
        );
        return $this->redirectToRoute('app_category');
    }

}