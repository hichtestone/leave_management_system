<?php

namespace App\Controller\RESPONSABLE;
use App\Entity\Demand;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @Route("RESPONSABLE/callendar")
 * @IsGranted("ROLE_RESPONSABLE", message="vous n'etes pas autoriser dans ce espace")
 */
class CallendarController extends AbstractController
{
    /**
     * @Route ("/", name= "callendarRes")
     */
    public function listCallendar() {

        $dataAll=[];
        $dataFinal=[];
        $callendar= [];
        $demandResp=[];
        $color= '';

        $demandBySameUser= $this->getDoctrine()->getManager()->getRepository(Demand::class)->getdemandBy($this->getUser()->getUser()->getId());
        $responsble =$this->getUser()->getResponsable();

        foreach ($responsble as $respon) {

          array_push($demandResp,$this->getDoctrine()->getManager()->getRepository(Demand::class)->findBy(['user' => $respon]));
        // dd($demandResp);
        }
        $dataAll=array_merge($demandBySameUser,$demandResp);
        $dataFinal=array_merge($demandBySameUser,$demandResp);
       // dd($dataFinal);
        foreach ($dataAll as $data) {

        array_push($dataFinal,$this->getDoctrine()->getManager()->getRepository(Demand::class)->getdemandBy($data[0]->getUser()->getId()));
        }
       // dd($dataFinal);
        $dataFinal=array_filter($dataFinal);
        foreach ($dataFinal as $data) {

            if ($data[0]->getStatus() == 1){
                $color = '#17a2b8'
                ;
            }

            elseif ($data[0]->getStatus() == 3) {
                $color= '#28a745';
            }

            elseif ($data[0]->getStatus() == 4) {
                $color= '#dc3545';
            }
            else
                $color= '#fd7e14';

            $callendar[]=
                [
                    'title' => $data[0]->getUser()->getLastName().' '.$data[0]->getUser()->getFirstName(),
                    'start' => $data[0]->getStartDate()->format('Y-m-d H:i:s'),
                    'end' => $data[0]->getEndDate()->modify('+1 day')->format('Y-m-d H:i:s'),
                    'description' => $data[0]->getReason(),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps'=> [
                        'status'=> 'done'
                    ]
                ];


        }

        $result = json_encode($callendar);
        return $this->render('RESPONSABLE/callendar/index.html.twig', compact('result'));


    }
    /**
     * @Route("/indexFerieeResp", name="indexFerieeResp", methods={"GET", "POST"})
     */
    public function indexFerieeResp(): Response {

        return $this->render('DIRECTEUR/feriee.html.twig');
    }
    /**
     * @Route("/indexFerieenxREsp", name="indexFerieenxREsp", methods={"GET", "POST"})
     */
    public function indexFerieenxREsp(): Response {

        return $this->render('DIRECTEUR/next.html.twig');
    }
}