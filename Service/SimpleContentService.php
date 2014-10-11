<?php

namespace C33s\SimpleContentBundle\Service;

use C33s\SimpleContentBundle\Model\ContentPage;
use C33s\SimpleContentBundle\Model\ContentPageQuery;
use C33s\SimpleContentBundle\Model\ContentBlock;
use C33s\SimpleContentBundle\Model\ContentBlockQuery;
use Criteria;
use Symfony\Component\Translation\TranslatorInterface;

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
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Project locales
     *
     * @var array
     */
    protected $locales;

    /**
     * This is used if the translator component is not available.
     *
     * @var string
     */
    protected $defaultLocale = 'en';

    /**
     * Default setting for locale fallback mode.
     *
     * @var boolean
     */
    protected $useLocaleFallbackByDefault;

    public function __construct($defaultTemplate, $defaultRendererTemplate, $locales, $useLocaleFallbackByDefault)
    {
        $this->defaultTemplate = $defaultTemplate;
        $this->defaultRendererTemplate = $defaultRendererTemplate;

        $this->locales = (array) $locales;
        $this->useLocaleFallbackByDefault = $useLocaleFallbackByDefault;
    }

    /**
     * Optional translator injection.
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator = null)
    {
        $this->translator = $translator;
    }

    /**
     * Check if the SimpleContentService is using locale fallback by default.
     *
     * @return boolean
     */
    public function isUsingLocaleFallback()
    {
        return $this->useLocaleFallbackByDefault;
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
     * automatically and assigned the given default values if available.
     *
     * If the content is empty and useLocaleFallback is true, the system locales (from parameter %locales%) will
     * be walked through in reverse order, starting from the given locale, until a non-empty content is found.
     *
     * @param string $name                  Content block name
     * @param string $locale                Locale
     * @param boolean $useLocaleFallback    If set to true or enabled globally, blocks with the same name but different locale will be returned
     *                                      in fallback order (reverse %locales%) if available.
     * @param string $defaultType           Content type (used for auto-creation)
     * @param string $defaultValue          Default value (used for auto-creation)
     *
     * @return ContentBlock
     */
    public function getOrCreateContentBlock($name, $locale = null, $useLocaleFallback = null, $defaultType = null, $defaultValue = null)
    {
        $autoLocale = false;
        if (null === $locale)
        {
            $locale = (null !== $this->translator) ? $this->translator->getLocale() : $this->defaultLocale;
            $autoLocale = true;
        }

        if (null === $useLocaleFallback)
        {
            $useLocaleFallback = $this->isUsingLocaleFallback();
        }

        $block = $this->getContentBlock($name, $locale, $useLocaleFallback);
        if (null !== $block && ($autoLocale || $block->getLocale() == $locale))
        {
            return $block;
        }

        if (null === $defaultType || null === $defaultValue)
        {
            // no default type and value, don't auto-create
            return null;
        }

        // Let's create a new block based on the default values

        // If the locale was determined automatically AND we are in fallback mode, the new block is added for the default locale
        // instead of the current one. This way we won't get different localed blocks with the same default value.
        if ($autoLocale && $useLocaleFallback)
        {
            $locale = (null !== $this->translator) ? reset($this->locales) : $this->defaultLocale;
        }

        return $this->createContentBlock($name, $locale, $defaultType, $defaultValue);
    }

    /**
     * Check if a content block with the given name and locale exists.
     *
     * @param string $name
     * @param string $locale
     *
     * @return boolean
     */
    public function hasContentBlock($name, $locale)
    {
        $this->fetchBlocks();

        return isset($this->blocks[$name][$locale]);
    }

    /**
     * Get the content block with the given parameters. Returns NULL if no block was found.
     *
     * @param string $name
     * @param string $locale
     * @param boolean $useLocaleFallback
     *
     * @return ContentBlock
     */
    public function getContentBlock($name, $locale, $useLocaleFallback = false)
    {
        $this->fetchBlocks();

        if ($this->hasContentBlock($name, $locale))
        {
            return $this->blocks[$name][$locale];
        }
        elseif ($useLocaleFallback)
        {
            // search for position of given locale in locales list
            $key = array_search($locale, $this->locales);

            if (false !== $key && null !== $key && isset($this->locales[$key - 1]))
            {
                return $this->getContentBlock($name, $this->locales[$key - 1], $useLocaleFallback);
            }
        }

        return null;
    }

    /**
     * Get all locales for the given block that have not been saved yet.
     *
     * @param string $name
     *
     * @return array
     */
    public function getMissingLocalesForBlockName($name)
    {
        $locales = array();
        foreach ($this->locales as $locale)
        {
            if (!$this->hasContentBlock($name, $locale))
            {
                $locales[$locale] = $locale;
            }
        }

        return $locales;
    }

    /**
     * Create content block in database.
     *
     * @param string $name      Content block name
     * @param string $locale    Locale
     * @param string $type      Content type
     * @param string $content   Default value to assign to the block
     *
     * @return ContentBlock
     */
    protected function createContentBlock($name, $locale, $type, $content)
    {
        $block = new ContentBlock();
        $block
            ->setName($name)
            ->setLocale($locale)
            ->setType($type)
            ->setContent($content)
            ->save()
        ;

        $this->blocks[$name][$locale] = $block;

        return $block;
    }
}
