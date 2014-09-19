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
            new \Twig_SimpleFilter('c33s_content_block', array($this, 'contentBlockFilter'), array('is_safe' => array('html'))),
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
     * @param string    $defaultContent
     * @param string    $name
     * @param string    $type, defaults to 'line'
     * @param string    $locale, defaults to null
     *
     * @return string
     */
    public function contentBlockFilter($defaultContent, $name, $type = 'line', $locale = null)
    {
        if (null === $locale && null !== $this->translator)
        {
            $fetchLocale = $this->translator->getLocale();
        }
        else
        {
            $fetchLocale = $locale;
        }

        $block = $this->contentService->fetchContentBlock($name, $type, $fetchLocale, $defaultContent);

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

    public function getName()
    {
        return 'c33s_simple_content';
    }
}
