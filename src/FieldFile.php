<?php

namespace Adaptcms\FieldFile;

use Adaptcms\Base\Models\Package;

class FieldFile
{
  /**
  * On Install
  *
  * @return void
  */
  public function onInstall()
  {
    Package::syncPackageFolder(get_class());
  }
}
