<?php

namespace C33s\SimpleContentBundle\Service;

use C33s\SimpleContentBundle\Model\ContentPage;
use C33s\SimpleContentBundle\Model\ContentPageQuery;
use C33s\SimpleContentBundle\Model\ContentBlock;
use C33s\SimpleContentBundle\Model\ContentBlockQuery;
use Criteria;

/**
 * Description of SimpleContentService
 *
 * @author david
 */
class SimpleContentService
{
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

    /**
     *
     * @var array
     */
    protected $blocks;

    public function __construct($defaultTemplate, $defaultRendererTemplate)
    {
        $this->defaultTemplate = $defaultTemplate;
        $this->defaultRendererTemplate = $defaultRendererTemplate;
    }

    /**
     * Fetch all blocks in one query to minimize db access.
     */
    protected function fetchBlocks()
    {
        if (null === $this->blocks)
        {
            $this->blocks = array();

            $allBlocks = ContentBlockQuery::create()
                ->find()
            ;

            foreach ($allBlocks as $block)
            {
                /* @var $block ContentBlock */
                $this->blocks[$block->getName()][$block->getLocale()] = $block;
            }
        }
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

    /**
     * Fetch the ContentBlock object with the given name and locale.
     * If no block with the given parameters exists, it is created
     * automatically and assigned the given default value.
     *
     * @param string $name          Content block name
     * @param string $type          Content type
     * @param string $locale        Locale (optional)
     * @param string $defaultValue  Default value to assign to the block if created
     *
     * @return ContentBlock
     */
    public function fetchContentBlock($name, $type, $locale = null, $defaultValue = null)
    {
        $this->fetchBlocks();

        $localeString = (string) $locale;
        if (!isset($this->blocks[$name][$localeString]))
        {
            $this->blocks[$name][$localeString] = $this->getOrCreateContentBlock($name, $type, $locale, $defaultValue);
        }

        return $this->blocks[$name][$localeString];
    }

    /**
     * Load content block from database or create it.
     *
     * @param string $name          Content block name
     * @param string $type          Content type
     * @param string $locale        Locale (optional)
     * @param string $defaultValue  Default value to assign to the block if created
     *
     * @return ContentBlock
     */
    protected function getOrCreateContentBlock($name, $type, $locale = null, $defaultValue = null)
    {
        $block = ContentBlockQuery::create()
            ->filterByName($name)
            ->filterByType($type)
            ->filterByLocale($locale)
            ->findOne()
        ;

        if (null === $block)
        {
            $block = new ContentBlock();
            $block
                ->setName($name)
                ->setLocale($locale)
                ->setType($type)
                ->setContent($defaultValue)
                ->save()
            ;
        }

        return $block;
    }
}
