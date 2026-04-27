<?php

namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\Folder;
use Illuminate\Http\Request;

class CheckpointController extends Controller
{
    public function index()
{
    // Get only non-temporary checkpoints
    $checkpoints = Checkpoint::with(['user', 'trips'])
        ->whereDoesntHave('trips', function($query) {
            $query->where('trip_checkpoints.is_temporary', true);
        })
        ->whereNotNull('folder_id') // Only checkpoints with folders
        ->get();
    
    // Only include folders that have permanent checkpoints
    $folders = Folder::whereHas('checkpoints', function($query) {
        $query->whereDoesntHave('trips', function($subQuery) {
            $subQuery->where('trip_checkpoints.is_temporary', true);
        })->whereNotNull('folder_id');
    })->get();
    
    return view('checkpoints.index', compact('checkpoints', 'folders'));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'folder_id' => 'nullable|exists:folders,id',
            'new_folder' => 'nullable|string|max:255',
        ]);

        // Create new folder if specified
        if (!empty($validated['new_folder'])) {
            $folder = Folder::create(['name' => $validated['new_folder']]);
            $validated['folder_id'] = $folder->id;
        }

        // Combine lat/long into coordinates
        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $validated['coordinates'] = $validated['latitude'] . ',' . $validated['longitude'];
        }

        $validated['user_id'] = auth()->id();
        
        unset($validated['new_folder']);

        Checkpoint::create($validated);

        return back()->with('success', 'Checkpoint created!');
    }

    public function update(Request $request, Checkpoint $checkpoint)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        if (!empty($validated['latitude']) && !empty($validated['longitude'])) {
            $validated['coordinates'] = $validated['latitude'] . ',' . $validated['longitude'];
        }

        $checkpoint->update($validated);

        return back()->with('success', 'Checkpoint updated!');
    }

    public function destroy(Checkpoint $checkpoint)
    {
        $checkpoint->delete();

        return back()->with('success', 'Checkpoint deleted!');
    }
}