<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactReply;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::latest();

        if ($request->filled('status') && in_array($request->input('status'), ['new', 'replied'], true)) {
            $query->where('status', $request->input('status'));
        }

        $contacts = $query->paginate(15)->withQueryString();

        return view('admin.contacts.index', compact('contacts'));
    }

    public function show(ContactMessage $contact)
    {
        return view('admin.contacts.show', compact('contact'));
    }

    public function markRead(ContactMessage $contact)
    {
        $contact->markHandled();

        return back()->with('success', 'Đã đánh dấu đã xử lý.');
    }

    public function reply(Request $request, ContactMessage $contact)
    {
        $data = $request->validate([
            'reply' => ['required', 'string', 'max:5000'],
        ]);

        try {
            Mail::to($contact->email)->send(new ContactReply($contact, $data['reply']));
        } catch (\Throwable $e) {
            Log::error('Không gửi được email trả lời liên hệ', [
                'contact_message_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Không gửi được email. Vui lòng kiểm tra cấu hình mail.');
        }

        if ($contact->status !== 'replied') {
            $contact->markHandled();
        }

        return redirect()->route('admin.contacts.index')->with('success', 'Đã gửi email trả lời cho ' . $contact->name . '.');
    }

    public function destroy(ContactMessage $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')->with('success', 'Đã xóa liên hệ.');
    }
}
