<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Notification $notificationModel): void
    {
        $user = Session::get('user');
        $notifications = $notificationModel->forUser((int) $user['id']);
        $this->view('notifications/index', ['notifications' => $notifications]);
    }

    public function markAllRead(Notification $notificationModel): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            $this->redirect('/notifications');
        }

        $user = Session::get('user');
        $notificationModel->markAllAsRead((int) $user['id']);
        Session::set('unread_notifications', 0);
        $this->redirect('/notifications');
    }
}
