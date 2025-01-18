# AWOOC Product Options

Дополнение к плагину Art WooCommerce Order One Click (3.0.0). 

## Описание

Поддержка плагинов дополнительных опций товаров:
* [Advanced Product Fields for WooCommerce](https://ru.wordpress.org/plugins/advanced-product-fields-for-woocommerce/),
* [Simple Product Options for WooCommerce](https://ru.wordpress.org/plugins/product-options-for-woocommerce/),
* [Extra product options For WooCommerce](https://ru.wordpress.org/plugins/woo-extra-product-options/),
* [Art WooCommerce Product Options](https://github.com/artikus11/art-woocommerce-product-options),
* [YITH WooCommerce Product Add-ons & Extra Options](https://ru.wordpress.org/plugins/yith-woocommerce-product-add-ons/)

**Внимание!** Плагин тестировался только с бесплатными версиями плагинов. Передаются все необходимые данные (название, значение, цена). Если поддерживаемый плаин не поддерживает вывод цены опции, то она выводитья не будет.

Для корректной работы требуется в настройках плагина Art WooCommerce Order One Click включить вывод Опций. Дальше плагин будет автоматически определять данные из поддерживаемого плагина и ввыводить во всплывающем окне, письме, заказе.

*Примечание:* Нельзя использовать все плагины из списка поддерживаемых одновременно, при активации одного из них, другие будут отключены.

# Как добавлять поддежку
1. В файле `src/PluginsManager.php` в константу `SUPPORT_PLUGINS` добавить путь к основному файлу плагина в виде `plugin-directory/plugin-file.php`
2. В папке `src/Handles` создать новый класс, например `NewPluginOptionsProduct`
3. В методе `init_classes` основного класса `Main` через проверку подключить объект класса
4. Во вновь созданном классе написать нужнй функционал

# [Changelog](https://github.com/artikus11/awooc-product-options/blob/master/CHANGELOG.md)

## [Unreleased]

## [1.1.0] - 2025-01-15

### Added

- Добавлено: поддержка плагина Art WooCommerce Product Options

### Changed

- Изменено: рефакторинг под новый формат обработки данных
- Изменено: вынос кода по классам согласно функционала
- Изменено: рефакторинг обработки для плагина Simple Product Options for WooCommerce
- Изменено: рефакторинг обработки для плагина Extra product options For WooCommerce
- Изменено: рефакторинг обработки для плагина Advanced Product Fields for WooCommerce
- Изменено: приведение к WPCS

### Fixed

- Исправлено:
