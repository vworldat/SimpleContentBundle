<?php

namespace C33s\SimpleContentBundle\Menu;

use C33s\MenuBundle\Exception\OptionRequiredException;
use C33s\MenuBundle\Item\MenuItem;
use C33s\MenuBundle\Menu\Menu;
use C33s\SimpleContentBundle\Model\ContentPage;
use C33s\SimpleContentBundle\Service\SimpleContentService;

/**
 * Description of SimpleContentMenuItem
 *
 * @author david
 */
class SimpleContentMenuItem extends MenuItem
{
    /**
     *
     * @var ContentPage
     */
    protected $simpleContentPage;
    protected $simplePageName;
    
    /**
     * Construct a new menu item. It requires its routeName, options and
     * the menu the item is assigned to.
     *
     * SimpleContentMenuItem requires the following routeName notation:
     * routeName/pageName
     *
     * @see MenuItem::__construct()
     *
     * @throws OptionRequiredException
     *
     * @param string $routeName
     * @param array $options
     * @param Menu $menu
     */
    public function __construct($routeName, array $options, Menu $menu)
    {
        if (false === strpos($routeName, '/'))
        {
            throw new OptionRequiredException('SimpleContentMenuItem requires routeName/pageName notation');
        }
        
        list($routeName, $this->simplePageName) = explode('/', $routeName, 2);
        
        parent::__construct($routeName, $options, $menu);
    }
    
    /**
     * Initialize the item's option values.
     */
    protected function initOptions()
    {
        $this->fetchContentPage();
        
        parent::initOptions();
        
        $this->matchRequestVariables['name'] = $this->simplePageName;
    }
    
    /**
     * @param string $pageName
     */
    protected function fetchContentPage()
    {
        $contentService = $this->container->get('c33s_simple_content');
        /* @var $contentService SimpleContentService */
        
        $this->simpleContentPage = $contentService->fetchPage($this->simplePageName);
    }
    
    /**
     * Fetch the item's "title" option.
     *
     * @return MenuItem
     */
    protected function fetchTitle()
    {
        try
        {
            $this->fetchOption('title', true);
        }
        catch (OptionRequiredException $e)
        {
            // no title was set manually
            if (null !== $this->simpleContentPage)
            {
                $this->title = $this->simpleContentPage->getTitle();
            }
            else
            {
                $this->title = $this->simplePageName;
            }
        }
        
        return $this;
    }
    
    /**
     * Check if the item should be rendered as enabled (with link)
     * or not (just title, no link).
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return null !== $this->simpleContentPage && parent::isEnabled();
    }
    
    /**
     * Add variable values defined in $addRequestVariables to the given
     * urlParameters. This can be used to pass through generally available
     * request parameters.
     *
     * @param array $urlParameters
     *
     * @return array
     */
    protected function addRequestVariablesToUrlParameters(array $urlParameters)
    {
        $urlParameters = parent::addRequestVariablesToUrlParameters($urlParameters);
        $urlParameters['name'] = $this->simplePageName;
        
        return $urlParameters;
    }
}
