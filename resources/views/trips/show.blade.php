@extends('layouts.app')

@section('content')
<div class="min-h-screen py-4 px-2" style="background: #f8fbff; font-size: 0.95rem;">
    <div class="mb-4">
        <a href="{{ route('trips.index') }}" class="text-blue-600 hover:text-blue-800">&larr; Back to Trips</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6 mb-6 border-2 {{ $trip->buffer_alert && now() > $trip->buffer_alert ? 'border-red-200' : 'border-gray-200' }}">
        <div class="flex justify-between items-center mb-3">
            <div>
                <span class="text-2xl font-extrabold text-blue-900" style="font-size: 1.5rem;">{{ $trip->name }}</span>
                <span class="ml-3 px-3 py-1.5 rounded text-lg font-extrabold align-middle shadow-lg" style="font-size: 1.1rem; letter-spacing: 1px; 
                    @if($trip->buffer_alert && now() > $trip->buffer_alert)
                        background-color: #dc2626; color: white;
                    @elseif($trip->status->name == 'active')
                        background-color: #16a34a; color: white;
                    @elseif($trip->status->name == 'upcoming')
                        background-color: #2563eb; color: white;
                    @else
                        background-color: #6b7280; color: white;
                    @endif
                ">
                    @if($trip->buffer_alert && now() > $trip->buffer_alert)
                        OVERDUE RETURN!
                    @else
                        {{ strtoupper($trip->status->name) }}
                    @endif
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('trips.edit', $trip) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1.5 px-4 rounded text-base">Update Trip</a>
                <form action="{{ route('trips.destroy', $trip) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this trip?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1.5 px-4 rounded text-base">Cancel Trip</button>
                </form>
            </div>
        </div>

        <div class="text-gray-700 mb-3 text-base" style="font-size: 1rem;">
            {{ $trip->start_date->format('d F Y H:i') }} &rarr; {{ $trip->end_date->format('d F Y H:i') }} <br>
            <span class="text-sm">Duration: {{ $trip->start_date->diffInDays($trip->end_date) }} days</span>
            @if($trip->buffer_alert)
                <br><span class="text-sm font-semibold">Designated Return: {{ $trip->buffer_alert->format('d F Y H:i') }}</span>
            @endif
        </div>

        @if($trip->buffer_alert && now() > $trip->buffer_alert)
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4" style="font-size: 1rem;">
                <strong>OVERDUE - Designated Return Time:</strong><br>
                {{ $trip->buffer_alert->format('l d F \a\t H:i') }}<br>
                <span class="font-semibold">Travelers have not checked in. Please contact them immediately!</span>
            </div>
        @endif

        <!-- Participants Section -->
        <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <span class="font-bold text-lg">Participants:</span>
                <button onclick="document.getElementById('addParticipantModal').showModal()" class="bg-green-200 hover:bg-green-300 text-green-900 font-bold py-1.5 px-3 rounded text-sm">Add Person</button>
            </div>
            <div class="flex gap-2 flex-wrap">
                @foreach($trip->users as $user)
                    <div class="bg-purple-200 text-purple-800 px-3 py-1 rounded-full text-sm flex items-center gap-2">
                        {{ $user->name }}
                        <form action="{{ route('trips.users.remove', [$trip, $user]) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-purple-600 hover:text-purple-800 font-bold">&times;</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Checkpoints Section -->
        <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <span class="font-bold text-lg">Route Checkpoints:</span>
                <button onclick="document.getElementById('addCheckpointModal').showModal()" class="bg-blue-200 hover:bg-blue-300 text-blue-900 font-bold py-1.5 px-3 rounded text-sm">Add Checkpoint</button>
            </div>
            @php
                $totalCheckpoints = $trip->checkpoints->count();
                $confirmedCheckpoints = $trip->checkpoints->where('pivot.is_confirmed', true)->count();
                $progressPercent = $totalCheckpoints > 0 ? ($confirmedCheckpoints / $totalCheckpoints) * 100 : 0;
            @endphp
            <div class="text-sm text-gray-600 mb-2">Route Progress: <span class="font-bold">{{ $confirmedCheckpoints }} of {{ $totalCheckpoints }} checkpoints reached</span></div>
            <div class="w-full bg-gray-200 rounded h-2 mb-2">
                <div class="bg-blue-400 h-2 rounded" style="width: {{ $progressPercent }}%"></div>
            </div>
            <div class="space-y-2">
                @foreach($trip->checkpoints as $checkpoint)
                    <div class="{{ $checkpoint->pivot->is_confirmed ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }} border rounded p-3 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-base">{{ $checkpoint->location }}</div>
                            @if($checkpoint->address)
                               