<?php

namespace LaravelEnso\StructureManager\tests\unit;

use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\StructureManager\app\Classes\Helpers\Writers\FormWriter;
use Tests\TestCase;

/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/3/18
 * Time: 10:35 AM.
 */
class FormGenerationTest extends TestCase
{
    private $structure;
    private $builderPath;
    private $templatePath;
    private $controllerPath;

    /** @test */
    public function testBuilderCreation()
    {
        $this->assertTrue(File::exists($this->builderPath));
    }

    /** @test */
    public function testTemplateCreation()
    {
        $this->assertTrue(File::exists($this->templatePath));
    }

    /** @test */
    public function testControllerCreation()
    {
        $this->assertTrue(File::exists($this->controllerPath));
    }

    public function setUp()
    {
        parent::setUp();

        $this->setupStructure();
        $this->setFolderPaths();
        $this->generateForm();
    }

    private function setupStructure(): void
    {
        $this->structure = new Obj((array) json_decode(File::get(__DIR__.'/../../src/app/Commands/stubs/test.stub')));

        collect($this->structure)->keys()
            ->each(function ($choice) {
                $this->structure->set($choice, new Obj((array) $this->structure->get($choice)));
            });
    }

    private function generateForm(): void
    {
        $formWriter = new FormWriter($this->structure);
        $formWriter->run();
    }

    private function setFolderPaths()
    {
        $model = $this->structure->get('model')->get('name');
        $permissionGroup = $this->structure->get('permissionGroup')->get('name');

        $segments = $this->getVariablePathSegment($permissionGroup);
        $controllerSegments = $this->getControllerNamespaceSegments($permissionGroup);

        $this->builderPath = app_path('Forms/Builders/'.implode('/', $segments).'/'.$model.'Form.php');
        $this->templatePath = app_path('Forms/Templates/'.implode('/', $segments).'/'.strtolower($model).'.json');
        $this->controllerPath = app_path('Http/Controllers/'.implode('/', $controllerSegments).'/'.$model.'Controller.php');
    }

    private function getVariablePathSegment($permissionGroup): array
    {
        $segments = collect(explode('.', $permissionGroup))
            ->map(function ($segment) {
                return ucfirst($segment);
            })
            ->toArray();

        array_pop($segments);

        return $segments;
    }

    private function getControllerNamespaceSegments($permissionGroup)
    {
        $segments = collect(explode('.', $permissionGroup))
            ->map(function ($segment) {
                return ucfirst($segment);
            })
            ->toArray();

        return $segments;
    }
}