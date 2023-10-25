<?php

namespace App\Controller\SuperAdmin;

use App\Entity\Company;

use App\Entity\Department;
use App\Entity\User;

use App\Form\CompanyType;
use App\Form\DepartmentType;

use App\Form\CompanyForm;

use Doctrine\DBAL\Types\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
/**
 * @Route("/SuperAdmin/company")
 * @IsGranted("ROLE_SUPER_ADMIN", message="vous n'etes pas autoriser dans ce espace")
 */
class CompanyController extends AbstractController
{
    /**
     * @Route("/", name="super_admin_company")
     */
    public function index(Request $request): Response
    {
        $companies = $this->getDoctrine()->getManager()->getRepository(Company::class)->findAll();
        $admin = $this->getDoctrine()->getManager()->getRepository(User::class)->getUserAdmins();
        if ($request->isXmlHttpRequest()){
            $id=$request->get('checked');
        }
        return $this->render('super_admin/company/index.html.twig', [
            'companies' => $companies,
            'admin' => $admin
        ]);
    }
    /**
     * @Route("ajax", name="affectAdmin", methods={"GET"})
     */
        public function affecter(Request $request)
    {

        if ($request->isXmlHttpRequest()){
            $users= $this->getDoctrine()->getManager()->getRepository(User::class)->findBy(['id' => $request->get('userid')]);
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($request->get('tab') as $t) {
                $company= $this->getDoctrine()->getManager()->getRepository(Company::class)->findOneBy(['id' => $t]);
                foreach ($users as $user){
                    $company->addUser($user);

                }
                $entityManager->persist($company);
                $entityManager->flush();
            }
            return $this->redirectToRoute('super_admin_company');
        }
    }

    /**

     * @Route ("/{id}/show",name="company_show")
     */
    public function show(Company $company)
    {
        $users=[];
        $departments=$this->getDoctrine()->getRepository(Department::class)->findBy(['company'=>$company]);
        foreach ($departments as $dep){
            foreach ($dep->getUsers() as $user){
                array_push($users,$user);
            }
        }
      return  $this->render('super_admin/company/show.html.twig',[
          'users'=>$users,
          'company'=>$company,
          'departments'=>$departments,
      ]);
    }

    /**
     * @Route ("/{id}/new_department",name="new_department")
     */
    public function addDepartment(Company $company, Request $request): Response
    {
        $department= new Department();
        $department->setCompany($company);
        $department->setLabel($request->get('label'));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($department);
        $entityManager->flush();
        $this->addFlash('success', 'Département ajouté avec succès');

        return $this->redirectToRoute('company_show',['id'=>$company->getId()]);
    }

    /**
     * @Route("/{id}/show_departement", name="company_departement_show", methods={"GET"})
     */
    public function showdepartment(Department $department): Response
    {
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->findBy(['department' => $department ]);
        $responsable1=[];
        $responsable=[];
        foreach ($users as $user){
            if($user->getUser() != null && $user->getUser()->getDepartment() == $department)
                $responsable1[]=$user->getUser();
        }

        foreach ($responsable1 as $res){
            $responsable=$res->getUser();
        }
        $allusers=$this->getDoctrine()->getManager()->getRepository(User::class)->findAll();

        return $this->render('Admin/department/show.html.twig', [
            'department' => $department,
            'users'      => $users,
            'allusers'   => $allusers,
            'responsable'=>$responsable,
            'responsables'=>$responsable1,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="company_department_delete", methods={"GET", "POST"})
     */
    public function deletedepartement(Department $department): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->findBy(['department' => $department ]);
        foreach ($users as $user) {
            $user->setDepartment(null);
        }

        $entityManager->remove($department);
        $entityManager->flush();

        return $this->redirectToRoute('department');
    }

    /**
     * @Route("/new", name="new_campany", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $company->setCreatedAt(new \DateTime('now'));
            $entityManager->persist($company);
            $entityManager->flush();

            return $this->redirectToRoute('super_admin_company');
        }

        return $this->render('company/new.html.twig', [
            'company' => $company,
            'form'    => $form->createView(),
            'edit'    => false,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit_company", methods={"GET","POST"})
     */
    public function edit(Request $request, Company $company): Response
    {
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('super_admin_company');
        }

        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'form'    => $form->createView(),
            'edit'    => true,
        ]);
    }

    /**
     * @Route ("/{id}/deleteCompany", name="delete_company", methods={"GET", "POST"})
     */
    public function deleteCompany(Company $company)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $departments=$this->getDoctrine()->getManager()->getRepository(Department::class)->findBy(['company' => $company ]);
        foreach ($departments as $dep) {
            $users=$this->getDoctrine()->getManager()->getRepository(User::class)->findBy(['department' => $dep]);
            foreach ($users as $user) {
                $user->setDepartment(null);
            }

            $dep->setCompany(null);
        }

        $entityManager->remove($company);
        $entityManager->flush();

        return $this->redirectToRoute('super_admin_company');

    }

}
