<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Orchid\Platform\Dashboard;

class RowsCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'fluffici:rows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new rows layout class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Rows';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return Dashboard::path('stubs/rows.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Orchid\Layouts';
    }
}
