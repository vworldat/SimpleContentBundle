<?php

namespace C33s\SimpleContentBundle\Controller\ContentBlock;

use Admingenerated\C33sSimpleContentBundle\BaseContentBlockController\NewController as BaseNewController;

/**
 * NewController
 */
class NewController extends BaseNewController
{
    protected $existingBlock;

    protected function getNewObject()
    {
        $block = parent::getNewObject();

        if (null !== $this->getRequest()->get('copy_name') && null !== $this->getRequest()->get('copy_locale') && null !== $this->getRequest()->get('new_locale'))
        {
            $existingBlock = $this->get('c33s_simple_content')->getContentBlock($this->getRequest()->get('copy_name'), $this->getRequest()->get('copy_locale'));
            if (null === $existingBlock)
            {
                throw $this->createNotFoundException('Invalid block data');
            }

            $block
                ->setName($existingBlock->getName())
                ->setContent($existingBlock->getContent())
                ->setType($existingBlock->getType())
                ->setLocale($this->getRequest()->get('new_locale'))
            ;

            $this->existingBlock = $existingBlock;
        }
        else
        {
            throw $this->createNotFoundException('Cannot add blocks directly');
        }

        return $block;
    }

    /**
     * Get additional parameters for rendering.
     *
     * @param \C33s\SimpleContentBundle\Model\ContentBlock $ContentBlock your \C33s\SimpleContentBundle\Model\ContentBlock object
     * return array
     */
    protected function getAdditionalRenderParameters(\C33s\SimpleContentBundle\Model\ContentBlock $ContentBlock)
    {
        return array(
            'existingBlock' => $this->existingBlock,
        );
    }
}
