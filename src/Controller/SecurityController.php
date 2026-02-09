<?php

namespace Shared\Controller;

use Shared\Entity\SonataUserUser;
use Sonata\UserBundle\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\RememberMeFactory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function apiLogin(#[CurrentUser] ?User $editor, Request $request): Response
    {
        
        // $request->getContent()
        if (null === $editor) {
            return $this->json([
                'error' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $editor->getId(),
            'username' => $editor->getUsername(),
            'roles' => $editor->getRoles(),
        ], Response::HTTP_OK);
    }

    // #[Route(['/api/login','/api/login/'], name: 'api_login', methods: ['POST'])]
    // public function apiLogin(#[CurrentUser] ?User $user, Request $request): Response
    // {
    //     if (null === $user) {
    //         return $this->json([
    //             'message' => 'missing credentials',
    //         ], Response::HTTP_UNAUTHORIZED);
    //     }
    //     $token = $request->attributes->get("_security_remember_me_cookie");
    //     $response = $this->json([
    //         'user'  => $user->getUserIdentifier(),
    //         $token->getName() => $token->getValue()
    //     ]);
    //     return $response;
    // }
}
