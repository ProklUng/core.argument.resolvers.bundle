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
