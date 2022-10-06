# Route
json взаимодействие с сервером
(красный – нужно сделать)

Последовательность обработки json:

1)Проверка на наличие тела, если отправлены пустые данные, то ответ 400, код ошибки 40003 – Нет входящих данных.
2)проверка на корректность json, если некорректный json – ответ 400, код ошибки 40001 Некорректный json.
3)Поиск ключей вида запроса, если ключей нет, то ответ 400, код ошибки 40002 Нет распознанных ключей запросов json.
4)Обработка запросов по ключу. Обрабатывается только первый попавшийся ключ, остальные игнорируются.
5)Поиск ключей необходимых для конкретного вида запроса, если ключей нет, то ответ 400, код ошибки 40002 Нет распознанных ключей запросов json.

Запросы за исключением регистрации/авторизации, запроса ключа сервера:
6)Если нет авторизации, то ответ 400, код ошибки 40006 – Необходима авторизация.
7)Если нет указания на bearer token, то ответ 400, код ошибки 40005 - Неверный тип авторизации.
8) Если токена нет, то ответ 400, код ошибки 40004 – Токен отсутствует
9) Если токен неверный, то ответ 401, код ошибки 40102 - Токен неверный
10) Если истек срок токен, то ответ 401, код ошибки 40101 - Истек срок токена
11)Обработка запроса, отправка данных, ответ 200, код ошибки - 0.

Запрос авторизации:
6)Если запрашиваемого пользователя нет, то ответ 401, код ошибки 40105 - Неправильный логин/пароль.
7)Генерация токенов, ответ 200, код ошибки 0.

Регистрация:
Если нет логина/пароля/имени, то ответ 400, ошибка 40002 – Нет распознанных ключей запросов json
Если пользователь уже существует, то 406, ошибка 40601 – Пользователь уже существует.
Если ошибка записи в базу, то ответ 400, ошибка СУБД.
Если регистрация прошла успешно, то ответ 201, код ошибки 0.


Токены

Токены имеют формат JWT, протокол RS256, система с открытым/закрытым RSA ключом.
Структура токена:
access_level – int (1-пользователь, 2 – администратор)
user_id - int
token_type - access/refresh
exp – время истечения токена
Рефреш токен живет неделю, ацесс – 50 минут (для тестов, потом будет минуту).

Рефреш-токен при создании хранится в таблице пользователей, чтобы был только 1 рабочий рефреш токен. При запросе на обновление токенов идет проверка на актуальность.
Ацесс токены не валидируются из-за малого срока жизни.

Запросы

Получить список ближайших точек

Запрос:
json_query: list_nav_point – название запроса
position_x – float (широта) - координаты места
position_y – float (долгота) - координаты места
radius – float (в метрах) – радиус
access_token

Ответ: 
ID (точки)
X (широта)
Y (долгота)
Tag (метка точки)
navpoint_route
error
error_code

Регистрация пользователя

Запрос:
json_query: registration – название запроса
login – логин пользователя
password – пароль
name - имя

Ответ:
error
error_code



Авторизация пользователя (отправка логина и пароля для получения токенов доступа)

Запрос:
json_query: autorization – название запроса
login
password

Ответ:
access_token
refresh_token
error
error_code

Продление авторизации (отправка рефреш-токена)

Запрос:
json_query: authorization_reneval – название запроса
refresh_token

Ответ:
access_token,
refresh_token
error
error_code

Запрос на предоставление публичного ключа сервера

Запрос:
json_query: public_key – название запроса

Ответ:
server_key

Смена имени/логина/пароля

Запрос:
json_query: registration_change – название запроса
login – логин пользователя
password – пароль
login_new – логин пользователя
password_new – пароль
name_new – имя

Ответ:
error
error_code

Удаление пользователя

Запрос:
json_query: user_delete – название запроса
login – логин пользователя
password – пароль

Ответ:
error
error_code

Ошибки

Поля ошибок:
Error – описание ошибки
error_code – код ошибки

Коды ошибок:

0 – Нет ошибок
20401 – Нет исходящих данных
40001 – Некорректный json
40002 - Нет распознанных ключей запросов json
40003 – Нет входящих данных
40004 – Токен отсутствует
40005 – Неверный тип авторизации
40006 – Необходима авторизация
40101 - Истек срок токена
40102 - Токен неверный
40103 - Токен утратил силу
40105 - Неправильный логин/пароль
40301 – Недостаточно прав
40601 – Пользователь уже существует
50001 - Проблемы с получением времени, возможно некорректное указание временной зоны в настройках сервера
50002 - Проблемы с временем жизни токенов, возможно некорректное указание времени жизни токена в настройках сервера

