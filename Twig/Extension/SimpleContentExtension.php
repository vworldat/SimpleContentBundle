<?php

namespace C33s\SimpleContentBundle\Twig\Extension;

use C33s\SimpleContentBundle\Service\SimpleContentService;
use Symfony\Component\Translation\TranslatorInterface;
use C33s\SimpleContentBundle\Model\ContentBlock;
use Knp\Bundle\MarkdownBundle\Helper\MarkdownHelper;

class SimpleContentExtension extends \Twig_Extension
{
    /**
     * @var SimpleContentService
     */
    protected $contentService;

    /**
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     *
     * @var MarkdownHelper
     */
    protected $markdownHelper;

    /**
     * @param SimpleContentService  $contentService
     * @param MarkdownHelper        $markdownHelper
     */
    public function __construct(SimpleContentService $contentService, MarkdownHelper $markdownHelper)
    {
        $this->contentService = $contentService;
        $this->markdownHelper = $markdownHelper;
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

    public function getFilters()
    {
        return array(
            // c33s_content_block syntax is now deprecated. Replace with c33s_content or any of the shortcuts.
            new \Twig_SimpleFilter('c33s_content_block', array($this, 'contentBlockFilter'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('c33s_content', array($this, 'contentBlockFilter'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('c33s_content_line', array($this, 'contentBlockFilterLine'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('c33s_content_text', array($this, 'contentBlockFilterText'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('c33s_content_markdown', array($this, 'contentBlockFilterMarkdown'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('c33s_content_html', array($this, 'contentBlockFilterHtml'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Fetch content block with the given default content and name.
     * This allows to wrap existing content with a filter block.
     *
     * If a locale different from the current locale is given, the block is fetched/created,
     * but not displayed.
     *
     * Implemented / allowed types so far:
     *  * line      Single line, escaped (default)
     *  * text      Multiline, escaped
     *  * markdown  Markdown, not escaped
     *  * html      HTML, not escaped
     *
     * @throws \InvalidArgumentException    If given type is unknown
     *
     * @param string $defaultContent    Default content to assign if block has to be created
     * @param string $name              Block name
     * @param string $type              Block type (defaults to 'line')
     * @param string $locale            Specific locale to use instead of current locale
     * @param string $useLocaleFallback Override global use_locale_fallback setting
     *
     * @return string
     */
    public function contentBlockFilter($defaultContent, $name, $type = 'line', $locale = null, $useLocaleFallback = null)
    {
        $block = $this->contentService->getOrCreateContentBlock($name, $locale, $useLocaleFallback, $type, $defaultContent);

        if (null === $locale || (null !== $this->translator && $this->translator->getLocale() == $locale))
        {
            return $this->renderContent($block);
        }

        return '';
    }

    /**
     * Render the given ContentBlock, HTML-escaping the content where necessary.
     *
     * @throws \InvalidArgumentException    If given type is unknown
     *
     * @param ContentBlock $block
     *
     * @return string
     */
    protected function renderContent(ContentBlock $block)
    {
        $content = $block->getContent();

        switch ($block->getType())
        {
            case 'line':
                $content = htmlspecialchars($content);
                break;

            case 'text':
                $content = htmlspecialchars($content);
                $content = str_replace("\n", '<br>', $content);
                break;

            case 'markdown':
                $content = $this->markdownHelper->transform($content);
                break;

            case 'html':
                break;

            default:
                throw new \InvalidArgumentException('Unknown content block type: ' . $block->getType());
        }

        return $content;
    }

    /**
     * Shortcut to contentBlockFilter using "line" rendering.
     *
     * @param string $defaultContent    Default content to assign if block has to be created
     * @param string $name              Block name
     * @param string $locale            Specific locale to use instead of current locale
     * @param string $useLocaleFallback Override global use_locale_fallback setting
     *
     * @return string
     */
    public function contentBlockFilterLine($defaultContent, $name, $locale = null, $useLocaleFallback = null)
    {
        return $this->contentBlockFilter($defaultContent, $name, 'line', $locale, $useLocaleFallback);
    }

    /**
     * Shortcut to contentBlockFilter using "text" rendering.
     *
     * @param string $defaultContent    Default content to assign if block has to be created
     * @param string $name              Block name
     * @param string $locale            Specific locale to use instead of current locale
     * @param string $useLocaleFallback Override global use_locale_fallback setting
     *
     * @return string
     */
    public function contentBlockFilterText($defaultContent, $name, $locale = null, $useLocaleFallback = null)
    {
        return $this->contentBlockFilter($defaultContent, $name, 'text', $locale, $useLocaleFallback);
    }

    /**
     * Shortcut to contentBlockFilter using "markdown" rendering.
     *
     * @param string $defaultContent    Default content to assign if block has to be created
     * @param string $name              Block name
     * @param string $locale            Specific locale to use instead of current locale
     * @param string $useLocaleFallback Override global use_locale_fallback setting
     *
     * @return string
     */
    public function contentBlockFilterMarkdown($defaultContent, $name, $locale = null, $useLocaleFallback = null)
    {
        return $this->contentBlockFilter($defaultContent, $name, 'markdown', $locale, $useLocaleFallback);
    }

    /**
     * Shortcut to contentBlockFilter using "html" rendering.
     *
     * @param string $defaultContent    Default content to assign if block has to be created
     * @param string $name              Block name
     * @param string $locale            Specific locale to use instead of current locale
     * @param string $useLocaleFallback Override global use_locale_fallback setting
     *
     * @return string
     */
    public function contentBlockFilterHtml($defaultContent, $name, $locale = null, $useLocaleFallback = null)
    {
        return $this->contentBlockFilter($defaultContent, $name, 'html', $locale, $useLocaleFallback);
    }

    public function getName()
    {
        return 'c33s_simple_content';
    }
}
