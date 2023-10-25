<?php

namespace App\Controller\Admin;

use App\Entity\Demand;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;



/**
 * @Route ("/ADMIN/callendar")
 * @IsGranted ("ROLE_ADMIN", message="vous n'etes pas autoriser dans ce espace")
 */
class CallendarController extends AbstractController
{
    /**
     * @Route ("/", name="listeAc")
     */
    public function index (Request $request):Response {

        $data=[];
        $color = '';
        $demands = $this->getDoctrine()->getManager()->getRepository(Demand::class)->findAllBCampany($request->getSession()->get('company'));
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
                ];
        }
        $result = json_encode($data);
        return $this->render('Admin/callendar/index.html.twig',compact('result'));
    }
    /**
     * @Route("/indexFerieeAdmin", name="indexFerieeAdmin", methods={"GET", "POST"})
     */
    public function indexFerieeAdmin(): Response {

        return $this->render('DIRECTEUR/feriee.html.twig');
    }
    /**
     * @Route("/indexFerieenxAdmin", name="indexFerieenxAdmin", methods={"GET", "POST"})
     */
    public function indexFerieenxAdmin(): Response {

        return $this->render('DIRECTEUR/next.html.twig');
    }
}


