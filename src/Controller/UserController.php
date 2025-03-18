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

    /**
    * Route permettant de créer un nouvel utilisateur
    * Accessible sans restriction
    * Body raw
    * {
    * "email": "adresse_email",
    * "password": "password",
    * "roles": ["ROLE_USER"],
    * "zipcode": "52402"
    * }
    */
    #[Route('/api/users', name: 'createUser', methods: ['POST'])]
    public function createUser(Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        
        if(!$user->getPassword()){
            return new JsonResponse(['error' => 'Le mot de passe doit être renseigné.'], JsonResponse::HTTP_BAD_REQUEST);
        }

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
    
    /**
    * Route permettant d'afficher la liste des utilisateurs
    * Accessible uniquement aux administrateurs (ROLE_ADMIN)
    */
    #[Route('/api/users', name: 'userList', methods: ['GET'])]
    public function getUserList(UserRepository $userRepository,
        SerializerInterface $serializer): JsonResponse
    {
        $userList = $userRepository->findAll();
        $jsonUserList = $serializer->serialize($userList, 'json');
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    /**
    * Route permettant d'afficher un utilisateur sélectionné par son identifiant
    * Accessible uniquement aux administrateurs (ROLE_ADMIN)
    */
    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getDetailUser(User $user,
        SerializerInterface $serializer): JsonResponse
    {
        $jsonUserList = $serializer->serialize($user, 'json');
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    /**
    * Route permettant de modifier un utilisateur sélectionné par son identifiant
    * Accessible uniquement aux administrateurs (ROLE_ADMIN)
    * Body raw
    * {
    * "email": "adresse_email",
    * "password": "password",
    * "roles": ["ROLE_USER"],
    * "zipcode": "52402"
    * }
    */
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

        $updateUser->setPassword($this->userPasswordHasher->hashPassword($updateUser, "password"));
        $updateUser->setRoles(["ROLE_USER"]);
        
        $errors = $validator->validate($updateUser);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $em->persist($updateUser);
        $em->flush();
        
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
    * Route permettant de supprimer un utilisateur sélectionné par son identifiant
    * Accessible uniquement aux administrateurs (ROLE_ADMIN)
    */
    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}