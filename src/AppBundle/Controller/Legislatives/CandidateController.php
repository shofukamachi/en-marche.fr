<?php

namespace AppBundle\Controller\Legislatives;

use AppBundle\Entity\LegislativeCandidate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CandidateController extends Controller
{
    /**
     * @Route("/candidat/{slug}", name="legislatives_candidate")
     * @Method("GET")
     */
    public function candidateAction(LegislativeCandidate $candidate): Response
    {
        return $this->render('legislatives/candidate.html.twig', [
            'candidate' => $candidate,
        ]);
    }
}
