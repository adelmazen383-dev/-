<x-app-layout>
    <x-slot name="header">{{ isset($client) ? 'تعديل بيانات العميل' : 'إضافة عميل جديد' }}</x-slot>

    <div class="max-w-2xl">
        <div class="card">
            <form method="POST" action="{{ isset($client) ? route('clients.update', $client) : route('clients.store') }}">
                @csrf
                @if(isset($client)) @method('PUT') @endif

                <div class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label for="name" class="form-label">اسم العميل الكامل</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $client->name ?? '') }}" required
                               class="form-input" placeholder="مثال: أحمد محمد علي">
                        @error('name') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- National ID -->
                    <div>
                        <label for="national_id" class="form-label">رقم الهوية / الإقامة</label>
                        <input type="text" id="national_id" name="national_id" value="{{ old('national_id', $client->national_id ?? '') }}" required
                               class="form-input" placeholder="مثال: 1012345678" dir="ltr">
                        @error('national_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="form-label">رقم الجوال</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $client->phone ?? '') }}" required
                               class="form-input" placeholder="مثال: 0501234567" dir="ltr">
                        @error('phone') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="form-label">نوع العميل</label>
                        <select id="type" name="type" required class="form-input">
                            <option value="lessee" {{ old('type', $client->type ?? '') == 'lessee' ? 'selected' : '' }}>مستأجر</option>
                            <option value="lessor" {{ old('type', $client->type ?? '') == 'lessor' ? 'selected' : '' }}>مؤجر</option>
                        </select>
                        @error('type') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-8">
                    <button type="submit" class="btn btn-indigo">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ isset($client) ? 'حفظ التعديلات' : 'إضافة العميل' }}
                    </button>
                    <a href="{{ route('clients.index') }}" class="btn btn-ghost">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
