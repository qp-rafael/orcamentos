<?php

namespace Orcamentos\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Orcamentos\Service\Project as ProjectService;

use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrapView;
use Pagerfanta\Adapter\ArrayAdapter;

class ProjectController
{
	public function index(Request $request, Application $app, $page)
	{
		$em = $app['orm.em'];
		$projects = $em->getRepository('Orcamentos\Model\Project')->findAll();


		$adapter = new ArrayAdapter($projects);
		$pagerfanta = new Pagerfanta($adapter);
		$pagerfanta->setCurrentPage($page);
		$view = new TwitterBootstrapView();
		$routeGenerator = function($page) use ($app) {
	        return '/user/'.$page;
	    };
		$htmlPagination = $view->render( $pagerfanta, $routeGenerator, array());
		return $app['twig']->render('project/index.twig', array( 
			'htmlPagination' => $htmlPagination,
			'pagerfanta' => $pagerfanta,
			'active_page' => 'project'
		));
	}

	public function edit(Request $request, Application $app, $projectId)
	{
		$em = $app['orm.em'];
		$project = null;
		if ( isset($projectId) ) {
			$project = $app['orm.em']->getRepository('Orcamentos\Model\Project')->find($projectId);
		}
		$clients = $em->getRepository('Orcamentos\Model\Client')->findAll();
		return $app['twig']->render('project/edit.twig', 
			array(
				'clients' => $clients,
				'project' => $project,
				'active_page' => 'project'
			)
		);
	}

	// Funcao usada para criar o cliente, via post
	public function create(Request $request, Application $app)
	{	
		$data = $request->request->all();
		
		// Pegar da session
		$data['companyId'] = 1;

    	$data = json_encode($data);

		$projectService = new ProjectService();
		$project = $projectService->save($data, $app['orm.em']);

		return $app->redirect('/project');
	}

	public function detail(Request $request, Application $app, $projectId )
	{
		$project = $app['orm.em']->getRepository('Orcamentos\Model\Project')->find($projectId);
		return $app['twig']->render('project/detail.twig', array( 
			'project' => $project,
			'active_page' => 'project'
		));
	}

}