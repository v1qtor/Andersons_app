<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="color-scheme: light">
    <head>
        @include('partials.head')
        <script>document.documentElement.classList.remove('dark')</script>
        <style>
            @keyframes floatA { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-18px) rotate(6deg); } }
            @keyframes floatB { 0%,100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(14px) rotate(-5deg); } }
            @keyframes floatC { 0%,100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-10px) scale(1.05); } }
            @keyframes pulse-slow { 0%,100% { opacity: 0.18; } 50% { opacity: 0.35; } }
            @keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
            @keyframes spin-reverse { from { transform: rotate(0deg); } to { transform: rotate(-360deg); } }

            .float-a { animation: floatA 7s ease-in-out infinite; }
            .float-b { animation: floatB 9s ease-in-out infinite; }
            .float-c { animation: floatC 11s ease-in-out infinite; }
            .pulse-slow { animation: pulse-slow 5s ease-in-out infinite; }
            .spin-slow { animation: spin-slow 30s linear infinite; }
            .spin-reverse { animation: spin-reverse 25s linear infinite; }
        </style>
    </head>
    <body class="min-h-screen antialiased overflow-x-hidden" style="background: linear-gradient(145deg, #042f2e 0%, #0d4a4a 30%, #0e7490 65%, #0891b2 100%)">

        <!-- Background decorations -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none select-none">
            <div class="absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full blur-3xl pulse-slow" style="background:radial-gradient(circle,rgba(6,182,212,0.30) 0%,transparent 65%)"></div>
            <div class="absolute -bottom-40 -right-40 w-[550px] h-[550px] rounded-full blur-3xl pulse-slow" style="background:radial-gradient(circle,rgba(8,145,178,0.25) 0%,transparent 65%);animation-delay:2s"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] rounded-full blur-3xl pulse-slow" style="background:radial-gradient(circle,rgba(34,211,238,0.15) 0%,transparent 70%);animation-delay:1s"></div>
            <div class="absolute top-8 right-8 w-72 h-72 rounded-full blur-3xl pulse-slow" style="background:radial-gradient(circle,rgba(20,184,166,0.2) 0%,transparent 70%);animation-delay:3s"></div>
            <div class="absolute top-12 left-12 w-32 h-32 rounded-full border-2 float-a opacity-20" style="border-color:rgba(34,211,238,0.7)"></div>
            <div class="absolute top-24 left-36 w-12 h-12 rounded-full border float-b opacity-35" style="border-color:rgba(6,182,212,0.8)"></div>
            <div class="absolute bottom-16 left-10 w-20 h-20 rounded-full border-2 float-c opacity-20" style="border-color:rgba(14,116,144,0.8)"></div>
            <div class="absolute bottom-36 left-44 w-7 h-7 rounded-full float-a opacity-50" style="background:rgba(34,211,238,0.35);animation-delay:2s"></div>
            <div class="absolute top-10 right-24 w-24 h-24 rounded-full border-2 float-b opacity-20" style="border-color:rgba(6,182,212,0.7)"></div>
            <div class="absolute top-36 right-8 w-9 h-9 rounded-full float-c opacity-40" style="background:rgba(34,211,238,0.3);animation-delay:3.5s"></div>
            <div class="absolute bottom-20 right-14 w-16 h-16 rounded-full border float-a opacity-25" style="border-color:rgba(20,184,166,0.6);animation-delay:1.5s"></div>
            <div class="absolute -top-12 right-40 w-56 h-56 rounded-full border opacity-[0.12] spin-slow" style="border-width:2px;border-color:rgba(34,211,238,0.9);border-style:dashed"></div>
            <div class="absolute -bottom-10 -left-10 w-48 h-48 rounded-full border opacity-[0.12] spin-reverse" style="border-width:2px;border-color:rgba(6,182,212,0.9);border-style:dashed"></div>
            <div class="absolute top-1/2 -left-32 -translate-y-1/2 w-80 h-80 rounded-full border opacity-[0.06] spin-slow" style="border-width:1px;border-color:rgba(34,211,238,1);animation-duration:50s"></div>
            <div class="absolute top-1/3 left-6 w-2 h-2 rounded-full float-b opacity-60" style="background:rgba(34,211,238,0.7)"></div>
            <div class="absolute top-2/3 left-20 w-1.5 h-1.5 rounded-full float-a opacity-50" style="background:rgba(6,182,212,0.8);animation-delay:4s"></div>
            <div class="absolute top-1/4 right-8 w-2.5 h-2.5 rounded-full float-c opacity-55" style="background:rgba(20,184,166,0.7);animation-delay:1s"></div>
            <div class="absolute top-3/4 right-24 w-2 h-2 rounded-full float-b opacity-45" style="background:rgba(34,211,238,0.6);animation-delay:2.5s"></div>
            <div class="absolute top-1/2 right-4 w-1.5 h-1.5 rounded-full float-a opacity-40" style="background:rgba(6,182,212,0.8);animation-delay:0.5s"></div>
            <div class="absolute top-1/4 left-0 right-0 h-px opacity-[0.10]" style="background:linear-gradient(90deg,transparent,rgba(34,211,238,1),transparent)"></div>
            <div class="absolute top-3/4 left-0 right-0 h-px opacity-[0.07]" style="background:linear-gradient(90deg,transparent,rgba(6,182,212,1),transparent)"></div>
            <div class="absolute top-0 left-0 w-48 h-48 opacity-[0.05]" style="background:conic-gradient(from 0deg,rgba(34,211,238,0.8),transparent 60%)"></div>
            <div class="absolute bottom-0 right-0 w-48 h-48 opacity-[0.05]" style="background:conic-gradient(from 180deg,rgba(6,182,212,0.8),transparent 60%)"></div>
        </div>

        <!-- Main layout -->
        <div class="relative flex min-h-screen flex-col items-center justify-center p-6">
            <div class="w-full max-w-3xl">
                <div class="flex rounded-2xl overflow-hidden shadow-2xl">

                    <!-- Left branding panel -->
                    <div class="hidden md:flex flex-col flex-1 p-10 relative overflow-hidden"
                        style="background:rgba(0,0,0,0.28);backdrop-filter:blur(16px)">
                        <div class="absolute -top-12 -right-12 w-40 h-40 rounded-full opacity-20 blur-2xl" style="background:#22d3ee"></div>
                        <div class="absolute -bottom-10 -left-10 w-36 h-36 rounded-full opacity-15 blur-2xl" style="background:#0891b2"></div>
                        <div class="absolute top-1/2 right-4 w-24 h-24 rounded-full border opacity-10 spin-slow" style="border-color:rgba(34,211,238,0.8);border-style:dashed;border-width:1px"></div>

                        <div class="relative z-10 flex items-center gap-3">
                            <img src="/favicon.svg" alt="Logo" class="w-10 h-10 drop-shadow-lg">
                            <span class="text-white text-xl font-bold">{{ config('app.name') }}</span>
                        </div>
                        <div class="relative z-10 flex-1 flex items-center">
                            <p class="text-white/90 text-3xl font-bold leading-snug">Welcome to<br>the household.</p>
                        </div>
                    </div>

                    <!-- Right form panel -->
                    <div class="flex-1 bg-white p-10">
                        <h2 class="text-2xl font-bold text-gray-900 mb-7">Sign in</h2>
                        {{ $slot }}
                    </div>

                </div>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
