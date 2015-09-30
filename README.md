
В composer.json добавляем в блок require
```json
 "vis/translations": "1.0.*"
```

Выполняем
```json
composer update
```

Добавляем в app.php
```php
  'Vis\Translations\TranslationsServiceProvider',
```

Выполняем миграцию таблиц
```json
   php artisan migrate --package=vis/translations
```

Публикуем js файлы
```json
   php artisan asset:publish vis/translations
```

Публикуем config
```json
   php artisan config:publish vis/translations
```

В файле app/config/packages/vis/builder/admin.php в массив menu добавляем
```php
 	array(
            'title' => 'Переводы',
            'icon'  => 'language',
            'link'  => '/translations/phrases',
            'check' => function() {
                return true;
            }
        ),
```