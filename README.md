# getTask

### Данный скрипт позволяет запускать контроль по сделкам менеджера. Контроль заключается в постоянном напоминании менеджеру о сделке, если сотрудник забыл о ней.

Данный скрипт является частью большой логики, которая тесно взаимосвязана с бизнес-процессами Битрикс24. Если кратко, процессы должны либо запускаться, либо останавливаться, в зависимости от действий менеджера над сделкой. Процессы сами по себе представляют напоминание менеджеру, если он перестает активно работать со сделкой.

Схематично с бизнес-процессом можно познакомиться во вложении.

**Механизм работы при создании задачи**:

1. Создается задача.
2. Запускается проверка, что данная задача была создана вручную менеджером. Условие проверки: тег в задаче != "Автоматика".  
3. Если такая задача была поставлена, то необходимо остановить бизнес-процесс Битрикс24, который регулярно уведомляет менеджера, так как, менеджер самостоятельно запланировал себе задачу, соответственно не забывает про сделку.
4. Если задача имеет тег "Автоматика", тогда цепочку уведомлений необходимо продолжить через Бизнес-процесс.

**Механизм работы при изменении задачи**:

1. Происходит измнение задачи.
2. Запускается проверка, что данная задача была создана вручную менеджером. Условие проверки: тег в задаче != "Автоматика" и задача была закрыта.  
3. Если такое условие случилось и при этом менеджер вручную не ставит себе новую задачу, запускается бизнес-процесс, который снова начинает уведомлять менеджера.
4. Если задача имеет тег "Автоматика" и задача закрыта, тогда цепочку уведомлений продолжается через Бизнес-процесс.

Решение может работать как на облачных, так и коробочных Битрикс24. 

**Как запустить**:
1. getTask.php и auth.php необходимо разместить на хостинге с поддержкой SSL.
2. В разделе "Разработчикам" необходимо создать исхоядщий вебхук с событиями Создание задачи (ONTASKADD) и Обновление задачи (ONTASKUPDATE). В URL вашего обработчика необходимо прописать адрес и путь на хостинге, где размещен getTask.php (пункт 1). Также, необходимо создать входящий вебхук с правами на CRM (crm), Задачи (task) и Бизнес-процессы (bizproc). Подробнее как создать входящий / исходящий вебхук: [Ссылки на документацию 1С-Битрикс](https://github.com/thnik911/getTask/blob/main/README.md#%D1%81%D1%81%D1%8B%D0%BB%D0%BA%D0%B8-%D0%BD%D0%B0-%D0%B4%D0%BE%D0%BA%D1%83%D0%BC%D0%B5%D0%BD%D1%82%D0%B0%D1%86%D0%B8%D1%8E-1%D1%81-%D0%B1%D0%B8%D1%82%D1%80%D0%B8%D0%BA%D1%81)
3. Полученный "Вебхук для вызова rest api" прописать в auth.php.
4. Необходимо создать отдельный бизнес-процесс, который будет создавать элемент сущности "Процессы". Более подробно о действии "Создать элемент списка": [Ссылки на документацию 1С-Битрикс](https://github.com/thnik911/getTask/blob/main/README.md#%D1%81%D1%81%D1%8B%D0%BB%D0%BA%D0%B8-%D0%BD%D0%B0-%D0%B4%D0%BE%D0%BA%D1%83%D0%BC%D0%B5%D0%BD%D1%82%D0%B0%D1%86%D0%B8%D1%8E-1%D1%81-%D0%B1%D0%B8%D1%82%D1%80%D0%B8%D0%BA%D1%81) 
5. При создании элемента списка необходимо в качестве инфоблока выбрать: "Процессы" и предварительно создать бизнес-процесс в ленту. Подробнее о процессах в ленте: [Ссылки на документацию 1С-Битрикс](https://github.com/thnik911/getTask/blob/main/README.md#%D1%81%D1%81%D1%8B%D0%BB%D0%BA%D0%B8-%D0%BD%D0%B0-%D0%B4%D0%BE%D0%BA%D1%83%D0%BC%D0%B5%D0%BD%D1%82%D0%B0%D1%86%D0%B8%D1%8E-1%D1%81-%D0%B1%D0%B8%D1%82%D1%80%D0%B8%D0%BA%D1%81)
6. Результатом отработки бизнес-процесса является созданный элемент в разделе "Процессы". ID данного элемента необходимо хранить в карточке сделки в поле 'UF_CRM_1647844191'. Можно подставить свой код поля в строках 40 и 90 getTask.php. На основании созданного элемента необходимо запустить отдельный бизнес-процесс в сущности "Процессы".
7. В строке 99 скрипта getTask.php в 'TEMPLATE_ID' необходимо указать ID бизнес-процесса, который необходимо запустить, если менеджер закрыл созданную задачу и не поставил новую задачу.
8. Создаем задачу без тега "Автоматика" в сделке. На основании созданной задачи бизнес-процесс в элементе "Процессы" должен остановиться. 
9. Закрываем задачу без тега "Автоматика" в сделке. На основании завершенной задачи бизнес-процесс в элементе "Процессы" должен запуститься повторно.

### Ссылки на документацию 1С-Битрикс 

<details><summary>Развернуть список</summary>

1. Действие Webhook внутри Бизнес-процесса / робота https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=57&LESSON_ID=8551
2. Как создать Webhook https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=99&LESSON_ID=8581&LESSON_PATH=8771.8583.8581
3. Действие "Создать элемент списка" https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=48&LESSON_ID=7122&LESSON_PATH=3918.4635.4744.5035.5036.7122
4. Подробнее о процессах в ленту https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=57&LESSON_ID=4516&LESSON_PATH=5442.5446.4516

</details>

![Снимок экрана 2022-04-03 163909](https://user-images.githubusercontent.com/59867180/161426142-6745ac43-e0c1-4718-a523-3ec8582941bc.png)
