<?php

namespace Adwiv\Laravel\CrudGenerator;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CrudGenerator extends Command
{
    use ClassHelper;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'crud:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create complete CRUD code';

    public function handle()
    {
        $model = trim($this->argument('model'));
        $modelClass = $this->fullModelClass($model);
        $modelBaseName = class_basename($modelClass);

        if (!($table = $this->option('table'))) {
            if (class_exists($modelClass)) {
                $table = (new $modelClass())->getTable();
            } else {
                $table = Str::snake(Str::plural($model));
            }
        }

        echo "Creating CRUD for '$modelClass' using '$table' table\n";

        // Generate Model
        if (!class_exists($modelClass) || $this->confirm("$modelClass already exists. Do you want to overwrite it?", false)) {
            $this->call('crud:model', ['name' => $modelClass, '--table' => $table, '--force' => true]);
        }

        // Generate Request
        $requestClass = $this->fullRequestClass("{$modelBaseName}Request");
        if (!class_exists($requestClass) || $this->confirm("$requestClass already exists. Do you want to overwrite it?", false)) {
            $this->call('crud:request', ['name' => $requestClass, '--model' => $modelClass, '--force' => true]);
        }

        // Generate Resource
        $resourceClass = $this->fullResourceClass("{$modelBaseName}Resource");
        if (!class_exists($resourceClass) || $this->confirm("$resourceClass already exists. Do you want to overwrite it?", false)) {
            $this->call('crud:resource', ['name' => $resourceClass, '--model' => $modelClass, '--force' => true]);
        }

        // Generate Controller
        $controllerClass = $this->fullControllerClass("{$modelBaseName}Controller");
        if (!class_exists($controllerClass) || $this->confirm("$controllerClass already exists. Do you want to overwrite it?", false)) {
            $this->call('crud:controller', ['name' => $controllerClass, '--model' => $modelClass, '--force' => true]);
        }

        $viewPrefix = $this->option('view-prefix') ?? $this->option('prefix') ?? '';
        $routePrefix = $this->option('route-prefix') ?? $this->option('prefix') ?? '';

        // Generate Index View
        $viewName = strtolower($modelBaseName);
        $viewPath = $this->fullViewPath($viewName, $viewPrefix, 'index');
        if (!file_exists($viewPath) || $this->confirm("$viewName index view already exists. Do you want to overwrite it?", false)) {
            $this->call('crud:view-index', ['name' => $viewName, '--model' => $modelClass, '--force' => true, '--view-prefix' => $viewPrefix, '--route-prefix' => $routePrefix]);
        }

        // Generate Edit View
        $viewName = strtolower($modelBaseName);
        $viewPath = $this->fullViewPath($viewName, $viewPrefix, 'edit');
        if (!file_exists($viewPath) || $this->confirm("$viewName edit view already exists. Do you want to overwrite it?", false)) {
            $this->call('crud:view-edit', ['name' => $viewName, '--model' => $modelClass, '--force' => true, '--view-prefix' => $viewPrefix, '--route-prefix' => $routePrefix]);
        }

        // Generate Show View
        $viewName = strtolower($modelBaseName);
        $viewPath = $this->fullViewPath($viewName, $viewPrefix, 'show');
        if (!file_exists($viewPath) || $this->confirm("$viewName show view already exists. Do you want to overwrite it?", false)) {
            $this->call('crud:view-show', ['name' => $viewName, '--model' => $modelClass, '--force' => true, '--view-prefix' => $viewPrefix, '--route-prefix' => $routePrefix]);
        }
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['model', InputArgument::REQUIRED, 'Specify the model class.'],
            ['prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for views and routes.'],
            ['view-prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for the views used.'],
            ['route-prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for the routes used.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['table', null, InputOption::VALUE_REQUIRED, 'Use specified table name instead of guessing.'],
        ];
    }
}
