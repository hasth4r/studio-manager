<?php

namespace App\Controllers;

class Telegram extends BaseController
{
    public function link()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('userId');
        $botUsername = env('TELEGRAM_BOT_USERNAME');
        
        if (empty($botUsername)) {
            return redirect()->back()->with('error', 'Telegram integration is not fully configured (missing BOT_USERNAME in .env).');
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRow();

        // Generate a link code if they don't have one
        if (empty($user->telegram_link_code)) {
            $linkCode = 'connect_' . bin2hex(random_bytes(4)) . '_' . $userId;
            $db->table('users')->where('id', $userId)->update(['telegram_link_code' => $linkCode]);
        } else {
            $linkCode = $user->telegram_link_code;
        }

        // Redirect to Telegram app
        return redirect()->to("https://t.me/" . ltrim($botUsername, '@') . "?start={$linkCode}");
    }

    public function poll()
    {
        // This is a simple endpoint to fetch updates from Telegram
        // In a real production app, you'd use a Webhook, but polling is easiest for local/testing.
        $botToken = env('TELEGRAM_BOT_TOKEN');
        if (empty($botToken)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No token']);
        }

        $db = \Config\Database::connect();
        
        // We'll store the last processed update_id in a file to avoid reprocessing
        $offsetFile = WRITEPATH . 'telegram_offset.txt';
        $offset = file_exists($offsetFile) ? (int)file_get_contents($offsetFile) : 0;

        $url = "https://api.telegram.org/bot{$botToken}/getUpdates?offset={$offset}";
        $response = @file_get_contents($url);
        
        if ($response) {
            $data = json_decode($response, true);
            if (!empty($data['result'])) {
                $maxUpdateId = $offset;
                
                foreach ($data['result'] as $update) {
                    $maxUpdateId = max($maxUpdateId, $update['update_id'] + 1);
                    
                    if (isset($update['message']['text'])) {
                        $text = $update['message']['text'];
                        $chatId = $update['message']['chat']['id'];
                        
                        // Check if it's a start command with a payload: "/start connect_abc123_5"
                        if (str_starts_with($text, '/start connect_')) {
                            $code = trim(str_replace('/start ', '', $text));
                            
                            $user = $db->table('users')->where('telegram_link_code', $code)->get()->getRow();
                            if ($user) {
                                // Link account
                                $db->table('users')->where('id', $user->id)->update([
                                    'telegram_chat_id' => $chatId,
                                    'telegram_link_code' => null // clear it
                                ]);
                                
                                // Send confirmation
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$botToken}/sendMessage");
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                                    'chat_id' => $chatId,
                                    'text' => "✅ *Success!*\nYour Ensoflow account has been linked successfully. You will now receive notifications here.",
                                    'parse_mode' => 'Markdown'
                                ]));
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_exec($ch);
                                curl_close($ch);
                            }
                        }
                    }
                }
                
                file_put_contents($offsetFile, $maxUpdateId);
                return $this->response->setJSON(['status' => 'success', 'processed' => count($data['result'])]);
            }
        }

        return $this->response->setJSON(['status' => 'success', 'processed' => 0]);
    }
}
