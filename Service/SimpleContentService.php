<?php

namespace c33s\SimpleContentBundle\Service;

use c33s\SimpleContentBundle\Model\ContentPage;
use c33s\SimpleContentBundle\Model\ContentPageQuery;
use Criteria;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of SimpleContentService
 *
 * @author david
 */
class SimpleContentService
{
    /**
     *
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     *
     * @var string
     */
    protected $defaultTemplate;
    
    /**
     *
     * @var string
     */
    protected $defaultRendererTemplate;
    
    /**
     *
     * @var array
     */
    protected $pages = array();
    
    public function __construct($defaultTemplate, $defaultRendererTemplate, ContainerInterface $container)
    {
        $this->defaultTemplate = $defaultTemplate;
        $this->defaultRendererTemplate = $defaultRendererTemplate;
        $this->container = $container;
    }
    
    /**
     * Get the template to use for rendering content.
     * 
     * @return string
     */
    public function getRendererTemplateForPage(ContentPage $contentPage)
    {
        if (null !== $contentPage->getContentTypeId())
        {
            return $contentPage->getContentType()->getTemplateName();
        }
        
        return $this->defaultRendererTemplate;
    }
    
    /**
     * Get the template to extend when rendering content.
     * 
     * @return string
     */
    public function getTemplateForPage(ContentPage $contentPage)
    {
        if (null !== $contentPage->getTemplateId())
        {
            return $contentPage->getTemplate()->getTemplateName();
        }
        
        return $this->defaultTemplate;
    }
    
    /**
     * Fetch a content page by its name
     * 
     * @param string $pageName
     * 
     * @return ContentPage
     */
    public function fetchPage($pageName)
    {
        $pageName = (string) $pageName;
        
        if (!array_key_exists($pageName, $this->pages))
        {
            if ('' == $pageName)
            {
                $page = ContentPageQuery::create()
                    ->filterByIsPublished(true)
                    ->filterByName(null, Criteria::ISNULL)
                    ->_or()
                    ->filterByName('')
                    ->findOne()
                ;
            }
            else
            {
                $page = ContentPageQuery::create()
                    ->filterByIsPublished(true)
                    ->filterByName($pageName)
                    ->findOne()
                ;
            }
            
            $this->pages[$pageName] = $page;
        }
        
        return $this->pages[$pageName];
    }
}
