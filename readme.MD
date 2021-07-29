# Ресолверы аргументов для кастомных вариантов использования Symfony

**INTERNAL**

"Core" бандл. 

## Установка

composer.json:

```json
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/proklung/core.argument.resolvers.bundle"
        }
    ]
```

```bash
composer require proklung/core-argument-resolvers-bundle
```

## Конфигурационный файл

По адресу `local/packages/custom_arguments_resolvers.yaml` (или `/config/packages/custom_arguments_resolvers.yaml` для Wordpress).

Пример:

```yaml
custom_arguments_resolvers:
  defaults:
    enabled: true
  params:
    # Какие конкретно листенеры запретить (ID сервиса).
    disabled_resolvers:
      # - custom_arguments_resolvers.argument_resolver.ajax_call
      - custom_arguments_resolvers.argument_resolver.csrf_token_checker_annotation
    # Обрабатывать только контроллеры, не зарегистрированные сервисами.
    process_only_non_service_controller: false
    # Классы контроллеров, подлежащие обработке.
    classes_controllers:
      - 'Symfony\Bundle\FrameworkBundle\Controller\AbstractController'
      - 'Symfony\Bundle\FrameworkBundle\Controller\Controller'
```

## Комплект

- `custom_arguments_resolvers.argument_resolver.set_session` - установить сессию Symfony для всех запросов к контроллерам
- `custom_arguments_resolvers.argument_resolver.validator_csrf_token` - валидатор CSRF токена
- `custom_arguments_resolvers.argument_resolver.security_token_checker` - проверка токена безопасности (по трейту `SecurityTokenTrait`)
- `custom_arguments_resolvers.argument_resolver.form_url_encoded` - обработка запросов x-www-form-urlencoded
- `custom_arguments_resolvers.argument_resolver.set_container` - пропихивание контейнера в контроллер
- `custom_arguments_resolvers.argument_resolver.ajax_call` - проверка вызова на AJAX
- `custom_arguments_resolvers.argument_resolver.from_container` - ресолвинг аргументов из контейнера
- `custom_arguments_resolvers.boot_trait` - bootable traits (по аналоги [c](https://medium.com/swlh/laravel-booting-and-initializing-models-with-traits-2f77059b1915))
- `custom_arguments_resolvers.argument_resolver.user_permission_check` - проверка прав на роут

## Прочее

#### Кэширование роутов

routes.yaml:

```yaml
foo_controller:
  path: /foo/bar/
  methods: GET
  controller: 'Local\Controllers\FooController::action'
  defaults:
    _cacheble: true # Кэшировать
    _cacher: 'example.cacher' # Сервис-кэшер, реализующий интерфейс Symfony\Contracts\Cache\CacheInterface
```

Ограничения:

- кэшируются только GET запросы.

#### Ресолвинг аргументов контроллера из "делегированных" контейнеров

Делегированный контейнер - любой сервис, возвращающий `Symfony\Component\DependencyInjection\ContainerInterface`,
и содержащий в возвращаемом полноценный контейнер.

##### Зачем?

Чтобы можно было автовайрить аргументы из контейнеров модулей, плагинов и тому подобных мест.

##### Конфигурирование

```yaml
  module_notifier_container:
    class: Symfony\Component\DependencyInjection\ContainerInterface
    factory: ['Proklung\Notifier\DI\Services', 'getInstance']
    tags:
      - { name: 'delegated.container' }
```