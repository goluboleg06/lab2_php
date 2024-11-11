<?php
session_start(); // Запускаємо сесію для зберігання підприємств

// Шлях до файлу для збереження підприємств
define('ENTERPRISES_FILE', 'enterprises.json');

// Функція для збереження підприємств у файл
function saveEnterprisesToFile($enterprises) {
    file_put_contents(ENTERPRISES_FILE, json_encode($enterprises));
}

// Функція для завантаження підприємств з файлу
function loadEnterprisesFromFile() {
    if (file_exists(ENTERPRISES_FILE)) {
        $data = file_get_contents(ENTERPRISES_FILE);
        return json_decode($data, true); // Повертаємо асоціативний масив
    }
    return [];
}

// Завантажуємо підприємства з файлу, якщо файл існує
if (!isset($_SESSION['enterprises'])) {
    $_SESSION['enterprises'] = loadEnterprisesFromFile();
}

// Літерал нумерованого масиву
if (!isset($_SESSION['enterprises'])) {
    $_SESSION['enterprises'] = [
        [
            'code' => '1',
            'title' => 'АТ НАК "Нафтогаз України"',
            'employees' => 50,
            'industry' => 'імпорт та продаж природного газу',
            'address' => 'вул. Богдана Хмельницького'
        ],
        [
            'code' => '2',
            'title' => 'ТОВ "АТБ-Маркет"',
            'employees' => 150,
            'industry' => 'Продаж продуктів',
            'address' => 'вул. Капушанська'
        ],
        [
            'code' => '3',
            'title' => 'ТОВ "Епіцентр К"',
            'employees' => 30,
            'industry' => 'Продаж будівельних матеріалів',
            'address' => 'вул. Цвітна'
        ],
    ];
}

// Функція для відображення підприємств
function displayEnterprises($enterprises) {
    echo '<table border="1">';
    echo '<thead>';
    echo '<tr><th>Код</th><th>Назва</th><th>Робочі</th><th>Спеціальність</th><th>Адреса</th><th>Дії</th></tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($enterprises as $index => $enterprise) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($enterprise['code']) . '</td>';
        echo '<td>' . htmlspecialchars($enterprise['title']) . '</td>';
        echo '<td>' . htmlspecialchars($enterprise['employees']) . '</td>';
        echo '<td>' . htmlspecialchars($enterprise['industry']) . '</td>';
        echo '<td>' . htmlspecialchars($enterprise['address']) . '</td>';
        echo '<td><a href="?edit=' . $index . '">Оновити</a></td>'; 
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}

// Функція для фільтрації підприємств
function filterEnterprises($enterprises, $industry, $minEmployees, $maxEmployees) {
    return array_filter($enterprises, function($enterprise) use ($industry, $minEmployees, $maxEmployees) {
        return $enterprise['industry'] === $industry &&
               $enterprise['employees'] >= $minEmployees &&
               $enterprise['employees'] <= $maxEmployees;
    });
}

// Обробка запиту на фільтрацію
$enterprises = $_SESSION['enterprises'];

if (isset($_GET['industry']) && isset($_GET['min_employees']) && isset($_GET['max_employees'])) {
    $industry = $_GET['industry'];
    $minEmployees = (int)$_GET['min_employees'];
    $maxEmployees = (int)$_GET['max_employees'];

    $filteredEnterprises = filterEnterprises($enterprises, $industry, $minEmployees, $maxEmployees);
    displayEnterprises($filteredEnterprises);
} else {
    displayEnterprises($enterprises);
}

// Обробка форми для додавання нового підприємства
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_index'])) {
    $code = $_POST['code'];
    $title = $_POST['title'];
    $employees = (int)$_POST['employees'];
    $industry = $_POST['industry'];
    $address = $_POST['address'];

    // Валідація даних
    if (empty($code) || empty($title) || $employees < 0 || empty($industry) || empty($address)) {
        echo "Всі поля обов'язкові для заповнення, а кількість співробітників повинна бути невід'ємною.";
    } else {
        // Оновлення підприємства
        $_SESSION['enterprises'][$editIndex] = [
            'code' => $code,
            'title' => $title,
            'employees' => $employees,
            'industry' => $industry,
            'address' => $address
        ];
        saveEnterprisesToFile($_SESSION['enterprises']); // Зберігаємо зміни у файл
        echo "Підприємство оновлено успішно";
        header("Location: index.php"); // Перенаправлення після оновлення
        exit;
    }
}
// Обробка запиту на редагування
if (isset($_GET['edit'])) {
    $editIndex = (int)$_GET['edit'];
    $editEnterprise = $_SESSION['enterprises'][$editIndex];
}

// Обробка форми для редагування підприємства
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_index'])) {
    $editIndex = (int)$_POST['edit_index'];
    $code = $_POST['code'];
    $title = $_POST['title'];
    $employees = (int)$_POST['employees'];
    $industry = $_POST['industry'];
    $address = $_POST['address'];

    // Валідація даних
    if (empty($code) || empty($title) || $employees < 0 || empty($industry) || empty($address)) {
        echo "Всі поля обов'язкові для заповнення, а кількість співробітників повинна бути невід'ємною.";
    } else {
        // Оновлення підприємства
        $_SESSION['enterprises'][$editIndex] = [
            'code' => $code,
            'title' => $title,
            'employees' => $employees,
            'industry' => $industry,
            'address' => $address
        ];
        echo "Підприємство оновлено успішно";
        header("Location: index.php"); // Перенаправлення після оновлення
        exit;
    }
}
?>

<form method="post">
    <label for="code">Код</label>
    <input name="code" value="<?php echo isset($editEnterprise) ? htmlspecialchars($editEnterprise['code']) : ''; ?>"/><br/>
    <label for="title">Назва</label>
    <input name="title" value="<?php echo isset($editEnterprise) ? htmlspecialchars($editEnterprise['title']) : ''; ?>"/><br/>
    <label for="employees">Робочі</label>
    <input type="number" name="employees" min="0" value="<?php echo isset($editEnterprise) ? htmlspecialchars($editEnterprise['employees']) : ''; ?>"/><br/>
    <label for="industry">Спеціальність</label>
    <input name="industry" value="<?php echo isset($editEnterprise) ? htmlspecialchars($editEnterprise['industry']) : ''; ?>"/><br/>
    <label for="address">Адреса</label>
    <input name="address" value="<?php echo isset($editEnterprise) ? htmlspecialchars($editEnterprise['address']) : ''; ?>"/><br/>
    <?php if (isset($editEnterprise)): ?>
        <input type="hidden" name="edit_index" value="<?php echo htmlspecialchars($editIndex); ?>"/>
        <button type="submit">Оновити</button><br/>
    <?php else: ?>
        <button type="submit">Додати</button><br/>
    <?php endif; ?>
    
</form>

