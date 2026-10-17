<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role'     => 'required|in:admin,employee',
            'invoice_template' => 'nullable|string|in:taotheeq,sindi',
        ], [
            'name.required'      => 'الاسم مطلوب.',
            'email.required'     => 'البريد الإلكتروني مطلوب.',
            'email.unique'       => 'هذا البريد مسجل مسبقاً.',
            'password.required'  => 'كلمة المرور مطلوبة.',
            'password.confirmed' => 'كلمة المرور غير متطابقة.',
            'password.min'       => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'role.required'      => 'يرجى اختيار الصلاحية.',
        ]);

        $user = User::create([
            'name'             => $validated['name'],
            'email'            => $validated['email'],
            'password'         => Hash::make($validated['password']),
            'invoice_template' => $validated['invoice_template'] ?? 'taotheeq',
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'تم إنشاء حساب المستخدم بنجاح.');
    }

    public function toggleTemplate(User $user)
    {
        $newTemplate = ($user->invoice_template === 'sindi') ? 'taotheeq' : 'sindi';
        $user->update(['invoice_template' => $newTemplate]);
        $name = ($newTemplate === 'sindi') ? 'Sindi Residence (داماك الجوهرة)' : 'توثيق العقود';

        return back()->with('success', "تم تحويل قالب فاتورة المستخدم {$user->name} إلى: {$name}.");
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك تعطيل حسابك الخاص.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'تفعيل' : 'تعطيل';

        return back()->with('success', "تم {$status} حساب {$user->name} بنجاح.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم بنجاح.');
    }
}
