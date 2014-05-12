<?php

namespace c33s\SimpleContentBundle\Menu;

use c33s\MenuBundle\Menu\Menu;
use c33s\SimpleContentBundle\Model\ContentPage;
use c33s\SimpleContentBundle\Model\ContentPageQuery;
use PropelCollection;

/**
 * Description of SimpleContentMenu
 *
 * @author david
 */
class SimpleContentMenu extends Menu
{
    /**
     * This is executed before initialize(). Put awesome stuff here.
     */
    protected function configure()
    {
        $this->addContentItems();
    }
    
    /**
     * Get the default class name to use for first-level items.
     *
     * @return string
     */
    protected function getDefaultItemClass()
    {
        return 'c33s\SimpleContentBundle\Menu\SimpleContentMenuItem';
    }
    
    protected function addContentItems()
    {
        foreach ($this->getContentPages() as $page)
        {
            $key = 'c33s_simple_content_show/'.$page->getName();
            
            $this->itemData[$key] = $this->generateContentChild($page);
        }
    }
    
    protected function generateContentChild(ContentPage $parent)
    {
        $data = array(
            'item_class' => $this->getDefaultItemClass(),
            'children' => array()
        );
        
        foreach ($parent->getChildren() as $page)
        {
            $key = 'c33s_simple_content_show/'.$page->getName();
            
            $data['children'][$key] = $this->generateContentChild($page);
        }
        
        return $data;
    }
    
    /**
     *
     * @return PropelCollection
     */
    protected function getContentPages()
    {
        $root = ContentPageQuery::create()->findRoot();
        if (null === $root)
        {
            return array();
        }
        
        return $root->getBranchAsTree()->getChildren();
    }
}
