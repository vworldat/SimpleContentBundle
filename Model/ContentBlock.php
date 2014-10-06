<?php

namespace C33s\SimpleContentBundle\Model;

use C33s\SimpleContentBundle\Model\om\BaseContentBlock;

class ContentBlock extends BaseContentBlock
{
    /**
     * Set the value of [content] column.
     *
     * @param  string $v new value
     * @return ContentBlock The current object (for fluent API support)
     */
    public function setContent($v)
    {
        if (null !== $v)
        {
            $v = trim($v);
        }

        return parent::setContent($v);
    }
}
