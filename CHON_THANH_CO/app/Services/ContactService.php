<?php

namespace App\Services;

use App\Mail\NewContactMessage;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactService
{
    public function store(array $data): ContactMessage
    {
        $products = $data['products'] ?? null;

        if (is_array($products) && $products !== []) {
            $data['products'] = array_values(array_filter(array_map('trim', $products)));
            $data['product'] = implode(', ', $data['products']);
        }

        $message = ContactMessage::create($data + ['status' => 'new']);

        $this->notifyAdmin($message);

        return $message;
    }

    protected function notifyAdmin(ContactMessage $message): void
    {
        try {
            Mail::to($this->adminEmail())->send(new NewContactMessage($message));
        } catch (\Throwable $e) {
            Log::error('Không gửi được mail thông báo liên hệ', [
                'contact_message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function adminEmail(): string
    {
        return Setting::where('key', 'contact_email')->value('value')
            ?? config('mail.admin_address');
    }
}
