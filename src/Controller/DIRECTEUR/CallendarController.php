<?php

namespace App\Controller\DIRECTEUR;

use App\Entity\Demand;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;



/**
 * @Route ("/DIRECTEUR/callendar")
 * @IsGranted ("ROLE_DIRECTEUR", message="vous n'etes pas autoriser dans ce espace")
 */
class CallendarController extends AbstractController
{
    /**
     * @Route ("/", name="listeDc")
     */
    public function index ():Response {

        $data=[];
        $color = '';
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->getUserNotAdmin();
        $demands=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findallDemandUserNot($users);
        foreach ($demands as $demand){

            if ($demand->getStatus() == 1){
                $color = '#17a2b8'
                ;
            }

            elseif ($demand->getStatus() == 3) {
                $color= '#28a745';
            }

            elseif ($demand->getStatus() == 4) {
            $color= '#dc3545';
            }
            else
                $color= '#fd7e14';

            $data[] =
                [
                    'title' => $demand->getUser()->getLastName().' '.$demand->getUser()->getFirstName(),
                    'start' => $demand->getStartDate()->format('Y-m-d H:i:s'),
                    'end' => $demand->getEndDate()->modify('+1 day')->format('Y-m-d H:i:s'),
                    'description' => $demand->getReason(),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                ];
        }
        $result = json_encode($data);
        return $this->render('DIRECTEUR/callendar/index.html.twig',compact('result'));
    }
    /**
     * @Route("/indexFeriee", name="indexFeriee", methods={"GET", "POST"})
     */
    public function indexFeriee(): Response {

        return $this->render('DIRECTEUR/feriee.html.twig');
    }
    /**
     * @Route("/indexFerieenx", name="indexFerieenx", methods={"GET", "POST"})
     */
    public function indexFerieenx(): Response {

        return $this->render('DIRECTEUR/next.html.twig');
    }
}