
1. в composer.json добавляем в блок require
```json
 "vis/translations": "1.0.*"
```

2. выполняем
```json
composer update
```

3. добавляем в app.php
```php
  'Vis\Translations\TranslationsServiceProvider',
```

4. Выполняем миграцию таблиц
```json
   php artisan migrate --package=vis/translations
```

5. Публикуем js файлы
```json
   php artisan asset:publish vis/translations
```

5. в файле app/config/packages/vis/builder/admin.php в массив menu добавляем
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