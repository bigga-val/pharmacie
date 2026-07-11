<?php

namespace App\Controller;

use App\Repository\AuditLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/audit')]
#[IsGranted('ROLE_ADMIN')]
class AuditLogController extends AbstractController
{
    #[Route('/', name: 'app_audit_log_index', methods: ['GET'])]
    public function index(Request $request, AuditLogRepository $repo): Response
    {
        $userEmail  = $request->query->get('user');
        $entityName = $request->query->get('entity');
        $action     = $request->query->get('action');
        $fromStr    = $request->query->get('from');
        $toStr      = $request->query->get('to');

        $from = $fromStr ? new \DateTimeImmutable($fromStr) : null;
        $to   = $toStr   ? new \DateTimeImmutable($toStr . ' 23:59:59') : null;

        $logs    = $repo->findFiltered($userEmail, $entityName, $action, $from, $to);
        $entities = $repo->findDistinctEntityNames();

        return $this->render('audit_log/index.html.twig', [
            'logs'        => $logs,
            'entities'    => $entities,
            'filters'     => compact('userEmail', 'entityName', 'action', 'fromStr', 'toStr'),
        ]);
    }
}
