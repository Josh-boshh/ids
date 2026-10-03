<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Strict',
]);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function requestBody(): array
{
    $body = json_decode((string) file_get_contents('php://input'), true);
    return is_array($body) ? $body : [];
}

function textLength(string $value): int
{
    preg_match_all('/./us', $value, $characters);
    return count($characters[0]);
}

function checkOrigin(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin === '') {
        return;
    }

    $originHost = strtolower((string) parse_url($origin, PHP_URL_HOST));
    $originPort = parse_url($origin, PHP_URL_PORT) ?: (parse_url($origin, PHP_URL_SCHEME) === 'https' ? 443 : 80);
    $requestAuthority = 'http://' . (string) ($_SERVER['HTTP_HOST'] ?? '');
    $requestHost = strtolower((string) parse_url($requestAuthority, PHP_URL_HOST));
    $requestPort = parse_url($requestAuthority, PHP_URL_PORT) ?: ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 443 : 80);
    if ($originHost !== $requestHost || $originPort !== $requestPort) {
        respond(['error' => 'Request origin is not allowed.'], 403);
    }
}

function requireAdmin(): void
{
    if (empty($_SESSION['admin_user_id'])) {
        respond(['error' => 'Please sign in to continue.'], 401);
    }
    $token = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        respond(['error' => 'Security token expired. Reload the admin and try again.'], 403);
    }
}

function startAdminSession(int $userId): string
{
    session_regenerate_id(true);
    $_SESSION['admin_user_id'] = $userId;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    unset($_SESSION['login_failures'], $_SESSION['login_locked_until']);
    return $_SESSION['csrf_token'];
}

function mapCase(array $row): array
{
    return [
        'id' => (string) $row['id'],
        'slug' => (string) $row['slug'],
        'title' => (string) $row['title'],
        'category' => (string) $row['category'],
        'summary' => (string) $row['summary'],
        'image' => (string) $row['image'],
        'imageAlt' => (string) $row['image_alt'],
        'content' => (string) $row['content'],
        'socialLinks' => [
            'website' => (string) ($row['social_website'] ?? ''),
            'linkedin' => (string) ($row['social_linkedin'] ?? ''),
            'instagram' => (string) ($row['social_instagram'] ?? ''),
            'facebook' => (string) ($row['social_facebook'] ?? ''),
            'x' => (string) ($row['social_x'] ?? ''),
        ],
        'featured' => (bool) $row['featured'],
        'published' => (bool) $row['published'],
    ];
}

function mapSiteSeo(array $row): array
{
    return [
        'siteName' => (string) $row['site_name'],
        'seoTitle' => (string) $row['seo_title'],
        'metaDescription' => (string) $row['meta_description'],
        'canonicalUrl' => (string) $row['canonical_url'],
        'ogTitle' => (string) $row['og_title'],
        'ogDescription' => (string) $row['og_description'],
        'ogImage' => (string) ($row['og_image'] ?? ''),
        'twitterTitle' => (string) $row['twitter_title'],
        'twitterDescription' => (string) $row['twitter_description'],
        'twitterImage' => (string) ($row['twitter_image'] ?? ''),
    ];
}

function currentSiteSeo(PDO $pdo): array
{
    $row = $pdo->query('SELECT * FROM site_seo_settings WHERE id = 1')->fetch();
    if (!$row) {
        respond(['error' => 'Site SEO settings are missing. Import admin/schema.sql or run migration 004.'], 503);
    }
    return mapSiteSeo($row);
}

function caseList(PDO $pdo, bool $publishedOnly): array
{
    $where = $publishedOnly ? ' WHERE published = 1' : '';
    $rows = $pdo->query('SELECT * FROM case_studies' . $where . ' ORDER BY featured DESC, sort_order ASC, updated_at DESC, id ASC')->fetchAll();
    return array_map('mapCase', $rows);
}

try {
    $pdo = database();
} catch (PDOException $error) {
    error_log('Case-study database connection failed: ' . $error->getMessage());
    respond(['error' => 'Could not connect to MySQL. Check the database settings and make sure the schema has been installed.'], 503);
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($action === 'auth-status' && $method === 'GET') {
    if (!empty($_SESSION['admin_user_id']) && empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    respond([
        'authenticated' => !empty($_SESSION['admin_user_id']),
        'csrfToken' => $_SESSION['csrf_token'] ?? null,
    ]);
}

if ($action === 'login' && $method === 'POST') {
    checkOrigin();
    $now = time();
    if ((int) ($_SESSION['login_locked_until'] ?? 0) > $now) {
        respond(['error' => 'Too many attempts. Wait 15 minutes before trying again.'], 429);
    }
    $input = requestBody();
    $password = (string) ($input['password'] ?? '');
    $statement = $pdo->query('SELECT id, password_hash FROM admin_users ORDER BY id ASC LIMIT 1');
    $user = $statement->fetch();
    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        $_SESSION['login_failures'] = (int) ($_SESSION['login_failures'] ?? 0) + 1;
        if ($_SESSION['login_failures'] >= 8) {
            $_SESSION['login_locked_until'] = $now + 900;
            $_SESSION['login_failures'] = 0;
        }
        respond(['error' => 'Password did not match.'], 401);
    }
    respond(['authenticated' => true, 'csrfToken' => startAdminSession((int) $user['id'])]);
}

if ($action === 'logout' && $method === 'POST') {
    checkOrigin();
    requireAdmin();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    respond(['authenticated' => false]);
}

if ($action === 'public-list' && $method === 'GET') {
    respond(['cases' => caseList($pdo, true)]);
}

if ($action === 'public-seo' && $method === 'GET') {
    respond(['settings' => currentSiteSeo($pdo)]);
}

if ($action === 'admin-list' && $method === 'GET') {
    requireAdmin();
    respond(['cases' => caseList($pdo, false)]);
}

if ($action === 'admin-seo' && $method === 'GET') {
    requireAdmin();
    respond(['settings' => currentSiteSeo($pdo)]);
}

if ($action === 'save-site-seo' && $method === 'POST') {
    checkOrigin();
    requireAdmin();
    $input = requestBody();
    $settings = [
        'site_name' => trim((string) ($input['siteName'] ?? '')),
        'seo_title' => trim((string) ($input['seoTitle'] ?? '')),
        'meta_description' => trim((string) ($input['metaDescription'] ?? '')),
        'canonical_url' => trim((string) ($input['canonicalUrl'] ?? '')),
        'og_title' => trim((string) ($input['ogTitle'] ?? '')),
        'og_description' => trim((string) ($input['ogDescription'] ?? '')),
        'og_image' => trim((string) ($input['ogImage'] ?? '')),
        'twitter_title' => trim((string) ($input['ogTitle'] ?? '')),
        'twitter_description' => trim((string) ($input['ogDescription'] ?? '')),
        'twitter_image' => trim((string) ($input['ogImage'] ?? '')),
    ];

    if ($settings['site_name'] === '' || $settings['seo_title'] === '' || $settings['meta_description'] === '') {
        respond(['error' => 'Complete the site name, SEO title, and meta description.'], 422);
    }
    if (textLength($settings['seo_title']) > 120 || textLength($settings['og_title']) > 120 || textLength($settings['twitter_title']) > 120) {
        respond(['error' => 'Site and social titles must be 120 characters or fewer.'], 422);
    }
    if (textLength($settings['meta_description']) > 320 || textLength($settings['og_description']) > 320 || textLength($settings['twitter_description']) > 320) {
        respond(['error' => 'Site and social descriptions must be 320 characters or fewer.'], 422);
    }
    if ($settings['canonical_url'] !== '' && filter_var($settings['canonical_url'], FILTER_VALIDATE_URL) === false) {
        respond(['error' => 'Canonical URL must be a complete URL.'], 422);
    }
    foreach (['og_image', 'twitter_image'] as $imageField) {
        $image = $settings[$imageField];
        if ($image !== '' && !preg_match('~^(https://[^\s]+|/?[a-zA-Z0-9_./-]+)$~', $image)) {
            respond(['error' => 'Social images must use an HTTPS URL or a local site path.'], 422);
        }
    }

    $statement = $pdo->prepare(
        'INSERT INTO site_seo_settings (id, site_name, seo_title, meta_description, canonical_url, og_title, og_description, og_image, twitter_title, twitter_description, twitter_image) '
        . 'VALUES (1, :site_name, :seo_title, :meta_description, :canonical_url, :og_title, :og_description, :og_image, :twitter_title, :twitter_description, :twitter_image) '
        . 'ON DUPLICATE KEY UPDATE site_name = VALUES(site_name), seo_title = VALUES(seo_title), meta_description = VALUES(meta_description), '
        . 'canonical_url = VALUES(canonical_url), og_title = VALUES(og_title), og_description = VALUES(og_description), og_image = VALUES(og_image), '
        . 'twitter_title = VALUES(twitter_title), twitter_description = VALUES(twitter_description), twitter_image = VALUES(twitter_image)'
    );
    $statement->execute($settings);
    respond(['settings' => currentSiteSeo($pdo)]);
}

if ($action === 'save' && $method === 'POST') {
    checkOrigin();
    requireAdmin();
    $input = requestBody();
    $slug = strtolower(trim((string) ($input['slug'] ?? '')));
    $title = trim((string) ($input['title'] ?? ''));
    $category = trim((string) ($input['category'] ?? ''));
    $summary = trim((string) ($input['summary'] ?? ''));
    $image = trim((string) ($input['image'] ?? ''));
    $imageAlt = trim((string) ($input['imageAlt'] ?? ''));
    $content = trim((string) ($input['content'] ?? ''));
    $socialInput = is_array($input['socialLinks'] ?? null) ? $input['socialLinks'] : null;
    $socialLinks = [
        'website' => trim((string) ($socialInput['website'] ?? '')),
        'linkedin' => trim((string) ($socialInput['linkedin'] ?? '')),
        'instagram' => trim((string) ($socialInput['instagram'] ?? '')),
        'facebook' => trim((string) ($socialInput['facebook'] ?? '')),
        'x' => trim((string) ($socialInput['x'] ?? '')),
    ];

    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || strlen($slug) > 80) {
        respond(['error' => 'Use a URL slug with lowercase letters, numbers and hyphens.'], 422);
    }
    if ($title === '' || $category === '' || $summary === '' || $image === '' || $imageAlt === '' || $content === '') {
        respond(['error' => 'Complete all required case-study fields.'], 422);
    }
    foreach ([$image] as $imageUrl) {
        if ($imageUrl !== '' && !preg_match('~^(https://[^\s]+|/?[a-zA-Z0-9_./-]+)$~', $imageUrl)) {
            respond(['error' => 'Images must use an HTTPS URL or a local site path.'], 422);
        }
    }
    foreach ($socialLinks as $url) {
        if ($url === '') {
            continue;
        }
        $parts = parse_url($url);
        if (filter_var($url, FILTER_VALIDATE_URL) === false || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            respond(['error' => 'Enter a complete HTTP or HTTPS URL for each social link.'], 422);
        }
        if (strlen($url) > 2048) {
            respond(['error' => 'Social URLs must be 2048 characters or fewer.'], 422);
        }
    }

    $id = trim((string) ($input['id'] ?? ''));
    $isUpdate = $id !== '';
    if ($isUpdate) {
        $exists = $pdo->prepare('SELECT sort_order, social_website, social_linkedin, social_instagram, social_facebook, social_x FROM case_studies WHERE id = :id');
        $exists->execute(['id' => $id]);
        $existingCase = $exists->fetch();
        if (!$existingCase) {
            respond(['error' => 'Case study not found.'], 404);
        }
        $sortOrder = $existingCase['sort_order'];
        if ($socialInput === null) {
            $socialLinks = [
                'website' => (string) ($existingCase['social_website'] ?? ''),
                'linkedin' => (string) ($existingCase['social_linkedin'] ?? ''),
                'instagram' => (string) ($existingCase['social_instagram'] ?? ''),
                'facebook' => (string) ($existingCase['social_facebook'] ?? ''),
                'x' => (string) ($existingCase['social_x'] ?? ''),
            ];
        }
    } else {
        $id = bin2hex(random_bytes(8));
        $sortOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM case_studies')->fetchColumn();
    }
    $saved = [
        'id' => $id,
        'slug' => $slug,
        'title' => $title,
        'category' => $category,
        'summary' => $summary,
        'image' => $image,
        'image_alt' => $imageAlt,
        'content' => $content,
        'social_website' => $socialLinks['website'],
        'social_linkedin' => $socialLinks['linkedin'],
        'social_instagram' => $socialLinks['instagram'],
        'social_facebook' => $socialLinks['facebook'],
        'social_x' => $socialLinks['x'],
        'featured' => !empty($input['featured']) ? 1 : 0,
        'published' => !empty($input['published']) ? 1 : 0,
        'sort_order' => (int) $sortOrder,
    ];

    try {
        if (!empty($saved['featured'])) {
            $pdo->beginTransaction();
            $clearFeatured = $pdo->prepare('UPDATE case_studies SET featured = 0 WHERE id <> :id');
            $clearFeatured->execute(['id' => $id]);
        }
        if ($isUpdate) {
            $updates = array_slice($saved, 1, null, true);
            $setClause = implode(', ', array_map(static fn ($column) => $column . ' = :' . $column, array_keys($updates)));
            $updates['id'] = $id;
            $statement = $pdo->prepare('UPDATE case_studies SET ' . $setClause . ' WHERE id = :id');
            $statement->execute($updates);
        } else {
            $columns = array_keys($saved);
            $parameters = array_map(static fn ($column) => ':' . $column, $columns);
            $statement = $pdo->prepare('INSERT INTO case_studies (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $parameters) . ')');
            $statement->execute($saved);
        }
        $lookup = $pdo->prepare('SELECT * FROM case_studies WHERE id = :id');
        $lookup->execute(['id' => $id]);
        $row = $lookup->fetch();
        if (!$row) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            respond(['error' => 'Could not reload the saved case study.'], 500);
        }
        if ($pdo->inTransaction()) $pdo->commit();
        respond(['case' => mapCase($row)]);
    } catch (PDOException $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($error->getCode() === '23000' || (int) ($error->errorInfo[1] ?? 0) === 1062) {
            respond(['error' => 'That slug is already in use.'], 409);
        }
        error_log('Could not save case study: ' . $error->getMessage());
        respond(['error' => 'Could not save the case study to MySQL.'], 500);
    }
}

if ($action === 'delete' && $method === 'POST') {
    checkOrigin();
    requireAdmin();
    $id = (string) (requestBody()['id'] ?? '');
    $statement = $pdo->prepare('DELETE FROM case_studies WHERE id = :id');
    $statement->execute(['id' => $id]);
    if ($statement->rowCount() === 0) {
        respond(['error' => 'Case study not found.'], 404);
    }
    respond(['deleted' => true]);
}

respond(['error' => 'Unknown action.'], 404);
