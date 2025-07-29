<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Submission;

class NotificationService
{
    /**
     * Create a notification for contact submission
     */
    public function createFormSubmissionNotification(Submission $submission)
    {
        return Notification::create([
            'type' => Notification::TYPE_FORM_SUBMISSION,
            'title' => 'New Form Submission',
            'message' => "New form submission from {$submission->email}",
            'url' => route('admin.submissions.show', $submission->id),
            'data' => [
            ],
        ]);
    }


    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        return Notification::unread()->count();
    }

    /**
     * Get recent notifications
     */
    public function getRecentNotifications($limit = 10)
    {
        return Notification::latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get notifications by type
     */
    public function getNotificationsByType($type, $limit = 10)
    {
        return Notification::byType($type)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        
        return false;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        return Notification::unread()->update([
            'read_at' => now(),
        ]);
    }
} 