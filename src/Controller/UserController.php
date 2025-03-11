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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    private $userPasswordHasher;
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    //PUBLIC_ACCESS
    #[Route('/api/user', name: 'createUser', methods: ['POST'])]
    public function createUser(Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $user->setRoles(["ROLE_USER"]);
        
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json');
        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['location' => $location], true);
        
    }
    
    //ADMIN ACCESS
    #[Route('/api/users', name: 'userList', methods: ['GET'])]
    public function getUserList(UserRepository $userRepository,
        SerializerInterface $serializer): JsonResponse
    {
        $userList = $userRepository->findAll();
        $jsonUserList = $serializer->serialize($userList, 'json');
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    //ADMIN ACCESS
    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getDetailUser(User $user,
        SerializerInterface $serializer): JsonResponse
    {
        $jsonUserList = $serializer->serialize($user, 'json');
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    //ADMIN ACCES
    #[Route('/api/users/{id}', name: 'updateUser', methods: ['PUT'])]
    public function updateUser(Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        User $currentUser,
        ValidatorInterface $validator): JsonResponse
    {
        $updateUser = $serializer->deserialize($request->getContent(),
            User::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);
        
        $errors = $validator->validate($updateUser);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $em->persist($updateUser);
        $em->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    //ADMIN ACCESS
    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
