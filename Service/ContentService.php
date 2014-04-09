<?php

namespace c33s\SimpleContentBundle\Service;

use c33s\SimpleContentBundle\Model\ContentPage;
use c33s\SimpleContentBundle\Model\ContentPageQuery;
use Criteria;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of ContentService
 *
 * @author david
 */
class ContentService
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
    protected $baseTemplate;
    
    /**
     *
     * @var string
     */
    protected $contentTemplate;
    
    /**
     *
     * @var array
     */
    protected $pages = array();
    
    public function __construct($baseTemplate, $contentTemplate, ContainerInterface $container)
    {
        $this->baseTemplate = $baseTemplate;
        $this->contentTemplate = $contentTemplate;
        $this->container = $container;
    }
    
    /**
     * Get the template to use for rendering content.
     * 
     * @return string
     */
    public function getContentTemplate()
    {
        return $this->contentTemplate;
    }
    
    /**
     * Get the template to extend when rendering content.
     * 
     * @return string
     */
    public function getBaseTemplate()
    {
        return $this->baseTemplate;
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
                    ->filterByName(null, Criteria::ISNULL)
                    ->findOne()
                ;
            }
            else
            {
                $page = ContentPageQuery::create()
                    ->filterByName($pageName)
                    ->findOne()
                ;
            }
            
            $this->pages[$pageName] = $page;
        }
        
        return $this->pages[$pageName];
    }
}
