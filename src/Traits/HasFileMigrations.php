<?php

namespace Adaptcms\FieldFile\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;

use Adaptcms\Base\Models\PackageField;

use Artisan;
use DB;
use Storage;

trait HasFileMigrations
{
  /**
  * Create Column Migration
  *
  * @param PackageField $packageField
  *
  * @return void
  */
  public function createColumnMigration(PackageField $packageField)
  {
    // get package
    $package = $packageField->package;

    // get model class
    $model = $package->customModel();

    // get model file
    $contents = $package->getModelFileContents();

    $find = [];
    $replace = [];

    // add implements reference if not there
    if (!strstr($contents, 'extends Model implements')) {
      $find[] = 'extends Model';
      $replace[] = 'extends Model implements \\Spatie\\MediaLibrary\\HasMedia';
    }

    // add a use call for trait if not there
    if (!strstr($contents, 'FieldFileTrait') && !strstr($contents, 'FieldImageTrait') && !strstr($contents, 'InteractsWithMedia')) {
      $traitClass = '\\Adaptcms\\FieldFile\\Traits\\FieldFileTrait';

      $find[] = '/* use calls */';
      $replace[] = '/* use calls */' . $traitClass . ', ';
    }

    // save model file if changed
    if (!empty($find) && !empty($replace)) {
      $contents = str_replace($find, $replace, $contents);

      $package->putModelFileContents($contents);
    }

    // get skeleton contents
    $contents = Storage::disk('packages')->get('Adaptcms/Fields/src/Skeletons/Migrations/CreateColumn.php');

    // get placeholder replacements
    $ucTable = Str::studly(Str::plural($package->name));
    $lcTable = Str::plural(Str::snake($package->name));
    $ucColumn = Str::studly($packageField->column_name);
    $lcColumn = Str::snake($packageField->column_name);

    // get attached field with migration command
    if ($packageField->meta['mode'] === 'multiple') {
      $migrationCommand = $packageField->field->fieldType()->multipleFilesMigrationCommand();
    } else {
      $migrationCommand = $packageField->field->fieldType()->singleFileMigrationCommand();
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
  * @param PackageField $packageField
  *
  * @return void
  */
  public function renameColumnMigration(PackageField $packageField)
  {
    // get skeleton contents
    $contents = Storage::disk('packages')->get('Adaptcms/Fields/src/Skeletons/Migrations/RenameColumn.php');

    // get package
    $package = $packageField->package;

    // replace placeholders with table & column names
    $oldColumnName = $packageField->getOriginal('column_name');
    $newColumName = $packageField->column_name;

    $ucTable = Str::studly(Str::plural($package->name));
    $lcTable = Str::plural(Str::snake($package->name));
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
  * PackageField $packageField
  *
  * @return void
  */
  public function dropColumnMigration(PackageField $packageField)
  {
    // get package
    $package = $packageField->package;

    // get model class
    $model = $package->customModel();

    // get model file
    $contents = $package->getModelFileContents();

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

      $package->putModelFileContents($contents);
    }

    // get skeleton contents
    $contents = Storage::disk('packages')->get('Adaptcms/Fields/src/Skeletons/Migrations/DropColumn.php');

    // replace placeholders with table & column names
    $ucTable = Str::studly(Str::plural($package->name));
    $lcTable = Str::plural(Str::snake($package->name));
    $ucColumn = Str::studly($packageField->column_name);
    $lcColumn = Str::snake($packageField->column_name);

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
    DB::table('media')->where('model_type', get_class($package->customModel()))->delete();
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
