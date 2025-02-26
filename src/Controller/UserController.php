<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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

final class UserController extends AbstractController
{
    #[Route('/api/users', name: 'userList', methods: ['GET'])]
    public function getUserList(UserRepository $userRepository,
        SerializerInterface $serializer): JsonResponse
    {
        $userList = $userRepository->findAll();
        $jsonUserList = $serializer->serialize($userList, 'json');
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getDetailUser(User $user,
        SerializerInterface $serializer): JsonResponse
    {
        $jsonUserList = $serializer->serialize($user, 'json');
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users', name: 'createUser', methods: ['POST'])]
    public function createUser(Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        try {
            $user = $serializer->deserialize($request->getContent(), User::class, 'json');
            $em->persist($user);
            $em->flush();

            $jsonUser = $serializer->serialize($user, 'json');
            $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
           
            return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['location' => $location], true);
        } catch (NotEncodableValueException $e) {
            return new JsonResponse(['error' => 'Invalid JSON format'], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/users/{id}', name: 'updateUser', methods: ['PUT'])]
    public function updateTip(Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        User $currentUser): JsonResponse
    {
        $updateUser = $serializer->deserialize($request->getContent(),
            User::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);
        $em->persist($updateUser);
        $em->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteTip(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }



}
