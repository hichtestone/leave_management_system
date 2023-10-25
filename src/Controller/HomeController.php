<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Department;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\DemandRepository;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\IpUtils;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Session\Session;


class HomeController extends AbstractController
{

    /**
     * @Route("/home", name="home")
     */
    public function index(Request $request,DemandRepository $demandRepository): Response
    {
        $country="";
        $departements0=[];
        $companies=$this->getDoctrine()->getRepository(Company::class)->findAll();
        foreach ($companies as $company)
        {
            $dep=$this->getDoctrine()->getRepository(Department::class)->findBy(['company'=>$company]);
            foreach ($dep as $d){
                array_push($departements0,$d) ;
            }

        }
        //dd($departements0);
        if ($this->getUser()->getDepartment() != null) {
        $depdirecteurs=$this->getDoctrine()->getRepository(Department::class)->findBy(['company'=>$this->getUser()->getDepartment()->getCompany()]);
           $country=$this->getUser()->getDepartment()->getCompany()->getCountry();
        }
      //  dd($depdirecteurs);

        else {
            $depdirecteurs = [];
        }
        $conges  = $demandRepository->findCongesByuser($this->getUser(),3);
        $conges1 = $demandRepository->findCongesByuser($this->getUser(),2);
        $conges2 = $demandRepository->findCongesByuser($this->getUser(),4);
        $conges3 = $demandRepository->findCongesByuser($this->getUser(),1);

        $totalDdeValied  = $demandRepository->findBy(['status'=>3]);
        $totalDdeRefuse  = $demandRepository->findBy(['status'=>4]);
        $totalDdeEncours = $demandRepository->findBy(['status'=>2]);
        $totalDdeEnvoyes = $demandRepository->findBy(['status'=>1]);
        $totalcongesbydep=[];
        foreach ( $depdirecteurs as $dep){
          array_push($totalcongesbydep, count($this->getDoctrine()->getManager()->getRepository(User::class)->findUserByDepartement($dep->getLabel()))) ;
        }

        $totalEmplDev  = $this->getDoctrine()->getManager()->getRepository(User::class)->findUserByDepartement('Dev');
        $totalEmplTech = $this->getDoctrine()->getManager()->getRepository(User::class)->findUserByDepartement('Tech');
        $totalEmplTo   = $this->getDoctrine()->getManager()->getRepository(User::class)->findUserByDepartement('To');
        $totalEmplResp = $this->getDoctrine()->getManager()->getRepository(User::class)->findUserByDepartement('Responsable');

        $totalbydep =[];
        $deplabels  =[];
        $departements = $this->getDoctrine()->getRepository(Department::class)->findAll();

        foreach ($departements as $departement){
            array_push($deplabels,$departement->getLabel());
            array_push($totalbydep,count($demandRepository->findCongesByDepartement($departement,3)))  ;
            }
       $soldeconsomme=0;

        foreach ($conges as $conge){
                    $x=date_diff($conge->getStartDate() , $conge->getEndDate());
                    $soldeconsomme+= (int)$x->format('%R%a');
              }

        $u=$this->getDoctrine()->getManager()->getRepository(User::class)->IsAdmin($this->getUser()->getId());

        if(isset($u[0])){
        if( $u[0]!= null){
            return $this->redirectToRoute("choiceCompany");
        }}
        else {
            return $this->render('home/index.html.twig', [
                'totalcongesbydep'=>$totalcongesbydep,
                'depdirecteur'=>$depdirecteurs,
                'departements'=>$departements0,
                'conges' => $conges,
                'conges1' => $conges1,
                'conges2' => $conges2,
                'conges3' => $conges3,
                'totalDdeValied' => $totalDdeValied,
                'totalDdeRefuse' => $totalDdeRefuse,
                'totalDdeEncours' => $totalDdeEncours,
                'totalDdeEnvoyes' => $totalDdeEnvoyes,
                'deplabels' => $departements,
                'totalbydep' => $totalbydep,
                'soldeconsomme' => $soldeconsomme,
                'totalempldev' => $totalEmplDev,
                'totalemptech' => $totalEmplTech,
                'totalemplto' => $totalEmplTo,
                'totalempres' => $totalEmplResp,
                'country'=>$country
            ]);
        }
    }

    /**
     * @Route("/choiceCompany", name="choiceCompany")
     */
    public function choiceCompany(): Response
    {
        $companies=$this->getUser()->getCompanies();

        return $this->render('home/choiceCompany.html.twig',[
            'companies'=>$companies,
        ]);
    }

    /**
     * @Route("/{id}/homewithcompany", name="gethome")
     */
    public function toHome(Company $company,Request $request,DemandRepository $demandRepository): Response
    {

        $session = $request->getSession();
        $session->set('company', $company->getId());
        $conges=$demandRepository->findCongesByuser($this->getUser(),3);
        $conges1=$demandRepository->findCongesByuser($this->getUser(),2);
        $conges2=$demandRepository->findCongesByuser($this->getUser(),4);
        $conges3=$demandRepository->findCongesByuser($this->getUser(),1);
//ttttt
        $totalDdeValied=$demandRepository->demandewithcompanyStatus($company,3);

        $totalDdeRefuse=$demandRepository->demandewithcompanyStatus($company,4);
        $totalDdeEncours=$demandRepository->demandewithcompanyStatus($company,2);
        $totalDdeEnvoyes=$demandRepository->demandewithcompanyStatus($company,1);
       // dd($totalDdeEnvoyes);
//ttttt
       $departements0=$this->getDoctrine()->getManager()->getRepository(Department::class)->getAllDepByCopany($company);


        $totalbydep=[];
        $deplabels=[];
        $departements=$this->getDoctrine()->getRepository(Department::class)->getAllDepByCopany($company);
        foreach ($departements as $departement){
            //    dd(count($demandRepository->findCongesByDepartement($departement,3)));
            array_push($deplabels,$departement->getLabel());
            array_push($totalbydep,count($demandRepository->findCongesByDepartement($departement,3)))  ;
        }
        $soldeconsomme=0;

        foreach ($conges as $conge){
            $x=date_diff($conge->getStartDate() , $conge->getEndDate());
            $soldeconsomme+= (int)$x->format('%R%a');
        }
        $depdirecteurs=[];
        $totalcongesbydep=[];
        return $this->render('home/index.html.twig', [
            'totalcongesbydep'=>$totalcongesbydep,
            'depdirecteur'=>$depdirecteurs,
            'departements'=>$departements0,
            'conges' => $conges,
            'conges1' => $conges1,
            'conges2' => $conges2,
            'conges3' => $conges3,
            'totalDdeValied' => $totalDdeValied,
            'totalDdeRefuse' => $totalDdeRefuse,
            'totalDdeEncours' => $totalDdeEncours,
            'totalDdeEnvoyes' => $totalDdeEnvoyes,
            'deplabels' => $departements,
            'totalbydep' => $totalbydep,
            'soldeconsomme' => $soldeconsomme,

        ]);
    }

    /**
     * @Route("/historiquepdf", name="historiquepdf")
     */
    public function historiquePdf(DemandRepository $demandRepository): Response
    {
        $conges=$demandRepository->findCongesByuser($this->getUser(),3);
        $conges1=$demandRepository->findCongesByuser($this->getUser(),4);
        //dd($this->getUser());
        $logo=$_SERVER["DOCUMENT_ROOT"].'/images/companies/'.$this->getUser()->getDepartment()->getCompany()->getLogo();
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('TO/demand/historique.html.twig', [
            'conges' => $conges,
            'congesrefuse'=>$conges1,
            "logo" => $logo
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true,

        ]);
    }

    /**
     * @Route ("/notifications", name="notificationsAdmin")
     */
    public function notifications(NotificationRepository $repository) : Response
    {
        $notifications = [];
        $notifications2= $repository->notif_number($this->getUser());
        $notifications1 = $repository->notif_number_admin();
        $notifications= array_merge($notifications2,$notifications1);
       return $this->render('notification/index.html.twig', [
           'notifications' => $notifications,
       ]);
    }
    /**
     * @Route ("/SuperAdmin/notifications", name="notificationsSuperAdmin")
     */
    public function notificationsSuperAdmin(NotificationRepository $repository) : Response
    {
        $notifications= $repository->notif_number_super_admin();
        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * @Route ("/TO/notifications", name="notificationsTO")
     */
    public function notificationsTO(NotificationRepository $repository) : Response
    {
        $n = [];
        $notifications= $repository->notif_number($this->getUser());
        $notifications1 = $repository->notif_number1($this->getUser());
        $n= array_merge($notifications,$notifications1);
        return $this->render('notification/index.html.twig', [
            'notifications' => $n,
        ]);
    }

    public function getcomp(Request $request) {
        $company = $this->getDoctrine()->getManager()->getRepository(Company::class)->findOneBy([
            'id' => $request->getSession()->get('company')]);
        return $this->render('layouts/comapn.html.twig', [
            'company' => $company
        ]);
    }
    public function notifTo(NotificationRepository $repository)
    {
        $notif = [];
        $notifications= $repository->notif_number($this->getUser());
        $notifications1 = $repository->notif_number1($this->getUser());
        $notif= array_merge($notifications,$notifications1);
        $notifs_number = count($notif);


        return $this->render('home/_notif.html.twig', [
            'notifications' => $notif,
            'notif_numbers' => $notifs_number,
            'path'=>'notificationsTO'
        ]);
    }

    public function notifDirecteurAdmin(NotificationRepository $repository)
    {
        $notif = [];
        $notifications= $repository->notif_number($this->getUser());
        $notifications1 = $repository->notif_number_admin();
        $notif= array_merge($notifications,$notifications1);
        $notifs_number = count($notif);


        return $this->render('home/_notif.html.twig', [
            'notifications' => $notif,
            'notif_numbers' => $notifs_number,
            'path'=>'notificationsAdmin'
        ]);
    }
    public function notifAdmin(NotificationRepository $repository)
    {
        $notifications= $repository->notif_number_admin();



            $notifs_number = count($repository->notif_number_admin());


        return $this->render('home/_notif.html.twig', [
            'notifications' => $notifications,
            'notif_numbers' => $notifs_number,
            'path'=>'notificationsAdmin'
        ]);
    }
    public function notifSuperAdmin(NotificationRepository $repository)
    {
        $notifications= $repository->notif_number_super_admin();



        $notifs_number = count($repository->notif_number_super_admin());


        return $this->render('home/_notif.html.twig', [
            'notifications' => $notifications,
            'notif_numbers' => $notifs_number,
            'path'=>'notificationsSuperAdmin'
        ]);
    }
    /**
     * @Route ("ajax", name="update_notif_lu", methods= {"GET", "POST"})
     *
     */

    public function updatedNotifRead( NotificationRepository $repository, Request $request)
    {
        if ($request->isXmlHttpRequest()){
            $notif_id= $request->get('notif_id');
            $repository->update_notif($notif_id);
        }
    }




}
