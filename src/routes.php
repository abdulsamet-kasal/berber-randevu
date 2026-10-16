<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\View\View;
use App\API\AppointmentController;
use App\API\AuthController;
use App\API\AdminController;
use App\API\CatalogController;
use App\Middleware\AdminAuthMiddleware;
use App\Slot\SlotEngine;

/** @var \Slim\App<\Psr\Container\ContainerInterface> $app Defined in public/index.php before this file is required. */

// Initialize view
$view = new View();

// Initialize API controllers
$appointmentController = new AppointmentController();
$authController = new AuthController();
$adminController = new AdminController();
$catalogController = new CatalogController();

// Home page
$app->get('/', function (Request $request, Response $response) use ($view) {
    $view->set('title', 'Berber Randevu - Ana Sayfa');
    $view->set('description', 'Berber randevu sistemi');
    $html = $view->render('home.php');
    $response->getBody()->write($html);
    return $response;
});

// Services page (placeholder)
$app->get('/hizmetler', function (Request $request, Response $response) use ($view) {
    $view->set('title', 'Hizmetler - Berber Randevu');
    $view->set('description', 'Berber Randevu tarafından sunulan hizmetler');
    $html = $view->render('services.php');
    $response->getBody()->write($html);
    return $response;
});

// Team page (placeholder)
$app->get('/ekip', function (Request $request, Response $response) use ($view) {
    $view->set('title', 'Ekip - Berber Randevu');
    $view->set('description', 'Berber Randevu ekibi');
    $html = $view->render('team.php');
    $response->getBody()->write($html);
    return $response;
});

// Gallery page (placeholder)
$app->get('/galeri', function (Request $request, Response $response) use ($view) {
    $view->set('title', 'Galeri - Berber Randevu');
    $view->set('description', 'Berber Randevu salon galerie');
    $html = $view->render('gallery.php');
    $response->getBody()->write($html);
    return $response;
});

// Reviews page (placeholder)
$app->get('/yorumlar', function (Request $request, Response $response) use ($view) {
    $view->set('title', 'Yorumlar - Berber Randevu');
    $view->set('description', 'Müşteri yorumları');
    $html = $view->render('reviews.php');
    $response->getBody()->write($html);
    return $response;
});

// Contact page (placeholder)
$app->get('/iletisim', function (Request $request, Response $response) use ($view) {
    $view->set('title', 'İletişim - Berber Randevu');
    $view->set('description', 'İletişim bilgileri ve harita');
    $html = $view->render('contact.php');
    $response->getBody()->write($html);
    return $response;
});

// KVKK aydinlatma metni
$app->get('/kvkk', function (Request $request, Response $response) use ($view) {
    $view->set('title', 'KVKK Aydınlatma Metni - Berber Randevu');
    $view->set('description', 'Kişisel verilerin korunması aydınlatma metni');
    $html = $view->render('kvkk.php');
    $response->getBody()->write($html);
    return $response;
});

// Appointment booking (placeholder)
$app->get('/randevu', function (Request $request, Response $response) use ($view) {
    $view->set('title', 'Randevu Al - Berber Randevu');
    $view->set('description', 'Randevu alma sayfası');
    $html = $view->render('appointment.php');
    $response->getBody()->write($html);
    return $response;
});

// Appointment inquiry (placeholder)
$app->get('/randevu-sorgula', function (Request $request, Response $response) use ($view) {
    $view->set('title', 'Randevu Sorgula - Berber Randevu');
    $view->set('description', 'Randevu sorgulama sayfası');
    $html = $view->render('inquiry.php');
    $response->getBody()->write($html);
    return $response;
});

// Admin login (placeholder)
$app->get('/admin/giris', function (Request $request, Response $response) use ($view) {
    $view->set('title', 'Admin Girişi - Berber Randevu');
    $view->set('description', 'Yönetim paneli girişi');
    $html = $view->render('admin/login.php');
    $response->getBody()->write($html);
    return $response;
});

// API Routes
$app->group('/api', function ($app) use ($appointmentController, $authController, $catalogController) {
    // Appointment endpoints
    $app->post('/appointments', [$appointmentController, 'create']);
    $app->get('/appointments/inquiry', [$appointmentController, 'inquiry']);
    $app->post('/appointments/cancel', [$appointmentController, 'cancel']);
    $app->post('/appointments/reschedule', [$appointmentController, 'reschedule']);

    // Auth endpoints
    $app->post('/auth/login', [$authController, 'login']);
    $app->post('/auth/logout', [$authController, 'logout']);

    // Public catalog (randevu formu için)
    $app->get('/services', [$catalogController, 'services']);
    $app->get('/staff', [$catalogController, 'staff']);
});

// Admin API (JWT korumalı)
$app->group('/api/admin', function ($app) use ($adminController) {
    $app->get('/appointments', [$adminController, 'appointments']);
    $app->patch('/appointments/{id}', [$adminController, 'updateStatus']);
    $app->get('/stats', [$adminController, 'stats']);
    $app->get('/services', [$adminController, 'listServices']);
    $app->post('/services', [$adminController, 'createService']);
    $app->patch('/services/{id}', [$adminController, 'updateService']);
    $app->delete('/services/{id}', [$adminController, 'deleteService']);
    $app->get('/staff', [$adminController, 'listStaff']);
    $app->post('/staff', [$adminController, 'createStaff']);
    $app->patch('/staff/{id}', [$adminController, 'updateStaff']);
})->add(new AdminAuthMiddleware());

// Add more routes as needed

// Admin shortcut: /admin -> dashboard (server-side cookie guard + client-side token check)
$app->get('/admin', function (Request $request, Response $response) use ($view) {
    $cookies = $request->getCookieParams();
    $cookieToken = trim((string)($cookies['br_admin_token'] ?? ''));
    $header = $request->getHeaderLine('Authorization');
    if ($cookieToken === '' && $header === '') {
        return $response->withHeader('Location', '/admin/giris')->withStatus(302);
    }
    $view->set('title', 'Yönetim Paneli - Berber Randevu');
    $view->set('description', 'Berber Randevu yönetim paneli');
    $html = $view->render('admin/dashboard.php');
    $response->getBody()->write($html);
    return $response;
});

// Admin login alias: templates/admin/login.php posts to /api/admin/login
$app->post('/api/admin/login', [$authController, 'login']);

// Available slots: GET /api/available-slots?staff_id=...&date=YYYY-MM-DD&service_duration=30
$app->get('/api/available-slots', function (Request $request, Response $response) {
    $writeJson = function (Response $response, array $data, int $status = 200): Response {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($json === false ? '{}' : $json);
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    };

    $params = $request->getQueryParams();
    $staffId = trim((string)($params['staff_id'] ?? ''));
    $date = trim((string)($params['date'] ?? ''));
    $duration = (int)($params['service_duration'] ?? 0);

    if ($staffId === '') {
        return $writeJson($response, ['error' => 'staff_id is required'], 400);
    }

    $dateObj = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
    if ($dateObj === false || $dateObj->format('Y-m-d') !== $date) {
        return $writeJson($response, ['error' => 'date must be in YYYY-MM-DD format'], 400);
    }

    if ($duration < 5 || $duration > 480) {
        return $writeJson($response, ['error' => 'service_duration must be between 5 and 480 minutes'], 400);
    }

    try {
        $supabase = \App\Services\Supabase::getClient();
        $workingHours = \App\Services\Supabase::rows($supabase->from('working_hours')->select('*')->eq('staff_id', $staffId)->eq('is_active', true)->execute());
        $timeOff = \App\Services\Supabase::rows($supabase->from('time_off')->select('*')->eq('staff_id', $staffId)->execute());
        // Sadece ilgili gun: tum staff gecmisini cekme (buyuk veri = yavas yanıt).
        $dayStart = $date . ' 00:00:00';
        $dayEnd = $date . ' 23:59:59';
        $existingAppointments = \App\Services\Supabase::rows($supabase->from('appointments')->select('id, staff_id, start_time, end_time, status')->eq('staff_id', $staffId)->eq('status', 'booked')->gte('start_time', $dayStart)->lte('start_time', $dayEnd)->execute());
    } catch (\Throwable $e) {
        return $writeJson($response, ['error' => 'Slot bilgisi su anda alinamiyor, lutfen daha sonra tekrar deneyin'], 503);
    }

    try {
        $engine = new SlotEngine();
        $slots = $engine->getAvailableSlots(
            $staffId,
            $date,
            $duration,
            $workingHours,
            $timeOff,
            $existingAppointments
        );
    } catch (\Exception $e) {
        return $writeJson($response, ['error' => $e->getMessage()], 400);
    }

    $out = array_map(static function (array $slot): array {
        return ['start' => $slot['start'], 'end' => $slot['end']];
    }, $slots);

    return $writeJson($response, [
        'success' => true,
        'data' => [
            'staff_id' => $staffId,
            'date' => $date,
            'service_duration' => $duration,
            'slots' => $out,
            'count' => count($out),
        ],
    ]);
});
