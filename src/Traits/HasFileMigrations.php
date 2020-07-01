<?php

namespace Adaptcms\FieldFile\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;

use Adaptcms\Modules\Models\ModuleField;

use Artisan;
use DB;
use Storage;

trait HasFileMigrations
{
  /**
  * Create Column Migration
  *
  * @param ModuleField $moduleField
  *
  * @return void
  */
  public function createColumnMigration(ModuleField $moduleField)
  {
    // get module
    $module = $moduleField->module;

    // get model class
    $model = $module->customModel();

    // get model file
    $contents = Storage::disk('modules')->get($module->name . '.php');

    $find = [];
    $replace = [];

    // add implements reference if not there
    if (!strstr($contents, 'extends Model implements')) {
      $find[] = 'extends Model';
      $replace[] = 'extends Model implements \\Spatie\\MediaLibrary\\HasMedia';
    }

    // add a use call for trait if not there
    if (!strstr($contents, 'FieldFileTrait')) {
      $traitClass = '\\Adaptcms\\FieldFile\\Traits\\FieldFileTrait';

      $find[] = '/* use calls */';
      $replace[] = '/* use calls */' . $traitClass . ', ';
    }

    // save model file if changed
    if (!empty($find) && !empty($replace)) {
      $contents = str_replace($find, $replace, $contents);

      Storage::disk('modules')->put($module->name . '.php', $contents);
    }

    // get skeleton contents
    $contents = Storage::disk('packages')->get('Adaptcms/Fields/src/Skeletons/Migrations/CreateColumn.php');

    // get placeholder replacements
    $ucTable = Str::plural($module->name);
    $lcTable = Str::plural(Str::snake($module->name));
    $ucColumn = Str::studly($moduleField->column_name);
    $lcColumn = Str::snake($moduleField->column_name);

    // get attached field with migration command
    if ($moduleField->meta['mode'] === 'multiple') {
      $migrationCommand = $moduleField->field->fieldType()->multipleFilesMigrationCommand();
    } else {
      $migrationCommand = $moduleField->field->fieldType()->singleFileMigrationCommand();
    }

    $migrationCommand = str_replace(':columnName', $lcColumn, $migrationCommand);

    // replace placeholders with table & column names
    $find = [
      ':ucTable',
      ':lcTable',
      ':ucColumn',
      ':lcColumn',
      ':migrationCommand'
    ];
    $replace = [
      $ucTable,
      $lcTable,
      $ucColumn,
      $lcColumn,
      $migrationCommand
    ];

    $contents = str_replace($find, $replace, $contents);

    // create file
    $migrationName = Carbon::now()->format('Y_m_d_His') . '_create_' . $lcTable . '_column_' . $lcColumn . '_custom.php';

    Storage::disk('app')->put('database/migrations/' . $migrationName, $contents);

    // migrate
    $this->makeMigrations();
  }

  /**
  * Rename Column Migration
  *
  * @param ModuleField $moduleField
  *
  * @return void
  */
  public function renameColumnMigration(ModuleField $moduleField)
  {
    // get skeleton contents
    $contents = Storage::disk('packages')->get('Adaptcms/Fields/src/Skeletons/Migrations/RenameColumn.php');

    // get module
    $module = $moduleField->module;

    // replace placeholders with table & column names
    $oldColumnName = $moduleField->getOriginal('column_name');
    $newColumName = $moduleField->column_name;

    $ucTable = Str::plural($module->name);
    $lcTable = Str::plural(Str::snake($module->name));
    $ucColumn = Str::studly($oldColumnName);
    $oldColumn = Str::snake($oldColumnName);
    $newColumn = Str::snake($newColumName);

    $find = [
      ':ucTable',
      ':lcTable',
      ':ucColumn',
      ':oldColumn',
      ':newColumn'
    ];
    $replace = [
      $ucTable,
      $lcTable,
      $ucColumn,
      $oldColumn,
      $newColumn
    ];
    $contents = str_replace($find, $replace, $contents);

    // create file
    $migrationName = Carbon::now()->format('Y_m_d_His') . '_rename_' . $lcTable . '_column_' . $oldColumn . '_custom.php';

    Storage::disk('app')->put('database/migrations/' . $migrationName, $contents);

    // migrate
    $this->makeMigrations();
  }

  /**
  * Drop Column Migration
  *
  * ModuleField $moduleField
  *
  * @return void
  */
  public function dropColumnMigration(ModuleField $moduleField)
  {
    // get module
    $module = $moduleField->module;

    // get model class
    $model = $module->customModel();

    // get model file
    $contents = Storage::disk('modules')->get($module->name . '.php');

    $find = [];
    $replace = [];

    // remove implements reference if there
    if (strstr($contents, 'extends Model implements')) {
      $find[] = 'extends Model implements \\Spatie\\MediaLibrary\\HasMedia';
      $replace[] = 'extends Model';
    }

    // remove use call for trait if there
    if (strstr($contents, 'FieldFileTrait')) {
      $traitClass = '\\Adaptcms\\FieldFile\\Traits\\FieldFileTrait';

      $find[] = '/* use calls */' . $traitClass . ', ';
      $replace[] = '/* use calls */';
    }

    // save model file if changed
    if (!empty($find) && !empty($replace)) {
      $contents = str_replace($find, $replace, $contents);

      Storage::disk('modules')->put($module->name . '.php', $contents);
    }

    // get skeleton contents
    $contents = Storage::disk('packages')->get('Adaptcms/Fields/src/Skeletons/Migrations/DropColumn.php');

    // replace placeholders with table & column names
    $ucTable = Str::plural($module->name);
    $lcTable = Str::plural(Str::snake($module->name));
    $ucColumn = Str::studly($moduleField->column_name);
    $lcColumn = Str::snake($moduleField->column_name);

    $find = [
      ':ucTable',
      ':lcTable',
      ':ucColumn',
      ':lcColumn'
    ];
    $replace = [
      $ucTable,
      $lcTable,
      $ucColumn,
      $lcColumn
    ];
    $contents = str_replace($find, $replace, $contents);

    // create file
    $migrationName = Carbon::now()->format('Y_m_d_His') . '_drop_' . $lcTable . '_column_' . $lcColumn . '_custom.php';

    Storage::disk('app')->put('database/migrations/' . $migrationName, $contents);

    // migrate
    $this->makeMigrations();

    // remove media attached
    DB::table('media')->where('model_type', get_class($module->customModel()))->delete();
  }

  /**
  * Make Migrations
  *
  * @return void
  */
  public function makeMigrations()
  {
    Artisan::call('migrate');
  }
}
