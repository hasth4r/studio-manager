<?php

if (!function_exists('send_notification')) {
    /**
     * Send a notification to a specific user.
     * 
     * @param int $userId Target user ID
     * @param string $type Notification type (e.g. 'task_assigned', 'review_approved')
     * @param string $title Short title
     * @param string $message Detailed message
     * @param string|null $link Optional URL to redirect to on click
     */
    function send_notification($userId, $type, $title, $message, $link = null, $imageUrl = null)
    {
        if (empty($userId)) return false;

        $db = \Config\Database::connect();
        
        // Auto-create table if it doesn't exist
        if (!$db->tableExists('notifications')) {
            $db->query("
                CREATE TABLE IF NOT EXISTS notifications (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    link VARCHAR(255) NULL,
                    is_read BOOLEAN NOT NULL DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
                );
            ");
        }

        $inserted = $db->table('notifications')->insert([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'link'       => $link,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($inserted) {
            // Check for Telegram Integration
            $botToken = env('TELEGRAM_BOT_TOKEN');
            if (!empty($botToken)) {
                $user = $db->table('users')->where('id', $userId)->get()->getRow();
                if ($user && !empty($user->telegram_chat_id)) {
                    $tgMessage = "🔔 *{$title}*\n\n{$message}";
                    
                    $fullLink = '';
                    if (!empty($link)) {
                        $fullLink = str_starts_with($link, 'http') ? $link : rtrim(env('app.baseURL'), '/') . '/' . ltrim($link, '/');
                        $tgMessage .= "\n\n🔗 [View Details]({$fullLink})";
                    }

                    $ch = curl_init();
                    if (!empty($imageUrl)) {
                        $fullImageUrl = str_starts_with($imageUrl, 'http') ? $imageUrl : rtrim(env('app.baseURL'), '/') . '/' . ltrim($imageUrl, '/');
                        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$botToken}/sendPhoto");
                        curl_setopt($ch, CURLOPT_POSTFIELDS, [
                            'chat_id'    => $user->telegram_chat_id,
                            'photo'      => $fullImageUrl,
                            'caption'    => $tgMessage,
                            'parse_mode' => 'Markdown'
                        ]);
                    } else {
                        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$botToken}/sendMessage");
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                            'chat_id'    => $user->telegram_chat_id,
                            'text'       => $tgMessage,
                            'parse_mode' => 'Markdown'
                        ]));
                    }
                    
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_exec($ch);
                    curl_close($ch);
                }
            }
        }

        return $inserted;
    }
}
