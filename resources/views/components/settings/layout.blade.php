@props([
    'heading' => '',
    'subheading' => '',
])

<section class="w-full">
    <div class="mx-auto max-w-5xl">
        <div class="mb-8">
            <flux:heading size="xl">{{ $heading }}</flux:heading>
            @if ($subheading)
                <flux:subheading>{{ $subheading }}</flux:subheading>
            @endif
        </div>

        {{ $slot }}
    </div>
</section>
