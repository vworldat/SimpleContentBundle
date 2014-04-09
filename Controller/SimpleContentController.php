<?php

namespace c33s\SimpleContentBundle\Controller;

use c33s\SimpleContentBundle\Service\ContentService;
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
        /* @var $contentService ContentService */
        $contentService = $this->get('c33s_simple_content');
        
        $contentPage = $contentService->fetchPage($name);
        
        if (null === $contentPage)
        {
            $this->createNotFoundException();
        }
        
        return $this->render($contentService->getContentTemplate(), array(
            'contentPage' => $contentPage, 
            'baseTemplate' => $contentService->getBaseTemplate(),
        ));
    }
}
