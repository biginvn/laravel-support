<?php

namespace Bigin\Support\Utils;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class MailVariable
{
    /**
     * layout header
     * @var string
     */
    protected $header;

    /**
     * layout footer
     * @var string
     */
    protected $footer;

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var array
     */
    protected $variableValues = [];

    /**
     * @var string
     */
    protected $module = 'core';

    public function __construct()
    {
        $this->init();
    }

    /**
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function init()
    {
        $this->initVariable();
        $this->initVariableValues();

        return $this;
    }

    /**
     * MailVariable constructor.
     */
    public function initVariable()
    {
        $this->variables['core'] = [
            'header' => __('Email template header'),
            'footer' => __('Email template footer'),
            'site_title' => __('Site title'),
            'site_url' => __('Site URL'),
            'site_logo' => __('Site Logo'),
            'date_time' => __('Current date time'),
            'date_year' => __('Current year'),
            'site_address' => __('Site Address'),
        ];
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function initVariableValues()
    {
        list($headerTemplate,
            $footerTemplate,
            $addressTemplate) = $this->loadTemplates([
            'header',
            'footer',
            'address',
        ]);

        $this->variableValues['core'] = [
            'header' => $headerTemplate,
            'footer' => $footerTemplate,
            'site_url' => url(''),
            'site_logo' => url(''),
            'date_time' => Carbon::now(config('app.timezone'))->toDateTimeString(),
            'date_year' => Carbon::now(config('app.timezone'))->format('Y'),
            'site_address' => $addressTemplate,
        ];
    }

    /**
     * Load template data
     *
     * @param array $templates
     * @return array
     */
    protected function loadTemplates(array $templates): array
    {
        return array_map(function ($template) {
            $templatePath = (config('bigin.support.support.email_templates') ?: __DIR__ . '/../../resources/email-templates/') . "{$template}.tpl";

            return File::exists($templatePath) ? get_file_data($templatePath, false) : '';
        }, $templates);
    }

    /**
     * @param $module
     * @return MailVariable
     */
    public function setModule($module): self
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @param array $variables
     * @param string $module
     * @return MailVariable
     */
    public function addVariables(array $variables): self
    {
        foreach ($variables as $name => $description) {
            $this->addVariable($name, $description);
        }

        return $this;
    }

    /**
     * @param $name
     * @param null $description
     * @param string $module
     * @return MailVariable
     */
    public function addVariable($name, $description = null): self
    {
        $this->variables[$this->module][$name] = $description;
        return $this;
    }

    /**
     * @param null $module
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getVariables($module = null): array
    {
        $this->initVariable();

        if (!$module) {
            return $this->variables;
        }

        return Arr::get($this->variables, $module, []);
    }

    /**
     * @return array
     */
    public function getVariableValues($module = null)
    {
        if ($module) {
            return Arr::get($this->variableValues, $module, []);
        }

        return $this->variableValues;
    }

    /**
     * @param array $data
     * @param string $module
     * @return MailVariable
     */
    public function setVariableValues(array $data): self
    {
        foreach ($data as $variable => $value) {
            $this->setVariableValue($variable, $value);
        }

        return $this;
    }

    /**
     * @param $variable
     * @param $value
     * @param string $module
     * @return MailVariable
     */
    public function setVariableValue($variable, $value): self
    {
        $this->variableValues[$this->module][$variable] = $value;
        return $this;
    }

    /**
     * @param $content
     * @param null $module
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function prepareData(?string $content, array $variables = []): string
    {
        if (!empty($content)) {
            $coreVariables = array_keys($this->variables['core']);

            if ($this->module !== 'core') {
                if (empty($variables)) {
                    $variables = Arr::get($this->variables, $this->module, []);
                }
            }

            $coreVariables = array_filter($coreVariables, function ($variable) use ($variables) {
                return !in_array($variable, $variables);
            });

            $content = $this->replaceVariableValue(
                array_keys($variables),
                $this->module,
                $this->replaceVariableValue($coreVariables, 'core', $content)
            );
        }

        return $content;
    }

    /**
     * @param array $variables
     * @param $module
     * @param $content
     * @return string
     */
    protected function replaceVariableValue(array $variables, $module, $content): string
    {
        foreach ($variables as $variable) {
            $keys = [
                '{{ ' . $variable . ' }}',
                '{{' . $variable . '}}',
                '{{ ' . $variable . '}}',
                '{{' . $variable . ' }}',
                '<?php echo e(' . $variable . '); ?>',
            ];

            foreach ($keys as $key) {
                $content = str_replace($key, $this->getVariableValue($variable, $module), $content);
            }
        }

        return $content;
    }

    /**
     * @param $variable
     * @param $module
     * @param string $default
     * @return string
     */
    public function getVariableValue($variable, $module, $default = ''): string
    {
        return (string)Arr::get($this->variableValues, $module . '.' . $variable, $default);
    }
}
