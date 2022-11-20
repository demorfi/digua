<?php

namespace Digua;

use Digua\Abstracts\Data;
use Digua\Traits\Output;
use Digua\Exceptions\Path as PathException;
use Stringable;

class Template extends Data implements Stringable
{
    use Output;

    /**
     * Path to views files.
     *
     * @var string
     */
    public static string $path = '';

    /**
     * Sections.
     *
     * @var array
     */
    private array $sections = [];

    /**
     * Extends.
     *
     * @var array
     */
    private array $extends = [];

    /**
     * Data template.
     *
     * @var array
     */
    private array $data = [];

    /**
     * Template name.
     *
     * @var string
     */
    private string $tpl = '';

    /**
     * Request instance.
     *
     * @var Request
     */
    private Request $request;

    /**
     * Template constructor.
     *
     * @throws PathException
     */
    public function __construct()
    {
        if (empty(static::$path)) {
            throw new PathException('the path to the template is not configured');
        }

        $this->startBuffer();
        $this->request = new Request();
    }

    /**
     * Render template.
     */
    public function __toString(): string
    {
        $this->startBuffer();
        $this->view($this->tpl);

        for ($i = 0; $i < sizeof($this->extends); $i++) {
            $this->view($this->extends[$i]);
        }

        $content = $this->flushBuffer();
        $this->cleanBuffer();
        return $content;
    }

    /**
     * Set path to views files.
     *
     * @param string $path
     */
    public static function setPath(string $path): void
    {
        static::$path = $path;
    }

    /**
     * Wrap template.
     *
     * @param string $name Template name
     * @return string
     */
    public function inject(string $name): string
    {
        $this->view($name);

        $this->section($name);
        for ($i = 0; $i < sizeof($this->extends); $i++) {
            $this->view($this->extends[$i]);
        }
        $this->endSection($name);

        $this->extends = [];
        return $this->block($name);
    }

    /**
     * Has active route.
     *
     * @param string $path Route path
     * @return bool
     */
    public function hasRoute(string $path): bool
    {
        return $this->request->getQuery()->hasRoute($path);
    }

    /**
     * Get active URI
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->request->getQuery()->getUri();
    }

    /**
     * Add template for render.
     *
     * @param string $name      Template name
     * @param array  $variables Added template variables
     * @return Template
     */
    public function render(string $name, array $variables = []): self
    {
        $this->tpl   = $name;
        $this->array = $variables;
        return $this;
    }

    /**
     * Include template.
     *
     * @param string $name Template name
     */
    public function view(string $name): void
    {
        require(static::$path . $name . '.php');
    }

    /**
     * Add template extend.
     *
     * @param string $name Template name
     */
    public function extend(string $name): void
    {
        $this->extends[] = $name;
    }

    /**
     * Fetch section.
     *
     * @param string $name Section name
     * @return string|null
     */
    public function block(string $name): string|null
    {
        return $this->sections[$name] ?? null;
    }

    /**
     * Init section.
     *
     * @param string $name Section name
     */
    public function section(string $name): void
    {
        $this->sections[$name] = $this->startBuffer();
    }

    /**
     * Close init section.
     *
     * @param string $name Section name
     */
    public function endSection(string $name): void
    {
        if (isset($this->sections[$name]) && $this->sections[$name] === true) {
            $this->sections[$name] = $this->flushBuffer();
            $this->cleanBuffer();
        }
    }

    /**
     * Init short section for include template.
     *
     * @param string $name Section name
     * @param string $tpl  Template name
     */
    public function shortSection(string $name, string $tpl): void
    {
        $this->section($name);
        $this->view($tpl);
        $this->endSection($name);
    }

    /**
     * Proxy.
     *
     * @param string $name      Method name
     * @param array  $arguments Method arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return empty($arguments)
            ? ($this->data[$name] ?? [])
            : $this->data[$name] = $arguments[0];
    }
}
