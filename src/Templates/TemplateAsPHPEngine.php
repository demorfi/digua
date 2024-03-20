<?php declare(strict_types=1);

namespace Digua\Templates;

use Digua\Helper;
use Digua\Enums\FileExtension;
use Digua\Interfaces\{
    Request as RequestInterface,
    Template\Engine as TemplateEngineInterface
};
use Digua\Traits\{Data, Buffering};
use Digua\Exceptions\{
    Path as PathException,
    Template as TemplateException
};

class TemplateAsPHPEngine implements TemplateEngineInterface
{
    use Buffering, Data;

    /**
     * @var array
     */
    private array $sections = [];

    /**
     * @var array
     */
    private array $extends = [];

    /**
     * @param string           $path
     * @param RequestInterface $request
     */
    public function __construct(private readonly string $path, private readonly RequestInterface $request)
    {
    }

    /**
     * @param string $template
     * @param array  $variables
     * @return string
     * @throws PathException
     * @throws TemplateException
     */
    public function build(string $template, array $variables = []): string
    {
        $build = clone $this;

        $build->overwrite($variables);
        $build->startBuffering();
        $build->view($template);

        for ($i = 0; $i < sizeof($build->extends); $i++) {
            $build->view($build->extends[$i]);
        }

        return $build->flushBuffering();
    }

    /**
     * @param string $route Route path
     * @return bool
     */
    protected function hasRoute(string $route): bool
    {
        return $this->request->getRoute()->hasRoute($route);
    }

    /**
     * @return string
     */
    protected function getUri(): string
    {
        return $this->request->getData()->query()->getUri();
    }

    /**
     * @param string $template Template name
     */
    public function extend(string $template): void
    {
        $this->extends[] = $template;
    }

    /**
     * @param string $section Section name
     * @return ?string
     */
    protected function block(string $section): ?string
    {
        return isset($this->sections[$section]) && !is_bool($this->sections[$section]) ? $this->sections[$section] : null;
    }

    /**
     * @param string $section Section name
     * @return bool
     */
    protected function hasBlock(string $section): bool
    {
        return isset($this->sections[$section]);
    }

    /**
     * @param string $name
     */
    protected function section(string $name): void
    {
        $this->sections[$name] = $this->startBuffering();
    }

    /**
     * @param string $name
     */
    protected function endSection(string $name): void
    {
        if (isset($this->sections[$name]) && $this->sections[$name] === true) {
            $this->sections[$name] = $this->flushBuffering();
        }
    }

    /**
     * @param string $template
     * @param array  $variables
     * @return void
     * @throws PathException
     * @throws TemplateException
     */
    protected function view(string $template, array $variables = []): void
    {
        $filePath = $this->path . '/' . Helper::filterFilePath($template) . FileExtension::TPL->value;
        if (!is_readable($filePath)) {
            throw new TemplateException(sprintf('Template (%s) not found!', $filePath));
        }

        $this->set('self', $variables);
        require($filePath);
    }

    /**
     * Wrap template.
     *
     * @param string $template Template name
     * @return string
     * @throws PathException
     * @throws TemplateException
     */
    protected function inject(string $template): string
    {
        $this->view($template);

        $this->section($template);
        for ($i = 0; $i < sizeof($this->extends); $i++) {
            $this->view($this->extends[$i]);
        }
        $this->endSection($template);

        $this->extends = [];
        return $this->block($template);
    }

    /**
     * @param string $section  Section name
     * @param string $template Template name
     * @throws PathException
     * @throws TemplateException
     */
    protected function shortSection(string $section, string $template): void
    {
        $this->section($section);
        $this->view($template);
        $this->endSection($section);
    }
}