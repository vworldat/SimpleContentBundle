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

    /**
     * Project locales
     *
     * @var array
     */
    protected $locales;

    public function __construct($defaultTemplate, $defaultRendererTemplate, $locales)
    {
        $this->defaultTemplate = $defaultTemplate;
        $this->defaultRendererTemplate = $defaultRendererTemplate;

        $this->locales = (array) $locales;
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
     * If the content is empty and localeFallback is true, the system locales (from parameter %locales%) will
     * be walked through in reverse order, starting from the given locale, until a non-empty content is found.
     *
     * @param string $name              Content block name
     * @param string $type              Content type
     * @param string $locale            Locale (optional)
     * @param string $defaultValue      Default value to assign to the block if created
     * @param boolean $localeFallback   If set to true, empty content will be replaced with another locales content
     *                                  in fallback order (reverse %locales%) if available
     *
     * @return ContentBlock
     */
    public function fetchContentBlock($name, $type, $locale = null, $defaultValue = null, $localeFallback = false)
    {
        $this->fetchBlocks();

        $block = $this->doFetchContentBlock($name, $type, $locale, $defaultValue);

        if ('' == $block->getContent() && $localeFallback)
        {
            // search for position of given locale in locales list
            $key = array_search($locale, $this->locales);

            if (false === $key || null === $key || !isset($this->locales[$key - 1]))
            {
                return $block;
            }

            return $this->fetchContentBlock($name, $type, $this->locales[$key - 1], $defaultValue, $localeFallback);
        }

        return $block;
    }

    protected function doFetchContentBlock($name, $type, $locale = null, $defaultValue = null)
    {
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
