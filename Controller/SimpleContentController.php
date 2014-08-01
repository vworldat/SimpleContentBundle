<?php

namespace C33s\SimpleContentBundle\Controller;

use C33s\SimpleContentBundle\Service\SimpleContentService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SimpleContentController extends Controller
{
    /**
     * 
     * @param string $name
     * 
     * @return type
     */
    public function showAction($name = null)
    {
        $contentService = $this->get('c33s_simple_content');
        /* @var $contentService SimpleContentService */
        
        $contentPage = $contentService->fetchPage($name);
        
        if (null === $contentPage)
        {
            return $this->createNotFoundException();
        }
        
        return $this->render('c33sSimpleContentBundle::show.html.twig', array(
            'contentPage' => $contentPage, 
            'baseTemplate' => $contentService->getTemplateForPage($contentPage),
            'rendererTemplate' => $contentService->getRendererTemplateForPage($contentPage),
        ));
    }
}
