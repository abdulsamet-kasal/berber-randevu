<?php

declare(strict_types=1);

namespace App\View;

class View
{
    private string $templateDir;
    /** @var array<string, mixed> */
    private array $data = [];
    private string $layout = '';
    private string $content = '';
    /** @var array<string, string> */
    private array $sections = [];
    private ?string $currentSection = null;

    public function __construct(string $templateDir = __DIR__ . '/../../templates')
    {
        $this->templateDir = rtrim($templateDir, '/\\');
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function extend(string $layout): void
    {
        $this->layout = $layout;
    }

    public function section(string $name): void
    {
        ob_start();
        $this->currentSection = $name;
    }

    public function stop(): void
    {
        if ($this->currentSection !== null) {
            $this->sections[$this->currentSection] = (string) ob_get_clean();
        }
        unset($this->currentSection);
    }

    public function render(string $template): string
    {
        // Set template data as local variables
        extract($this->data, EXTR_SKIP);

        // Obtain the template content
        $templatePath = $this->templateDir . '/' . $template;
        if (!is_file($templatePath)) {
            throw new \RuntimeException("Template not found: {$template}");
        }
        ob_start();
        require $templatePath;
        $this->content = (string) ob_get_clean();

        if ($this->layout !== '') {
            $layoutPath = $this->templateDir . '/' . $this->layout;
            if (!is_file($layoutPath)) {
                throw new \RuntimeException("Layout not found: {$this->layout}");
            }
            $content = $this->sections['content'] ?? $this->content;
            ob_start();
            require $layoutPath;
            $output = (string) ob_get_clean();
            // Reset per-render state so consecutive renders don't leak layout/sections
            $this->layout = '';
            $this->sections = [];
            return $output;
        }

        $output = $this->content;
        // Reset per-render state
        $this->layout = '';
        $this->sections = [];
        return $output;
    }
}
