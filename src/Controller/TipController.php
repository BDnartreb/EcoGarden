<?php

namespace App\Controller;

use App\Entity\Tip;
use App\Repository\TipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class TipController extends AbstractController
{
    #[Route('/api/tips', name: 'tipList', methods: ['GET'])]
    public function getTipList(TipRepository $tipRepository,
        SerializerInterface $serializer): JsonResponse
    {
        $tipList = $tipRepository->findAll();
        $jsonTipList = $serializer->serialize($tipList, 'json');
        return new JsonResponse($jsonTipList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/tips/{month}', name: 'monthTips', methods: ['GET'])]
    public function getMonthTips(int $month, TipRepository $tipRepository,
        SerializerInterface $serializer): JsonResponse
    {
        $tipsByMonth = $tipRepository->findByMonth($month);
        if($tipsByMonth){
            $jsonTipsByMonth = $serializer->serialize($tipsByMonth, 'json');
            return new JsonResponse($jsonTipsByMonth, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/tips/id/{id}', name: 'detailTip', methods: ['GET'])]
    public function getDetailTip(Tip $tip,
        SerializerInterface $serializer): JsonResponse
    {
        $jsonTip = $serializer->serialize($tip, 'json');
        return new JsonResponse($jsonTip, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/tips', name: 'createTip', methods: ['POST'])]
    public function createTip(Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        try {
            $tip = $serializer->deserialize($request->getContent(), Tip::class, 'json');
            $em->persist($tip);
            $em->flush();
    
            $jsonTip = $serializer->serialize($tip, 'json');
            $location = $urlGenerator->generate('detailTip', ['id' => $tip->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            
            return new JsonResponse($jsonTip, Response::HTTP_CREATED, ['location' => $location], true);
        } catch (NotEncodableValueException $e) {
            return new JsonResponse(['error' => 'Invalid JSON format'], JsonResponse::HTTP_BAD_REQUEST);
        }

    }

    #[Route('/api/tips/id/{id}', name: 'updateTip', methods: ['PUT'])]
    public function updateTip(Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        Tip $currentTip): JsonResponse
    {
        $updateTip = $serializer->deserialize($request->getContent(),
            Tip::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentTip]);
        $em->persist($updateTip);
        $em->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/tips/id/{id}', name: 'deleteTip', methods: ['DELETE'])]
    public function deleteTip(Tip $tip, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($tip);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
