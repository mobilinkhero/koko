<?php

namespace Corbital\LaravelEmails\Services;

use Corbital\LaravelEmails\Exceptions\EmailException;
use Corbital\LaravelEmails\Models\EmailTemplate;
use Illuminate\Support\Arr;

class TemplateRenderer
{
    /**
     * Default variables that are available in all templates.
     *
     * @var array
     */
    protected $defaultVariables = [];

    /**
     * Create a new template renderer instance.
     */
    public function __construct()
    {
        $this->defaultVariables = config('laravel-emails.default_variables', [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
        ]);
    }

    /**
     * Render a template with the given data.
     *
     * @param  EmailTemplate|string  $template  The template model or content
     * @param  array  $data  Data for the template variables
     *
     * @throws EmailException
     */
    public function render($template, array $data = []): string
    {
        // Merge default variables with provided data
        $mergedData = array_merge($this->defaultVariables, $data);

        if ($template instanceof EmailTemplate) {
            return $this->renderTemplateModel($template, $mergedData);
        } elseif (is_string($template)) {
            return $this->renderContent($template, $mergedData);
        }

        throw new EmailException('Invalid template provided.');
    }

    /**
     * Render the template model with the given data.
     */
    protected function renderTemplateModel(EmailTemplate $template, array $data): string
    {
        if (empty($template->content)) {
            return '';
        }

        return $this->renderContent($template->content, $data);
    }

    /**
     * Render the subject with the given data.
     *
     * @param  EmailTemplate|string  $template
     *
     * @throws EmailException
     */
    public function renderSubject($template, array $data = []): string
    {
        // Merge default variables with provided data
        $mergedData = array_merge($this->defaultVariables, $data);

        if ($template instanceof EmailTemplate) {
            if (empty($template->subject)) {
                return '';
            }

            return $this->renderContent($template->subject, $mergedData);
        } elseif (is_string($template)) {
            return $this->renderContent($template, $mergedData);
        }

        throw new EmailException('Invalid template provided.');
    }

    /**
     * Render a content string with the given data.
     */
    protected function renderContent(string $content, array $data): string
    {
        // Replace variables in the format {{variable_name}}
        return preg_replace_callback('/\{\{(.*?)\}\}/', function ($matches) use ($data) {
            $key = trim($matches[1]);

            return Arr::get($data, $key, '');
        }, $content);
    }

    /**
     * Get a list of variables used in the template.
     */
    public function extractVariables(string $content): array
    {
        preg_match_all('/\{\{(.*?)\}\}/', $content, $matches);

        $variables = [];
        if (isset($matches[1]) && ! empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $variables[] = trim($match);
            }
        }

        return array_unique($variables);
    }
}
