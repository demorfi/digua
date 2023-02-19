<?php declare(strict_types=1);

namespace Digua;

use Digua\Enums\FileExtension;
use Digua\Interfaces\Request as RequestInterface;
use Digua\Interfaces\Template as TemplateInterface;
use Digua\Traits\{Data, Output, Configurable, DiskPath};
use Digua\Exceptions\{
    Path as PathException,
    Template as TemplateException
};
use Stringable;

class Template implements TemplateInterface, Stringable
{
    use Output, Data, Configurable, DiskPath;

    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/resource/views'
    ];

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
     * @throws PathException
     */
    public function __construct(private readonly RequestInterface $request)
    {
        self::throwIsBrokenDiskPath();
        $this->startBuffer();
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
     * @param string $route Route path
     * @return bool
     */
    public function hasRoute(string $route): bool
    {
        return $this->request->getRoute()->hasRoute($route);
    }

    /**
     * @return RequestInterface
     */
    public function request(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Get active URI
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->request->getData()->query()->getUri();
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
     * @param string $name      Template name
     * @param array  $arguments Template arguments
     * @throws TemplateException
     */
    public function view(string $name, array $arguments = []): void
    {
        $filePath = self::getDiskPath(Helper::filterFileName($name) . FileExtension::TPL->value);
        if (!is_readable($filePath)) {
            throw new TemplateException($filePath . ' - template not found!');
        }

        $this->set('self', $arguments);
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
        return isset($this->sections[$name]) && !is_bool($this->sections[$name]) ? $this->sections[$name] : null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasBlock(string $name): bool
    {
        return isset($this->sections[$name]);
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
        if (empty($arguments)) {
            return $this->data[$name] ?? [];
        }

        $this->data[$name] = $arguments[0];
        return $this;
    }
}
