<?php

namespace Adaptcms\FieldFile\Field;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Adaptcms\Fields\FieldType;
use Adaptcms\FieldFile\Traits\HasFileMigrations;
use Adaptcms\Modules\Models\PackageField;

class FieldFile extends FieldType
{
  use HasFileMigrations;

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
  * Format Name
  *
  * @param PackageField $packageField
  *
  * @return string
  */
  public function formatName(PackageField $packageField)
  {
    $name = Str::singular($packageField->name);
    if ($packageField->meta['mode'] === 'multiple') {
      $name = Str::plural($packageField->name);
    }

    return $name;
  }

  /**
  * Format Column Name
  *
  * @param PackageField $packageField
  *
  * @return string
  */
  public function formatColumnName(PackageField $packageField)
  {
    $name = Str::singular($packageField->name);
    if ($packageField->meta['mode'] === 'multiple') {
      $name = Str::plural($packageField->name);
    }

    return strtolower($name);
  }

  /**
  * With Form Meta
  *
  * @param Request      $request
  * @param PackageField $packageField
  *
  * @return array
  */
  public function withFormMeta(Request $request, PackageField $packageField)
  {
    $meta = [];

    // set media info to view
    $columnName = $packageField->column_name;

    $customModel = $packageField->package->customModel();

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
  * After Model Store
  *
  * @param Model        $model
  * @param Request      $request
  * @param PackageField $packageField
  *
  * @return void
  */
  public function afterModelStore($model, Request $request, PackageField $packageField)
  {
    $this->afterModelSave($model, $request, $packageField);
  }

  /**
  * After Model Update
  *
  * @param Model        $model
  * @param Request      $request
  * @param PackageField $packageField
  *
  * @return void
  */
  public function afterModelUpdate($model, Request $request, PackageField $packageField)
  {
    $this->afterModelSave($model, $request, $packageField);
  }

  /**
  * After Model Save
  *
  * @param Model        $model
  * @param Request      $request
  * @param PackageField $packageField
  *
  * @return void
  */
  public function afterModelSave($model, Request $request, PackageField $packageField)
  {
    // init vars
    $columnName = $packageField->column_name;
    $isMultiple = ($packageField->meta['mode'] === 'multiple');

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
