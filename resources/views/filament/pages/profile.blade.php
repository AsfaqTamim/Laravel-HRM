<x-filament-panels::page>
    <style>
        .profile-layout { display: grid; gap: 16px; max-width: 100%; }

        .profile-card {
            background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
            overflow: hidden;
        }
        :where(.dark) .profile-card { background: #1f2937; border-color: #374151; }

        {{-- Horizontal card: icon sidebar + content --}}
        .profile-hcard {
            display: grid; grid-template-columns: 40px 1fr;
        }
        .profile-hcard-icon {
            display: flex; align-items: center; justify-content: center;
            background: #f9fafb; border-right: 1px solid #e5e7eb;
        }
        :where(.dark) .profile-hcard-icon { background: #111827; border-color: #374151; }
        .profile-hcard-icon svg { width: 16px; height: 16px; color: #2563eb; }
        .profile-hcard-main { padding: 16px 20px; }
        .profile-hcard-title { font-size: 13px; font-weight: 600; color: #111827; margin-bottom: 14px; }
        :where(.dark) .profile-hcard-title { color: #fff; }

        {{-- avatar --}}
        .profile-avatar-wrap { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; }
        .profile-avatar {
            width: 52px; height: 52px; border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 600; color: #fff;
            text-transform: uppercase; flex-shrink: 0;
        }
        .profile-name { font-size: 16px; font-weight: 600; color: #111827; margin: 0; }
        :where(.dark) .profile-name { color: #fff; }
        .profile-email { font-size: 12px; color: #6b7280; margin: 2px 0 0 0; }

        .profile-role {
            display: inline-flex; align-items: center;
            padding: 2px 8px; border-radius: 4px;
            font-size: 11px; font-weight: 500;
            background: #eff6ff; color: #2563eb;
            margin-top: 6px;
        }
        :where(.dark) .profile-role { background: #1e3a5f; color: #60a5fa; }

        .profile-detail { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
        :where(.dark) .profile-detail { border-color: #374151; }
        .profile-detail:last-child { border-bottom: none; }
        .profile-detail-icon { width: 16px; height: 16px; color: #9ca3af; flex-shrink: 0; }
        .profile-detail-label { font-size: 11px; color: #9ca3af; min-width: 70px; }
        .profile-detail-value { font-size: 12px; color: #374151; }
        :where(.dark) .profile-detail-value { color: #d1d5db; }

        .profile-grid { grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    </style>

    <div class="profile-layout">
        {{-- ═══════════ PROFILE INFO CARD ═══════════ --}}
        <div class="profile-card">
            <div class="profile-hcard">
                <div class="profile-hcard-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="profile-hcard-main">
                    <div class="profile-hcard-title">My Profile</div>
                    <div class="profile-avatar-wrap">
                        <div class="profile-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                        <div>
                            <h2 class="profile-name">{{ Auth::user()->name }}</h2>
                            <p class="profile-email">{{ Auth::user()->email }}</p>
                            @php $role = Auth::user()->roles->first(); @endphp
                            @if($role) <span class="profile-role">{{ $role->name }}</span> @endif
                        </div>
                    </div>
                    <div>
                        <div class="profile-detail">
                            <svg class="profile-detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span class="profile-detail-label">Email</span>
                            <span class="profile-detail-value">{{ Auth::user()->email }}</span>
                        </div>
                        <div class="profile-detail">
                            <svg class="profile-detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="profile-detail-label">Phone</span>
                            <span class="profile-detail-value">{{ Auth::user()->phone ?? '—' }}</span>
                        </div>
                        <div class="profile-detail">
                            <svg class="profile-detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="profile-detail-label">Address</span>
                            <span class="profile-detail-value">{{ Auth::user()->address ?? '—' }}</span>
                        </div>
                        <div class="profile-detail">
                            <svg class="profile-detail-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="profile-detail-label">Joined</span>
                            <span class="profile-detail-value">{{ Auth::user()->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════ FORMS (side by side) ═══════════ --}}
        <div class="profile-grid">
            {{-- Edit Profile --}}
            <div class="profile-card">
                <div class="profile-hcard-main">
                    <div class="profile-hcard-title">Edit Profile</div>
                    <x-filament-panels::form wire:submit="saveInfo">
                        {{ $this->infoForm }}
                        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px;">
                            <x-filament::button type="submit" size="sm">Update Profile</x-filament::button>
                        </div>
                    </x-filament-panels::form>
                </div>
            </div>

            {{-- Change Password --}}
            <div class="profile-card">
                <div class="profile-hcard-main">
                    <div class="profile-hcard-title">Change Password</div>
                    <x-filament-panels::form wire:submit="savePassword">
                        {{ $this->passwordForm }}
                        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px;">
                            <x-filament::button type="submit" size="sm">Change Password</x-filament::button>
                        </div>
                    </x-filament-panels::form>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
