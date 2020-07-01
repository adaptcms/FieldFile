<?php

namespace Adaptcms\FieldFile\Field;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Adaptcms\Fields\FieldType;
use Adaptcms\FieldFile\Traits\HasFileMigrations;
use Adaptcms\Modules\Models\ModuleField;

class FieldFile extends FieldType
{
  use HasFileMigrations;

  /**
  * Rules applied when record is being stored with a post type.
  *
  * @var array
  */
  public $storeRules = [
    //
  ];

  /**
  * Rules applied when record is being updated with a post type.
  *
  * @var array
  */
  public $updateRules = [
    //
  ];

  /**
  * @var boolean
  */
  public $shouldNotSetData = true;

  /**
  * Single File Migration Command
  *
  * @return string
  */
  public function singleFileMigrationCommand()
  {
    return '$table->string(":columnName")->nullable();';
  }

  /**
  * Multiple Files Migration Command
  *
  * @return string
  */
  public function multipleFilesMigrationCommand()
  {
    return '$table->json(":columnName")->nullable();';
  }

  /**
  * Get Value
  *
  * @param mixed $value
  *
  * @return mixed
  */
  public function getValue($value)
  {
    return $value;
  }

  /**
  * Set Value
  *
  * @param mixed $value
  *
  * @return void
  */
  public function setValue($value)
  {
    return $value;
  }

  /**
  * Format Name
  *
  * @param ModuleField $moduleField
  *
  * @return string
  */
  public function formatName(ModuleField $moduleField)
  {
    $name = Str::singular($moduleField->name);
    if ($moduleField->meta['mode'] === 'multiple') {
      $name = Str::plural($moduleField->name);
    }

    return $name;
  }

  /**
  * Format Column Name
  *
  * @param ModuleField $moduleField
  *
  * @return string
  */
  public function formatColumnName(ModuleField $moduleField)
  {
    $name = Str::singular($moduleField->name);
    if ($moduleField->meta['mode'] === 'multiple') {
      $name = Str::plural($moduleField->name);
    }

    return strtolower($name);
  }

  /**
  * After Store
  *
  * @param ModuleField $moduleField
  *
  * @return void
  */
  // public function afterStore(ModuleField $moduleField)
  // {
  //
  // }

  /**
  * With Form Meta
  *
  * @param Request     $request
  * @param ModuleField $moduleField
  *
  * @return array
  */
  public function withFormMeta(Request $request, ModuleField $moduleField)
  {
    $meta = [];

    // set media info to view
    $columnName = $moduleField->column_name;

    $customModel = $moduleField->module->customModel();

    $routeParams = $request->route()->parameters();

    if (!empty($routeParams['itemId'])) {
      $model = $customModel->find($routeParams['itemId']);

      if (!empty($model)) {
        $meta = $model->getMedia($columnName);
      }
    }

    return $meta;
  }

  /**
  * With Loaded Relationships
  *
  * @param Model $model
  * @param ModuleField $moduleField
  *
  * @return Model
  */
  // public function withLoadedRelationships($model, ModuleField $moduleField)
  // {
  //   return $model;
  // }

  /**
  * After Model Store
  *
  * @param Model       $model
  * @param Request     $request
  * @param ModuleField $moduleField
  *
  * @return void
  */
  public function afterModelStore($model, Request $request, ModuleField $moduleField)
  {
    $this->afterModelSave($model, $request, $moduleField);
  }

  /**
  * After Model Update
  *
  * @param Model       $model
  * @param Request     $request
  * @param ModuleField $moduleField
  *
  * @return void
  */
  public function afterModelUpdate($model, Request $request, ModuleField $moduleField)
  {
    $this->afterModelSave($model, $request, $moduleField);
  }

  /**
  * After Model Save
  *
  * @param Model       $model
  * @param Request     $request
  * @param ModuleField $moduleField
  *
  * @return void
  */
  public function afterModelSave($model, Request $request, ModuleField $moduleField)
  {
    // init vars
    $columnName = $moduleField->column_name;
    $isMultiple = ($moduleField->meta['mode'] === 'multiple');

    if ($isMultiple) {
      $values = is_null($model->$columnName) ? [] : $model->$columnName;
    } else {
      $values = is_null($model->$columnName) ? [] : [ $model->$columnName ];
    }

    // delete existing file(s) if there are any
    $media = $model->getMedia($columnName);
    $removeFiles = $request->removeFiles;

    if ($media->count() && !empty($removeFiles)) {
      $mediaToDelete = $media->whereIn('id', $removeFiles);

      // unset column value if no media no longer present
      if ($mediaToDelete->count() === $media->count()) {
        $model->$columnName = null;

        $model->save();
      }

      foreach ($mediaToDelete as $item) {
        $findKey = array_search($item->getFullUrl(), $values);

        if ($findKey !== false) {
          unset($values[$findKey]);
        }

        $item->delete();
      }
    }

    // ensure files have been uploaded
    $fileData = $request->$columnName;

    if (!empty($fileData)) {
      // handle uploading the file contents
      $handleFile = function ($file) use ($model, $columnName) {
        $filename = $file->getClientOriginalName();

        // save file to media collection
        $uploadedFile = $model
          ->addMedia($file->path())
          ->usingName($filename)
          ->usingFileName($filename)
          ->toMediaCollection($columnName);

        return $uploadedFile->getFullUrl();
      };

      // set column data to either a json array of file urls
      // or a single file url string
      foreach ($fileData as $file) {
        $values[] = $handleFile($file);
      }

      if (!empty($values)) {
        if ($isMultiple) {
          $model->$columnName = $values;
        } else {
          $model->$columnName = $values[0];
        }

        // save file data
        $model->save();
      }
    }
  }
}
