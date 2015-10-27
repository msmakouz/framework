<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Reactor;

use Spiral\Reactor\Generators\DocumentGenerator;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generate ORM record with pre-defined schema and validation placeholders.
 */
class DocumentCommand extends \Spiral\Commands\Reactor\Prototypes\EntityCommand
{
    /**
     * Success message. To be used by DocumentCommand.
     */
    const SUCCESS_MESSAGE = 'ODM Document was successfully created:';

    /**
     * Generator class to be used.
     */
    const GENERATOR = DocumentGenerator::class;

    /**
     * Generation type to be used.
     */
    const TYPE = 'entity';

    /**
     * {@inheritdoc}
     */
    protected $name = 'create:document';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new ODM document.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Document name.']
    ];
}