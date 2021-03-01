# FieldFile

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require adaptcms/fieldfile
```

## Usage

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email charliepage88@gmail.com instead of using the issue tracker.

## Credits

- [Charles Page][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/adaptcms/fieldfile.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/adaptcms/fieldfile.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/adaptcms/fieldfile/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/adaptcms/fieldfile
[link-downloads]: https://packagist.org/packages/adaptcms/fieldfile
[link-travis]: https://travis-ci.org/adaptcms/fieldfile
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/adaptcms
[link-contributors]: ../../contributors

## AdaptCMS - UI

#### Include layout in Vue page view

```import Layout from '@/Adaptcms/Base/ui/layouts/AdminLayout'``

or

```import Layout from '@/Adaptcms/Site/ui/layouts/layout'```

and

```layout: (h, page) => h(Layout, [ page ]),```
