<?php

namespace Corbital\LaravelEmails\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Corbital\LaravelEmails\Services\CustomEmailService to($to)
 * @method static \Corbital\LaravelEmails\Services\CustomEmailService cc($cc)
 * @method static \Corbital\LaravelEmails\Services\CustomEmailService bcc($bcc)
 * @method static \Corbital\LaravelEmails\Services\CustomEmailService replyTo(string $replyTo)
 * @method static \Corbital\LaravelEmails\Services\CustomEmailService from(string $email, ?string $name = null)
 * @method static \Corbital\LaravelEmails\Services\CustomEmailService subject(string $subject)
 * @method static \Corbital\LaravelEmails\Services\CustomEmailService content(string $content, array $data = [])
 * @method static \Corbital\LaravelEmails\Services\CustomEmailService template(string $template, array $data = [])
 * @method static \Corbital\LaravelEmails\Services\CustomEmailService attach(string $path, array $options = [])
 * @method static \Corbital\LaravelEmails\Services\CustomEmailService attachments(array $attachments)
 * @method static bool send()
 */
class Email extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'email';
    }
}
