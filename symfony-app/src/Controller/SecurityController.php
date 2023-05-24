<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SecurityController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    /**
     * @Route("/", name="app_login")
     */
    public function login(Request $request, LoggerInterface $logger): Response
    {
        try{
            $tokenStorage = $this->get('security.token_storage');
            if ($tokenStorage) {
                $token = $tokenStorage->getToken();
                if ($token && $token->isAuthenticated()) {
                    $user = $token->getUser();
                    if (($user instanceof User) && $user->getId() !== 0) {
                        return $this->render('index/index.html.twig', [
                            'load_duration' => 0,
                            'inserted_record_cnt' => 0,
                            'file_path' => 'none',
                        ]);
                    }
                }

                $email = $request->query->get('email');
                $plane_password = $request->query->get('password');
                if ($email) {                    
                    $entityManager = $this->getDoctrine()->getManager();
                    $userRepository = $entityManager->getRepository(User::class);

                    $user = $userRepository->findOneByEMail($email);                    
                    if ($user && ($user instanceof User) && $this->passwordEncoder->isPasswordValid($user, $plane_password)) {                        
                        $roles = ['ROLE_USER'];                        
                        $new_token = new UsernamePasswordToken($user, 'main', $roles);                        
                        $tokenStorage->setToken($new_token);
                                              
                        return $this->redirectToRoute('app_index');                                         
                    }            
                }
            }
        }catch (Exception $e) {}

        return $this->render('security/login.html.twig', [            
            'last_username' => '', 
            'error' => ''
        ]);        
    }
    
    /**
     * @Route("/logout", name="log_out")
     */
    public function logout(Request $request): Response
    {
        $tokenStorage = $this->get('security.token_storage');
        if ($tokenStorage) {
            $tokenStorage->setToken(null);
        }
      
        return $this->render('security/login.html.twig', [            
            'last_username' => '', 
            'error' => ''
        ]);  
    }
}