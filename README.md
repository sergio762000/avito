1) git clone https://github.com/sergio762000/avito.git {your_directory}
2) cd {your_directory}
3) composer install
4) database & mailer - default settings


Тестовое задание для backend-стажёра в юнит Billing

Давай напишем собственную платёжную систему, которая будет уметь отображать форму оплаты банковской картой и сохранять информацию о выполненных платежах.

    1) Пусть это будет сервис с JSON API. - принимает и отдает данные в JSON

    2) Маршруты

    Запрос на регистрацию - сделано (V).
    (У него должен быть метод /register, принимающий сумму и назначение платежа и возвращающий URL страницы с идентификатором платёжной сессии,
         например, http://somehost/payments/card/form?sessionId=4e273d86-864c-429d-879a-34579708dd69.)
    defaults: { _controller: RegisterController:register }
    path: /register
    methods: POST
    inputData:
        {
            "summa" : int,
            "назначение платежа" : string
            "уведомление" : string
        }
    returnData:
        {
            "url" : string
        }

    Вывод формы для платежа - сделано (V).
    (По URL должна открываться форма оплаты с суммой и назначением платежа.)
    defaults: { _controller: PaymentController:paymentForm }
    path: /payments/card/form?sessionId=4e273d86-864c-429d-879a-34579708dd69
    methods: GET
    inputData:
        {
            параметры запроса
        }
    returnData:
        {
            форма для заполнения реквизитов
        }

    Отправка информации о платежных данных - (V)
    (При отправке формы номер карты должен проверяться по алгоритму Луна. Валидные номера должны имитировать успешную оплату, невалидные — возвращать ошибку.)
    defaults: { _controller: PaymentController:paymentSend }
    path: /payments/send
    methods: POST
    inputData:
        {
            "summa" : int,
            "назначение платежа" : string,
            "cardNumber" : string,
            "monthExpire" : string,
            "yearExpire" : string,
            "cardHolder" : string,
            "CVV" : int
        }
    returnData:
        {
            'isSuccessStore' => boolean,
            'message' => string
        }


Мы ждём от тебя ссылку на github с реализацией на PHP (можно использовать любой фреймворк).
Что можно сделать дополнительно:

    Подготовить OpenAPI-спецификацию.
    Покрыть реализацию тестами.
    Ограничить время жизни платёжной сессии 30 минутами.
    Опубликовать решение как Docker-образ.
    Добавить API-метод, который возвращает список всех платежей за переданный период.
    После успешной оплаты асинхронно отправлять HTTP-уведомление на URL магазина. URL для таких уведомений передаваётся магазином в запросе /register.
