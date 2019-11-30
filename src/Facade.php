<?php

namespace Xelt2011\Attachment;

/**
 * @method static \Xelt2011\Attachment\AttachmentInterface make($file, array $options)
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return AttachmentManager::class;
    }
}
