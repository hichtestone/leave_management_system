<?php

namespace App\Controller\Admin;

use App\Entity\Demand;
use App\Entity\Notification;
use App\Entity\SupportingDocument;
use App\Entity\User;
use App\Form\DemandType;
use App\Repository\DemandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @Route ("ADMIN/demand")
 * @IsGranted ("ROLE_ADMIN", message="vous n'etes pas autoriser dans ce espace")
 */
class DemandController extends AbstractController
{
    /**
     * @Route("/", name="demand_admin")
     */
    public function index(Request $request): Response
    {
        $date1 = null;
        $date2= null;
        $analyse= $this->getDoctrine()->getManager()->getRepository(Demand::class)->analyse($date1,$date2);
        $company=$request->getSession()->get('company');
        $demandes=$this->getDoctrine()->getManager()->getRepository(Demand::class)->demandewithcompany($company);
        $result=(count($analyse));

        return $this->render('Admin/demand/index.html.twig', [
            'company' => $company,
            'demands' => $demandes,
            'anaylse' => $analyse,
            'result' => $result
        ]);
    }

    /**
     * @Route("/demands", name="demandAdmin", methods={"GET"})
     */
    public function listeDemand(DemandRepository $demandRepository): Response
    {
        return $this->render('TO/demand/index.html.twig', [
            'demands' => $demandRepository->findBy(['user'=>$this->getUser()]),
            'demande' => $demandRepository->findBy(['user' =>$this->getUser(), 'isCancled' => false])
        ]);
    }

    /**
     * @Route ("/{id}/approuved", name="approuve_demand_admin", methods = {"GET", "POST"})
     */
    public function approuveDemand(Request $request, Demand $demand)
    {
        $entityManager= $this->getDoctrine()->getManager();
        $solde = $demand->getUser()->getSolde();
        $date = date_diff($demand->getStartDate(), $demand->getEndDate());
        $day_number= (int)$date->format('%R%a') +1;
        if ($solde  >= $day_number ) {
            $demand->setStatus(3);
            $user = $demand->getUser();
            if ($day_number >= 5) {
                $number_add = (int)($day_number / 5);
                $user->setSolde($solde - ($day_number + $number_add));
            }
            else{
                $user->setSolde($solde - $day_number);
            }
            $notif = new Notification();
            $notif->setDemand($demand);
            $notif->setCreatedAt(new \DateTime('now'));
            $notif->setIsRead(false);
            $notif->setMessage(4);
            $entityManager->persist($notif);
            $entityManager->persist($demand);
            $entityManager->persist($user);
            $this->addFlash('success', 'Aprés avoir verifié le solde , la demande est acceptée');
            $entityManager->flush();
        }
        else{
            $this->addFlash('warning', 'on peut pas accepter la demande puisque le solde est inférieur à la peridé souhaitée');
        }
        return $this->redirectToRoute('demand_admin');
    }
    /**
     * @Route ("/{id}/reject", name="reject_demand_admin", methods = {"GET", "POST"})
     */
    public function rejectDemand(Request $request, Demand $demand)
    {
        $entityManager= $this->getDoctrine()->getManager();
        $demand->setStatus(4);
        $notif = new Notification();
        $notif->setDemand($demand);
        $notif->setCreatedAt(new \DateTime('now'));
        $notif->setIsRead(false);
        $notif->setMessage(5);
        $entityManager->persist($notif);
        $entityManager->persist($demand);
        $entityManager->flush();
        return $this->redirectToRoute('demand_admin');
    }

    /**
     * @Route ("/{id}/analyse", name="demand_analyse_admin", methods={"GET", "POST"})
     */
    public function analyse(Request $request, Demand $demand)
    {
        $company=$request->getSession()->get('company');

        $date1= $demand->getStartDate();
        $date2= $demand->getEndDate();
        $analyse= $this->getDoctrine()->getManager()->getRepository(Demand::class)->analyse1($date1,$date2,$company);
        $result=(count($analyse));
        $this->addFlash('info', 'Durant cette periode vous avez que ' .$result. ' demande accepté');
        if (null ==$result){
            return $this->redirectToRoute('demand_admin');
        }
        $demandes=$this->getDoctrine()->getManager()->getRepository(Demand::class)->demandewithcompany($company);
        $result=(count($analyse));

        return $this->render('Admin/demand/index.html.twig', [
            'company' => $company,
            'demands' => $demandes,
            'anaylse' => $analyse,
            'result' => $result
        ]);

    }

    /**
     * @Route ("ajax", name= "update_demand_send_admin", methods={"POST", "GET"})
     */
    public function update_demand_send(DemandRepository $repository, Request $request){

        if ($request->isXmlHttpRequest()){
            $id=$request->get('id');
            $repository->update_demand_progress($id);
            $notif = new Notification();
            $entityManager= $this->getDoctrine()->getManager();
            $demand= $this->getDoctrine()->getRepository(Demand::class)->findOneBy(['id' => $id] );
            $notif->setDemand($demand);
            $notif->setCreatedAt(new \DateTime('now'));
            $notif->setIsRead(false);
            $notif->setMessage(3);
            $entityManager->persist($notif);
            $entityManager->flush();

        }
    }

    /**
     * @Route("/{id}/edit", name="demandADedit", methods={"GET","POST"})
     */
    public function edit(Request $request, Demand $demand): Response
    {
        $congeaccepted= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 3] );
        $congearefused= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 4] );
        $form = $this->createForm(DemandType::class, $demand);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('demand');
        }

        return $this->render('TO/demand/edit.html.twig', [
            'demand' => $demand,
            'accepted' => $congeaccepted,
            'refused' => $congearefused,
            'form' => $form->createView(),
            'edit' => true,
        ]);
    }

    /**
     * @Route("/new", name="demandADnew", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $congeaccepted= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 3] );
        $congearefused= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 4] );
        $demand = new Demand();
        $form = $this->createForm(DemandType::class, $demand);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();
            $date = date_diff($startDate, $endDate);
            $day_number = (int)$date->format('%R%a') + 1;
            if ($day_number < 0) {
                $this->addFlash('subs', 'La date fin ne doit pas être antérieure à la date début');

            }
            if ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $demand->setUser($this->getUser());
                $demand->setStatus(1);
                $demand->setFilingDate(new \DateTime('now'));
                $demand->setIsCancled(false);
                $notif = new Notification();
                $notif->setDemand($demand);
                $notif->setCreatedAt(new \DateTime('now'));
                $notif->setIsRead(false);
                $notif->setMessage(1);
                $entityManager->persist($notif);
                $ff = $form->getData()->getSupportingDocuments();
                if (!empty ($form->getData()->getSupportingDocuments())) {

                    $supportDocument = new SupportingDocument();
                    foreach ($ff as $f) {
                        $supportDocument->setName($f->getName());
                        $supportDocument->setFilename($f->getFilename());
                        $demand->addSupportingDocument($f);
                        $f->setDemand($demand);
                        //$this->sendConfirmationEmail('mohamed.hammami.1@esprit.tn', $this->getUser()->getfirstName(), $this->getUser()->getlastName());
                    }
                    $entityManager->persist($demand);

                } else {

                    $entityManager->persist($demand);

                }
                $entityManager->flush();

                return $this->redirectToRoute('demandAdmin');
            }
        }

        return $this->render('TO/demand/new.html.twig', [
            'accepted' => $congeaccepted,
            'refused' => $congearefused,
            'demand' => $demand,
            'form' => $form->createView(),
            'edit' => false,

        ]);
    }

    /**
     * @Route("/delete/{id}", name="demandAd_delete", methods={"POST","GET"})
     */
    public function delete(Request $request, Demand $demand): Response
    {
        //$demand=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findOneBy('id' => $request->get
        $entityManager = $this->getDoctrine()->getManager();
        $demand->setIsCancled(true);
        $entityManager->persist($demand);
        $entityManager->flush();


        return $this->redirectToRoute('demandResp_index');
    }

}
