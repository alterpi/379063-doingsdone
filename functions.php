<?php
/**
 * Функция - шаблонизатор
 *
 * @param string $template_path путь к файлу шаблона
 * @param array $data данные
 * @return string возвращает разметку
 */
function renderTemplate (string $template_path, array $data): string {
    if(is_file($template_path)) {
        extract($data);
        ob_start();
        require_once $template_path;
        return ob_get_clean();
    } else {
        return '';
    }
};

/**
 * Возвращает количество задач по id проекта
 *
 * @param $link соединение с БД
 * @param int $project_id номер id проекта
 * @return int Количество задач
 */
function count_tasks_by_project ($link, int $project_id): int {
    $sql = 'SELECT * FROM `tasks` WHERE `project_id` = "' . $project_id . '"';
    $result = mysqli_query($link, $sql);

    if(!$result) {
        $error = mysqli_error($link);
        print('Ошибка MySQL: ' . $error);
    }

    $records_count = mysqli_num_rows($result);

    return $records_count;
};

/**
 * Возвращает общее количество задач во всех проектах пользователя
 *
 * @param $link соединение с БД
 * @param array $user_id номер id пользователя
 * @return int общее количество входящих задач пользователя
 */
function count_inbox_tasks_by_user ($link, int $user_id): int {

    $sql = 'SELECT COUNT(*) FROM `tasks` WHERE `author_id` = "' . $user_id . '" AND `project_id` IS NULL';
    $result = mysqli_query($link, $sql);

    if(!$result) {
        $error = mysqli_error($link);
        print('Ошибка MySQL: ' . $error);
    }

    $count = mysqli_fetch_row($result);

    return (int) $count[0];
};

/**
 * функция подсчета оставшего времени до наступления срока выполнения задачи
 *
 * @param string $deadline срок выполнения задачи
 * @param int $mark рубеж значимости
 * @return bool Если время, оставшееся до наступления срока задачи меньше рубежа значимости
 * функция вернёт true, если больше - false. Если срок выполнения задачи не задан - вернёт false
 */
function count_deadline (string $deadline, int $mark): bool {
    $deadline_ts = strtotime($deadline);
    if (is_int($deadline_ts)) {
        $now = time();
        $time_delta = $deadline_ts - $now;
        if ($time_delta < $mark) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
};

/**
 * Возвращает массив проектов, созданных определенным пользователем
 *
 * @param $link соединение с БД
 * @param int $user_id номер id пользователя
 * @return array Возвращает двумерный массив ассоциативных массивов с данными проектов
 */
function get_projects_by_user ($link, int $user_id): array {
    $sql = 'SELECT * FROM `projects` WHERE `user_id` = "' . $user_id . '"';
    $result = mysqli_query($link, $sql);

    if(!$result) {
        $error = mysqli_error($link);
        print('Ошибка MySQL: ' . $error);
    }

    $projects = mysqli_fetch_all($result, MYSQLI_ASSOC);

    return $projects;
}

/**
 * Возвращает массив задач в рамках определенного проекта
 *
 * @param $link соединение с БД
 * @param int $project_id номер id проекта
 * @return array Возвращает двумерный массив ассоциативных массивов с данными задач
 */
function get_tasks_by_project ($link, int $project_id): array {
    $sql = 'SELECT * FROM `tasks` WHERE `project_id` = "' . $project_id . '"';
    $result = mysqli_query($link, $sql);

    if(!$result) {
        $error = mysqli_error($link);
        print('Ошибка MySQL: ' . $error);
    }

    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);

    return $tasks;
};

/**
 * Возвращает массив задач пользователя, не отнесенных к какому-либо проекту
 *
 * @param $link соединение с БД
 * @param int $user_id номер id пользователя
 * @return array Возвращает двумерный массив ассоциативных массивов с данными задач
 */
function get_inbox_tasks_by_user ($link, int $user_id): array {
    $sql = 'SELECT * FROM `tasks` WHERE `author_id` = "' . $user_id . '" AND `project_id` IS NULL';
    $result = mysqli_query($link, $sql);

    if(!$result) {
        $error = mysqli_error($link);
        print('Ошибка MySQL: ' . $error);
    }

    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);

    return $tasks;
};

/**
 * Проверяет, есть ли у определенного пользователя соответствующий проект
 *
 * @param $link соединение с БД
 * @param int $project_id номер id проекта
 * @param int $user_id номер id пользователя
 * @return bool возвращает true, если проект найден, возвращает false, если проект не найден
 */
function check_project ($link, int $project_id, int $user_id): bool {
    $sql = 'SELECT * FROM `projects` WHERE `id` = "' . $project_id . '" AND `user_id`= "' . $user_id . '"';
    $result = mysqli_query($link, $sql);

    if(!$result) {
        $error = mysqli_error($link);
        print('Ошибка MySQL: ' . $error);
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC) ? true : false;
};

/**
 * Возвращает массив данных пользователя
 *
 * @param $link соединение с БД
 * @param int $user_id номер id пользователя
 * @return array Возвращает ассоциативный массив с данными пользователя
 */
function get_user_data_by_id ($link, int $user_id): array {
    $sql = 'SELECT * FROM `users` WHERE `id` = "' . $user_id . '"';
    $result = mysqli_query($link, $sql);

    if(!$result) {
        $error = mysqli_error($link);
        print('Ошибка MySQL: ' . $error);
    }

    $user = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $user = $user[0];

    return $user;
};

/**
 * Валидация формата даты и времени
 *
 * @param string $date Проверяемая строка, содержащая дату и время
 * @param string $format Формат, которому должна соответствовать строка
 * @return bool Возвращает true, если строка даты и времени соответствует формату, и false - если не соответствует
 */
function validate_date (string $date, string $format = 'Y-m-d H:i'): bool {
    $result = date_create_from_format($format, $date);
    return $result ? true : false;
};

/**
 * Валидация формата названия задачи
 *
 * @param string $task_name Проверяемое название
 * @param string $pattern шаблон (регулярное выражение), которому должно соответствовать название
 * @return bool Возвращает true, если название соответствует формату, и false - если не соответствует
 */
function validate_task_name (string $task_name, string $pattern = '/\S/'): bool {
    return preg_match($pattern, $task_name) === 1 ? true : false;
};

/**
 * Валидация адреса электронной почты
 *
 * @param string $email проверяемый адрес электронной почты
 * @return bool Возвращает true, если адрес верный, и false если - нет
 */
function validate_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
};

/**
 * Проверка наличия адреса электронной почты в БД
 *
 * @param $link Соединение с БД
 * @param string $email проверяемый адрес электронной почты
 * @return bool Возвращает true, если адрес не найден, и false если - найден
 */

function check_email($link, string $email): bool {
    $sql = 'SELECT * FROM `users` WHERE `email` = "' .$email.'"';
    $result = mysqli_query($link, $sql);

    if(!$result) {
        $error = mysqli_error($link);
        print('Ошибка MySQL: ' . $error);
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

    return empty($rows) ? true : false;
};
