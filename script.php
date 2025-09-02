<?php

//Класс ClientUrl - основной клиент для работы с API

class ClientUrl
{
    // $endpoint - URL конечной точки API для отправки запросов
    private $endpoint = "https://test.icorp.uz/private/interview.php";

    //$url (string) - URL для отправки запроса
    //$msg (array) - данные для отправки (будут преобразованы в JSON)

    private function postJson($url, $msg)
    {

        //Инициализация cURL

        $init = curl_init($url);
        $json_payload = json_encode($msg);

        //Инициализация cURL
        curl_setopt_array($init, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json_payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json_payload),
            ]
        ]);
        //Выполнение cURL запроса
        $res = curl_exec($init);
        $code = curl_getinfo($init, CURLINFO_HTTP_CODE);

        //Проверка ошибок cURL
        if ($res === false) {
            //Получение текста ошибки cURL
            $err = curl_error($init);
            curl_close($init);
            //Выброс исключения при ошибке cURL
            throw new \RuntimeException("POST curl error: " . $err);
        }

        curl_close($init);

        //Проверка HTTP кода ответа
        if ($code !== 200) {

            //Выброс исключения при неуспешном HTTP коде
            throw new \RuntimeException("POST $url -> HTTP $code: $res");
        }

        return $res;
    }

    //Объявление публичного метода run
    public function run()
    {
        //Первый запрос к API
        $response = $this->postJson($this->endpoint, [
            'uri' => "<<NGROK_DOMAIN>>/script/script.php",
            'msg' => "Hello!"
        ]);

        $arr = json_decode($response, true);

        if (!file_exists('json.txt')) {
            //Исключение при отсутствии файла
            throw new \RuntimeException("Файл json.txt не найден.");
        }

        //Чтение содержимого файла
        $content = file_get_contents('json.txt');

        //Проверка успешности чтения файла
        if ($content == false) {
            //Исключение при ошибке чтения файла
            throw new \RuntimeException("Не удалось прочитать файл json.txt");
        }

        //Декодирование JSON из файла
        $getPart = json_decode($content, true);

        //Исключение при невалидном JSON
        if ($getPart == null) {
            throw new \RuntimeException("Недопустимый JSON в файле json.txt");
        }

        //Проверка наличия необходимых ключей
        if (!isset($arr['part1']) || !isset($getPart['part2'])) {
            //Исключение при отсутствии ключей
            throw new \RuntimeException("Отсутствуют необходимые ключи: part1 или part2");
        }

        $concat = $arr['part1'] . $getPart['part2'];

        $res = $this->postJson($this->endpoint, [
            'code' => $concat
        ]);
        //Вывод результата
        print_r($res);
    }
}


//Функция callback() - обработчик входящих webhook-вызовов

function callback()
{
    $get_code = file_get_contents('php://input');

    //Проверка входящих данных
    if ($get_code == false || empty($get_code)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Входные данные не получены.']);
        return;
    }

    $output = json_decode($get_code, true);

    if ($output == null) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Получен неверный JSON']);
        return;
    }

    $json = json_encode($output);

    if (file_put_contents('json.txt', $json) === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Не удалось сохранить данные.']);
        return;
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Получен обратный вызов']);
}

//Проверка режима выполнения CLI
if (php_sapi_name() === 'cli') {
    echo "Запуск с терминала...\n";
    
    //Создание объекта и вызов метода (CLI)
    
    $client = new ClientUrl();
    $client->run();
    
    //Проверка POST запроса
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    callback();

    //Альтернативный путь выполнения
} else {
    $client = new ClientUrl();
    $client->run();
}
