<?php

namespace Cws\EloquentModelGenerator\Processor;

use Cws\CodeGenerator\Model\ClassNameModel;
use Cws\CodeGenerator\Model\DocBlockModel;
use Cws\CodeGenerator\Model\MethodModel;
use Cws\CodeGenerator\Model\NamespaceModel;
use Cws\CodeGenerator\Model\UseClassModel;
use Cws\EloquentModelGenerator\Config;
use Cws\EloquentModelGenerator\Model\EloquentModel;
use Illuminate\Support\Str;
use Cws\EloquentModelGenerator\Misc;

/**
 * Class NamespaceProcessor
 * @package Cws\EloquentModelGenerator\Processor
 */
class RepositoryProcessor implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function process(EloquentModel $model, Config $config)
    {
        if (!Misc::endsWith($model->getTableName(), "_translations"))
            $this->createRepositoryForModelIfNeeded($config, $model);
        return $this;
    }

    /**
     * If repository key in config is not false creates the repository for current model
     *
     * @param Config $config
     * @param EloquentModel $model
     */
    private function createRepositoryForModelIfNeeded(Config $config, EloquentModel $model)
    {
        if ($config->get("repository") !== false) {
            $this->checkIfBaseFilesAlreadyExistsOtherwiseCreate($config, $model);
            $repoResourceFolder = __DIR__ . '/../Resources/Repositories';
            $modelName = $model->getName()->getName();
            $config->checkIfFileAlreadyExistsOrCopyIt(
                $model,
                Misc::appPath('Repositories/' . $modelName),
                $modelName . "Contract.php",
                $repoResourceFolder,
                "ModelContract.php"
            );
            $config->checkIfFileAlreadyExistsOrCopyIt(
                $model,
                Misc::appPath('Repositories/' . $modelName),
                "Eloquent" . $modelName . "Repository.php",
                $repoResourceFolder,
                "EloquentModelRepository.php"
            );
            $contractPath = $config->getAppNamespace() . "Repositories\\$modelName\\" . $modelName . "Contract";
            $repoPath = $config->getAppNamespace() . "Repositories\\$modelName\\Eloquent" . $modelName . "Repository";
            $this->bindOnProvider($contractPath, $repoPath);
        }
    }

    private function bindOnProvider($contractName, $repoName)
    {
        $file = base_path('app/Providers/RepositoryServiceProvider.php');
        $line = "\\{$contractName}::class => \\{$repoName}::class";
        $this->addArrayLineToFileIfMissing($file, 'bindings', $line);
    }

    private function addArrayLineIfMissing(string $key, string $line, string $content): string
    {
        $checkRegex = '/' . str_replace(' ', '\s+', str_replace('\\', '\\\\', $line)) . '/';
        if (preg_match($checkRegex, $content) !== 1) {
            // TODO: add to array with sorting?
            $replaceRegex = "/([ \t]*)([a-zA-Z ]*[\$|'|\"]{$key}['|\"]? =>? \[)(.*?)?\s*,?\s*]/s";
            $content = preg_replace_callback($replaceRegex, function ($matches) use ($line) {
                $replace = "\n{$matches[1]}    {$line}";
                if (trim($matches[3])) {
                    $replace = $matches[3] . ',' . $replace;
                }
                return $matches[1] . $matches[2] . $replace . "\n" . $matches[1] . ']';
            }, $content);
        }

        return $content;
    }

    private function addArrayLineToFileIfMissing(string $filePath, string $key, string $line)
    {
        $content = file_get_contents($filePath);
        $content = $this->addArrayLineIfMissing($key, $line, $content);
        file_put_contents($filePath, $content);
    }

    private function checkIfBaseFilesAlreadyExistsOtherwiseCreate(Config $config, EloquentModel $model)
    {
        $repoResourceFolder = __DIR__ . '/../Resources/Repositories';
        $config->checkIfFileAlreadyExistsOrCopyIt(
            $model,
            Misc::appPath('Exceptions'),
            "GenericException.php",
            $repoResourceFolder,
            "GenericException.php"
        );
        $config->checkIfFileAlreadyExistsOrCopyIt(
            $model,
            Misc::appPath('Repositories'),
            "RepositoryContract.php",
            $repoResourceFolder,
            "RepositoryContract.php"
        );
        $config->checkIfFileAlreadyExistsOrCopyIt(
            $model,
            Misc::appPath('Repositories'),
            "EloquentRepository.php",
            $repoResourceFolder,
            "EloquentRepository.php"
        );
        $config->checkIfFileAlreadyExistsOrCopyIt(
            $model,
            Misc::appPath('Providers'),
            "RepositoryServiceProvider.php",
            $repoResourceFolder,
            "RepositoryServiceProvider.php"
        );
        $this->registerServiceProvider();
    }

    private function registerServiceProvider()
    {
        $file = base_path('config/app.php');
        $line = 'App\Providers\RepositoryServiceProvider::class';
        $this->addArrayLineToFileIfMissing($file, 'providers', $line);
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return 2;
    }
}
