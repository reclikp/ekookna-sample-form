<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class MainController extends AbstractController {
    /**
     * @Route("/")
     * @Method({"GET", "POST"})
     */
    public function index() {
        return $this->render('form.html.twig');
    }

    /**
     * @Route("/admin")
     * @Method({"GET", "POST"})
     */
    public function admin() {
        $requests = ["Request No. 1", "Request No. 2"];

        return $this->render('panel.html.twig', array('requests' => $requests));
    }
}