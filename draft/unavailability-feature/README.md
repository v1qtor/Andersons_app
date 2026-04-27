# Unavailability Feature — Draft

Bu klasör, Unavailability özelliği için yapılan tüm değişiklikleri içerir.
Projedeki değişiklikler geri alınmıştır. Hazır olduğunuzda bu dosyaları kullanabilirsiniz.

## Değiştirilen / Eklenen Dosyalar

1. `routes/web.php` — Yeni route eklendi
2. `app/Http/Middleware/IsUnavailabilityUser.php` — Yeni middleware (dosya silinmeli)
3. `bootstrap/app.php` — Middleware alias eklendi
4. `resources/views/components/layouts/app/sidebar.blade.php` — Sidebar link eklendi
5. `app/Livewire/Unavailability.php` — Yeni Livewire component (dosya silinmeli)
6. `resources/views/livewire/unavailability.blade.php` — Yeni blade view (dosya silinmeli)
7. `app/Livewire/Schedule/ScheduleCalendar.php` — unavailableUserIds eklendi
8. `resources/views/components/schedule-calendar.blade.php` — prop eklendi
9. `resources/views/components/task-form-modal.blade.php` — prop + @continue eklendi
