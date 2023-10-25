<?php

namespace App\Controller\SuperAdmin;

use App\Entity\Company;
use App\Entity\Demand;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CallendarController
 * @package App\Controller\SuperAdmin
 * @Route("/SuperAdmin/callendar")
 * @IsGranted("ROLE_SUPER_ADMIN", message="vous n'etes pas autoriser dans ce espace")
 */
class CallendarController extends AbstractController
{
    private $id;

    /**
     * @Route("/", name="superadmin_callendar")
     */
    public function index(): Response
    {
        $data = [] ;
        $demandstot = [];
        $admin=$this->getDoctrine()->getManager()->getRepository(User::class)->findAll();
        $company = $this->getDoctrine()->getManager()->getRepository(Company::class)->findAll();

        foreach ($admin as $user) {
            $demands= $this->getDoctrine()->getManager()->getRepository(Demand::class)->findBy(['user' => $user]);
            $demandstot[]= $demands;
        }

        foreach ($demandstot as $demandz){
            foreach ($demandz as $demand){

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
                    'end'   => $demand->getEndDate()->modify('+1 day')->format('Y-m-d H:i:s'),
                    'description' => $demand->getReason(),
                    'backgroundColor' => $color,
                ];
        }}

        $result = json_encode($data);

        return $this->render('super_admin/callendar/index.html.twig', compact('result', 'company' ));
    }

    /**
     * @Route ("/{id}/bycompany", name= "calendar_company_super", methods={"POST", "GET"})
     */
    public function getcalle(Request $request, Company $companiee):Response{

        if ($request->isXmlHttpRequest()) {
            $this->id = $request->get('id');
        }

            $company = $this->getDoctrine()->getManager()->getRepository(Company::class)->findAll();


            $data=[];
            $color = '';
            $demands = $this->getDoctrine()->getManager()->getRepository(Demand::class)->findAllBCampany($request->get('id'));
           // $comp =  $this->getDoctrine()->getManager()->getRepository(Company::class)->findOneBy(['id' => $request->get('id')]);

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
            return $this->render('super_admin/callendar/index.html.twig',compact('result', 'company', 'companiee'));


    }
    /**
     * @Route("ajax", name="calendar_company", methods={"POST", "GET"})
     */
    public function index1(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->get('id');


            $data = [];
            $demandstot = [];
            $admin = $this->getDoctrine()->getManager()->getRepository(User::class)->findAll();
            $company = $this->getDoctrine()->getManager()->getRepository(Company::class)->findAll();

            foreach ($admin as $user) {
                $demands = $this->getDoctrine()->getManager()->getRepository(Demand::class)->findBy(['user' => $user]);
                $demandstot[] = $demands;
            }

            foreach ($demandstot as $demandz) {
                foreach ($demandz as $demand) {

                    if ($demand->getStatus() == 1) {
                        $color = '#17a2b8';
                    } elseif ($demand->getStatus() == 3) {
                        $color = '#28a745';
                    } elseif ($demand->getStatus() == 4) {
                        $color = '#dc3545';
                    } else
                        $color = '#fd7e14';

                    $data[] =
                        [
                            'title' => $demand->getUser()->getLastName() . ' ' . $demand->getUser()->getFirstName(),
                            'start' => $demand->getStartDate()->format('Y-m-d H:i:s'),
                            'end' => $demand->getEndDate()->modify('+1 day')->format('Y-m-d H:i:s'),
                            'description' => $demand->getReason(),
                            'backgroundColor' => $color,
                            'borderColor' => $color,
                        ];
                }
            }

            $result = json_encode($data);
            return $this->render('super_admin/callendar/index.html.twig', compact('result', 'company' ));

        }

    }
    /**
     * @Route("/indexFerieeSuper", name="indexFerieeSuper", methods={"GET", "POST"})
     */
    public function indexFerieeSuper(): Response {

        return $this->render('DIRECTEUR/feriee.html.twig');
    }
    /**
     * @Route("/indexFerieenxSuper", name="indexFerieenxSuper", methods={"GET", "POST"})
     */
    public function indexFerieenxSuper(): Response {

        return $this->render('DIRECTEUR/next.html.twig');
    }
}