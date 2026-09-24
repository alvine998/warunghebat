<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $subject = $request->string('subject')->toString();

        if (! in_array($subject, ContactMessage::SUBJECTS, true)) {
            $subject = null;
        }

        $search = trim((string) $request->input('search', ''));

        $messages = ContactMessage::query()
            ->when($subject, fn ($query) => $query->where('subject', $subject))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => ContactMessage::count(),
        ];

        foreach (ContactMessage::SUBJECTS as $value) {
            $counts[$value] = ContactMessage::where('subject', $value)->count();
        }

        return view('admin.inquiries', [
            'messages' => $messages,
            'counts' => $counts,
            'subject' => $subject,
        ]);
    }
}
