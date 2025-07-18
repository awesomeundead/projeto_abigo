<?php

header('Content-Type: application/json; charset=utf-8');

require ROOT_DIR . '/../../session.php';

$logged_in = $_SESSION['logged_in'] ?? false;

if(!$logged_in)
{
    http_response_code(401);
    echo 'UNAUTHORIZED';
    exit;
}

require ROOT_DIR . '/pdo.php';

$content = trim(file_get_contents('php://input'));
$dados = json_decode($content, true);

$user_id = $_SESSION['user_id'];
$id = $vars['id'];
$title_br = trim($dados['title_br']);
$title_us = trim($dados['title_us']);
$director = trim($dados['director']);
$release_year = trim($dados['release_year']);
$imdb = trim($dados['imdb']);

$params = [
    'id' => $id,
    'title_br' => $title_br,
    'release_year' => $release_year
];

$query = 'SELECT id FROM movies WHERE id != :id AND title_br = :title_br AND release_year = :release_year';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = !$stmt->fetchColumn();

if ($result)
{
    if (preg_match('#^https://www.imdb.com/[\w-]+/title/(tt\d+)/#', $imdb, $matches))
    {
        $imdb = $matches[1];
    }

    $params = [
        'id' => $id,
        'title_br' => $title_br,
        'title_us' => $title_us,
        'director' => $director,
        'release_year' => $release_year,
        'imdb' => $imdb,
        'last_user_id' => $user_id
    ];

    $query = 'UPDATE movies SET
            title_br = :title_br,
            title_us = :title_us,
            director = :director,
            release_year = :release_year,
            imdb = :imdb,
            last_user_id = :last_user_id
            WHERE id = :id';

    $stmt = $pdo->prepare($query);
    $result = $stmt->execute($params);
}

$json['status'] = $result ? 'success' : 'failure';

echo json_encode($json);