<?php

namespace App\Controller\DIRECTEUR;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("DIRECTEUR/user/management")
 * @IsGranted("ROLE_DIRECTEUR")
 */
class UserManagementController extends AbstractController
{
    /**
     * @Route("/", name="user_management")
     * @Security ("is_granted('ROLE_DIRECTEUR')")
     */
    public function index(): Response
    {
        $users=$this->getUser()->getResponsable();
        return $this->render('DIRECTEUR/user_management/index.html.twig', [
           'users'=>$users,
        ]);
    }
}
