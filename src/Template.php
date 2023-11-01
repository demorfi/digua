<?php declare(strict_types=1);

namespace Digua;

use Digua\Templates\TemplateAsPHPEngine;
use Digua\Interfaces\{
    Request as RequestInterface,
    Template as TemplateInterface,
    Template\Engine as TemplateEngineInterface
};
use Digua\Traits\DiskPath;
use Digua\Exceptions\Path as PathException;
use Stringable;

class Template implements TemplateInterface, Stringable
{
    use DiskPath;

    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/resource/views'
    ];

    /**
     * @var string
     */
    private string $name;

    /**
     * @var array
     */
    private array $variables = [];

    /**
     * @param RequestInterface         $request
     * @param ?TemplateEngineInterface $engine
     * @throws PathException
     */
    public function __construct(private readonly RequestInterface $request, private ?TemplateEngineInterface $engine = null)
    {
        self::throwIsBrokenDiskPath();
        $this->engine ??= new TemplateAsPHPEngine(self::getDiskPath(), $this->request);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->engine->build($this->name, $this->variables);
    }

    /**
     * @param array $variables
     * @return void
     */
    public function addVariables(array $variables): void
    {
        $this->variables = [...$this->variables, ...$variables];
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return TemplateEngineInterface
     */
    public function getEngine(): TemplateEngineInterface
    {
        return $this->engine;
    }

    /**
     * @inheritdoc
     */
    public function render(string $name, array $variables = []): self
    {
        $this->name      = $name;
        $this->variables = $variables;
        return $this;
    }
}