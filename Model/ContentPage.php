<?php

namespace c33s\SimpleContentBundle\Model;

use c33s\SimpleContentBundle\Model\om\BaseContentPage;
use PropelPDO;

class ContentPage extends BaseContentPage
{
    public function preSave(PropelPDO $con = null)
    {
        // we have to use preSave() instead of preInsert() because the nested set hooks won't work in preInsert()
        if (!$this->isNew() || $this->isInTree())
        {
            // not new object or some tree config seems already to be here
            return parent::preSave($con);
        }
        
        $rootElement = ContentPageQuery::create()
            ->findRoot()
        ;
        
        if (null === $rootElement)
        {
            $this->makeRoot();
        }
        else
        {
            $this->insertAsLastChildOf($rootElement);
        }
        
        return parent::preSave($con);
    }
    
    public function preInsert(PropelPDO $con = null)
    {
        // This is a hack to allow #-prefixed headers in yaml fixtures
        $this->setContent(str_replace('---[too bad we have to do this]---###', '###', $this->getContent()));
        
        return parent::preInsert();
    }
    
    /**
     * Fetch all branch elements and assign them as children recursively.
     * The elements will be available through $contentPage->getNestedSetChildren()
     * and $contentPage->getParent()
     * 
     * @return ContentPage
     */
    public function getBranchAsTree()
    {
        $lastParents = array();
        
        foreach ($this->getBranch() as $page)
        {
            if (!$page->isRoot())
            {
                $lastParents[$page->getLevel() - 1]->addNestedSetChild($page);
            }
            
            $lastParents[$page->getLevel()] = $page;
        }
        
        return $this;
    }
}
