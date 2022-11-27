<?php declare(strict_types=1);

namespace Digua;

use Digua\Traits\{Data, Output, StaticPath};
use Digua\Exceptions\{
    Path as PathException,
    Template as TemplateException
};
use Stringable;

class Template implements Stringable
{
    use Output, Data, StaticPath;

    /**
     * @var array
     */
    private array $sections = [];

    /**
     * @var array
     */
    private array $extends = [];

    /**
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
     * @var Request
     */
    private Request $request;

    /**
     * @throws PathException
     */
    public function __construct()
    {
        self::isEmptyPath();
        $this->startBuffer();
        $this->request = new Request();
    }

    /**
     * Render template.
     *
     * @throws TemplateException
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
     * Wrap template.
     *
     * @param string $name Template name
     * @return string
     * @throws TemplateException
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
     * @throws TemplateException
     */
    public function view(string $name): void
    {
        $filePath = static::$path . $name . '.tpl.php';
        if (!is_readable($filePath)) {
            throw new TemplateException($filePath . ' - template not found!');
        }

        require($filePath);
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
     * Start section.
     *
     * @param string $name Section name
     */
    public function section(string $name): void
    {
        $this->sections[$name] = $this->startBuffer();
    }

    /**
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
     * Start short section for include template.
     *
     * @param string $name Section name
     * @param string $tpl  Template name
     * @throws TemplateException
     */
    public function shortSection(string $name, string $tpl): void
    {
        $this->section($name);
        $this->view($tpl);
        $this->endSection($name);
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return empty($arguments)
            ? ($this->data[$name] ?? [])
            : $this->data[$name] = $arguments[0];
    }
}
