<?php

namespace App\Controller;

use App\Entity\Month;
use App\Entity\Tip;
use App\Repository\MonthRepository;
use App\Repository\TipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TipController extends AbstractController
{
    
    /**
    * Route permettant d'afficher la liste des conseils
    * Accessible aux utilisateurs connectés (IS_AUTHENTICATED_FULLY)
    */
    #[Route('/api/tips', name: 'tipList', methods: ['GET'])]
    public function getTipList(TipRepository $tipRepository,
        SerializerInterface $serializer): JsonResponse
    {
        $tipList = $tipRepository->findAll();
        $jsonTipList = $serializer->serialize($tipList, 'json', ['groups' => 'getTipList']);

        return new JsonResponse($jsonTipList, Response::HTTP_OK, [], true);
    }

    /**
    * Route permettant d'afficher les conseils du mois sélectionné par son numéro (exple numéro 3 pour le mois de mars)
    * Accessible aux utilisateurs connectés (IS_AUTHENTICATED_FULLY)
    */
    #[Route('/api/tips/{month}', name: 'monthTips', methods: ['GET'])]
    public function getMonthTips(int $month, MonthRepository $MonthRepository,
        SerializerInterface $serializer): JsonResponse
    {
        $tipsByMonth = $MonthRepository->findByMonth($month);
        if($tipsByMonth){
            $jsonTipsByMonth = $serializer->serialize($tipsByMonth, 'json', ['groups' => 'getMonthTips']);
            return new JsonResponse($jsonTipsByMonth, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    /**
    * Route permettant d'afficher un conseil sélectionné par son identifiant
    * Accessible aux utilisateurs connectés (IS_AUTHENTICATED_FULLY)
    */
    #[Route('/api/tips/id/{id}', name: 'detailTip', methods: ['GET'])]
    public function getDetailTip(Tip $tip,
        SerializerInterface $serializer): JsonResponse
    {
        $jsonTip = $serializer->serialize($tip, 'json', ['groups' => 'getDetailTip']);   
        return new JsonResponse($jsonTip, Response::HTTP_OK, ['accept' => 'json'], true); 
    }

    /**
    * Route permettant de créer un conseil
    * Accessible uniquement aux adminitrateurs
    * Header raw
    * {
    * "title": "TipNew",
    * "content": "BlaBLa newTip",
    * "month": [5]
    * }
    */
    #[IsGranted('ROLE_ADMIN', message:'Vous n\'avez pas les droits nécessaires pour créer un conseil.')]
    #[Route('/api/tips', name: 'createTip', methods: ['POST'])]
    public function createTip(Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator, 
        ValidatorInterface $validator): JsonResponse
    {
        $tip = $serializer->deserialize($request->getContent(), Tip::class, 'json', ['groups' => 'createTip']);

        $content = $request->toArray();
        $monthNbs = $content['month'] ?? [];
        foreach ($monthNbs as $monthNb) {
            $month = $em->getRepository(Month::class)->findOneByMonth($monthNb);
            if ($month) {
                $tip->addMonth($month);
            } else {
                return new JsonResponse(['error' => 'Mois non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }
        }

        $errors = $validator->validate($tip);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            // or other alternative on lève une exception et on la renvoit via subscriber
            // pour gérer plus finement les erreurs, créer sa propre exception qui étend HttpException et prend $errors en paramètre
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }
      
        $em->persist($tip);
        $em->flush();
        
        $jsonTip = $serializer->serialize($tip, 'json', ['groups' => 'createTip']);
        $location = $urlGenerator->generate('detailTip', ['id' => $tip->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            
        return new JsonResponse($jsonTip, Response::HTTP_CREATED, ['location' => $location], true);
    }
    
    /**
    * Route permettant de mettre à jour un conseil sélectionné par son identifiant
    * Accessible uniquement aux administrateurs (ROLE_ADMIN)
    * Header raw
    * {
    * "title": "TipNew",
    * "content": "BlaBLa newTip",
    * "month": [5]
    * }
    */
    #[IsGranted('ROLE_ADMIN', message:'Vous n\'avez pas les droits nécessaires pour modifier un conseil.')]
    #[Route('/api/tips/id/{id}', name: 'updateTip', methods: ['PUT'])]
    public function updateTip(Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        Tip $currentTip, 
        ValidatorInterface $validator): JsonResponse
    {
        $updateTip = $serializer->deserialize($request->getContent(),
            Tip::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentTip]);

        $content = $request->toArray();
        $monthNbs = $content['month'] ?? [];

        foreach ($currentTip->getMonths() as $currentMonth) {
            $currentTip->removeMonth($currentMonth);
        }

        foreach ($monthNbs as $monthNb) {
            $month = $em->getRepository(Month::class)->findOneByMonth($monthNb);
            if ($month) {
                $updateTip->addMonth($month);
            } else {
                return new JsonResponse(['error' => 'Mois non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }
        }
        
        $errors = $validator->validate($updateTip);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $em->persist($updateTip);
        $em->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
 
    /**
    * Route permettant de supprimer un conseil sélectionné par son identifiant
    * Accessible uniquement aux administrateurs (ROLE_ADMIN)
    */
    #[IsGranted('ROLE_ADMIN', message:'Vous n\'avez pas les droits nécessaires pour supprimer un conseil.')]
    #[Route('/api/tips/id/{id}', name: 'deleteTip', methods: ['DELETE'])]
    public function deleteTip(Tip $tip, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($tip);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
