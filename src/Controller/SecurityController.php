<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {

           // dd($this->getUser());
            return $this->redirectToRoute('home');
        }
        if ($this->getUser()) {
          //  dd($this->getUser());
            return $this->redirectToRoute('target_path');
        }
//        $ldap_dn = "cn=read-only-admin,dc=example,dc=com";
//        $ldap_password = "password";
//
//        $ldap_con = ldap_connect("ldap.forumsys.com");
//
//        ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);
//
//        if(ldap_bind($ldap_con, $ldap_dn, $ldap_password)) {
//            $filter = "(ou=scientists)";
//            $result = ldap_search($ldap_con,"dc=example,dc=com",$filter) or exit("Unable to search");
//            $entries = ldap_get_entries($ldap_con, $result);
//            for ($i=0; $i<$entries["count"]; $i++) {
////              dd( "Name is: ". $entries[$i]["name"][0]);
////                dd("Display name is: ". $entries[$i]["displayname"][0]);
////                dd( "Email is: ". $entries[$i]["mail"][0]);
//          //     dump("Telephone number is: ". $entries[$i]["telephonenumber"][0]);
//            }
//         //   dd($entries);
//        ldap_close();
       // }
        // get the login error if there is one

        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/exception", name="exception", methods={"GET"})
     */
    public function exception(): Response
    {

        return $this->render('security/exception.html.twig');
    }
}
