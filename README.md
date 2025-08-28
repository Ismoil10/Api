# Api
○	Пример вывода: 
Первый вывод:
{"part1": "ARuOdq0eofZzYdvvDWkdw3bx2M+jLib/8abO/sTIMrJsM+pD7fvbcu0WLKKtFCgNwDz7eiEGERJ0FVIX/1q5NfJPcBuExyN8A7BF2KDJpi9vEIPvXGeYQAuhvm6AwAF3LzLnmR70kQB+nqfq4r6Zgz5tV46peWx6hZkvaWyd0HEq"}
Второй вывод:
{"part2":"4C4PUE0jCjSAfcT5BQ8YX+sB4cD98pVPwUomwPttCgZ6/v7uT3v0ispnlHSL6ZQ2OouFQm7Up5YL/dBt6ywxl3Z8RMYNv8CqKhA5J8LJDBEq/5M7p7Ua6ugKRmVki9ic4Jz9Yy6KLdcwbCVIxgQmmFcuuP/RHBn3ccxfYqUqKg=="}
Последний вывод — отправленное "msg" :
Hello!



README — Работа с тестовым API (PHP, cURL)

Документация к скрипту index.php (один файл), который реализует простой сценарий взаимодействия с тестовым API:

Класс ClientUrl отправляет POST-запрос на тестовый endpoint и получает part1.

Скрипт ожидает, что внешний callback (webhook) пришлёт part2 в теле POST — этот обработчик сохраняёт данные в json.txt.

Затем ClientUrl соединяет part1 + part2 и отправляет итог в поле code на тот же endpoint.

Содержание репозитория

index.php — основной файл, содержит:

класс ClientUrl (методы postJson() и run());

функцию callback() — handler для входящих webhook POST;

логика определения режима работы (CLI / POST / web).

json.txt — файл, куда сохраняется тело callback (создаётся автоматически при получении POST).

Требования

PHP 7.2+ (рекомендуется 7.4 или 8.x)

PHP-расширение curl включено (ext-curl)

Права на запись в директорию, где лежит index.php (для json.txt)

Доступ в сеть для обращения к https://test.icorp.uz/private/interview.php

/project
  ├─ index.php        <-- ваш объединённый скрипт (client + callback)
  ├─ json.txt         <-- создаётся автоматически при callback (или вручную)
  └─ README.md




Подготовка и запуск — шаг за шагом
1) Запустите локальный PHP-сервер (вариант)

Если вы используете встроенный сервер PHP:

cd /path/to/project
php -S 127.0.0.1:8000


Если проект доступен через XAMPP/Apache — разместите index.php в документ-руте (например, htdocs/interview/index.php).

2) Запустите ngrok, чтобы получить публичный URL

Если сервер слушает порт 8000:

ngrok http 8000


После запуска ngrok вы получите публичный HTTPS URL, например:

https://abcd1234.ngrok-free.app

3) Сформируйте callback URL и вставьте в скрипт

В index.php найдите место, где задаётся uri (пример в коде):

'uri' => "https://f38fb25de8cc.ngrok-free.app/script/script.php",


Замените на ваш public ngrok URL + путь к файлу, например:

https://abcd1234.ngrok-free.app/index.php


Важно: URL должен указывать на тот же файл, который принимает POST (т. е. index.php), если вы используете объединённый файл.

4) Запустите client-часть

Есть два варианта:

CLI:

php index.php


Через браузер (если скрипт настроен на это): откройте https://127.0.0.1:8000/index.php?run=1 или просто ?run=1, если в коде предусмотрен параметр.

При запуске клиент отправит первый POST на https://test.icorp.uz/private/interview.php, передав ваше uri. Сервер в ответ должен вернуть JSON с part1 и (в идеале) сам вызовет ваш callback (POST на ваш ngrok URL) с part2.

5) Убедитесь, что callback сработал

После входящего webhook’а в папке проекта должен появиться (или обновиться) файл json.txt.

Проверьте содержимое json.txt. Оно должно быть JSON с хотя бы part2, например:

{ "part2": "..." }


Если json.txt отсутствует — проверьте ngrok лог/console, серверные логи и доступность публичного URL.

6) Скрипт завершает работу

Если part2 найден в json.txt, скрипт объединяет part1 + part2 и делает финальный POST {"code": "<concatenated>"}

Результат финального запроса выводится в консоль или в ответ браузеру.
