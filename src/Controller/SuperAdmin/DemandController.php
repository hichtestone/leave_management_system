<?php

namespace App\Controller\SuperAdmin;

use App\Command\SoldeCommand;
use App\Entity\Demand;
use App\Entity\Notification;
use App\Entity\SupportingDocument;
use App\Entity\User;
use App\Form\DemandType;
use App\Repository\DemandRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @Route ("SuperAdmin/demand")
 * @IsGranted ("ROLE_SUPER_ADMIN", message="vous n'etes pas autoriser dans ce espace")
 */
class DemandController extends AbstractController
{
    private $em;
    private $parameters;
    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameters)
    {
        $this->em=$em;
        $this->parameters=$parameters;
    }

    /**
     * @Route("/", name="demand_super_admin")
     */
    public function index(): Response
    {
        $date1 = null;
        $date2= null;
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->getUserAdmin();
        $demands=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findall($users);
        $analyse= $this->getDoctrine()->getManager()->getRepository(Demand::class)->analyse($date1,$date2);
        $result=(count($analyse));
      //  dd($demands);
        return $this->render('super_admin/demand/index.html.twig', [
            'demands' => $demands,
            'anaylse' => $analyse,
            'result' => $result
        ]);
    }
    /**
     * @Route("/deleteDemand", name="deleteDemand", methods={"GET"})
     */
    public function deleteDemand(Request $request,DemandRepository $demandRepository,NotificationRepository $notificationRepository): Response
    {
      // dd($request);
            $demand_id=$request->get('id');
            $notif=$notificationRepository->findBy(array("demand" => $demand_id));
           //dd($notif);
        //delete notif
        foreach($notif as $notification){
            $notificationRepository->removeNotif($notification->getId());
        }

        //delete demand
        if($notificationRepository->findBy(array("demand" => $demand_id))==[]){
            $demandRepository->remove($demand_id);
        }



        $date1 = null;
        $date2= null;
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->getUserAdmin();
        $demands=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findall($users);
        $analyse= $this->getDoctrine()->getManager()->getRepository(Demand::class)->analyse($date1,$date2);
        $result=(count($analyse));
        //  dd($demands);
        return $this->render('super_admin/demand/index.html.twig', [
            'demands' => $demands,
            'anaylse' => $analyse,
            'result' => $result
        ]);
    }

    /**
     * @Route("/demands", name="demandSP", methods={"GET"})
     */
    public function listeDemand(DemandRepository $demandRepository): Response
    {
        return $this->render('TO/demand/index.html.twig', [
            'demands' => $demandRepository->findBy(['user'=>$this->getUser()]),
            'demande' => $demandRepository->findBy(['user' =>$this->getUser(), 'isCancled' => false])
        ]);
    }

    /**
     * @Route ("/{id}/approuved", name="approuve_demand_super_admin", methods = {"GET", "POST"})
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
        return $this->redirectToRoute('demand_super_admin');
    }
    /**
     * @Route ("/{id}/reject", name="reject_demand_super_admin", methods = {"GET", "POST"})
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
        return $this->redirectToRoute('demand_super_admin');
    }

    /**
     * @Route ("/{id}/analyse", name="demand_analyse_super_admin", methods={"GET", "POST"})
     */
    public function analyse(Request $request, Demand $demand)
    {
        $demands = [];
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->getUserAdmin();
        $demands=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findallDemandUserNotAdmin($users);
        $date1= $demand->getStartDate();
        $date2= $demand->getEndDate();
        $analyse= $this->getDoctrine()->getManager()->getRepository(Demand::class)->analyse($date1,$date2);
        $result=(count($analyse));
        $this->addFlash('info', 'Durant cette periode vous avez que ' .$result. ' demande accepté');
        if (null ==$result){
            return $this->redirectToRoute('demand_super_admin');
        }

        return $this->render('DIRECTEUR/demand/index.html.twig', [
            'anaylse'=>$analyse,
            'demands' => $demands,
            'result' => $result
        ]);
    }

    /**
     * @Route ("ajax", name= "update_demand_send_super_admin", methods={"POST", "GET"})
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



}
