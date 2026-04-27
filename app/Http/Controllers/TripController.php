<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripCategory;
use App\Models\Status;
use App\Models\Checkpoint;
use App\Models\User;
use App\Models\AttachedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['status', 'tripCategory', 'users', 'checkpoints']);
        
        if ($request->has('status') && $request->status !== 'all' && $request->status !== '') {
            $query->whereHas('status', function($q) use ($request) {
                $q->where('name', $request->status);
            });
        }
        
        $trips = $query->orderBy('start_date', 'desc')->get();
        
        $categories = TripCategory::all();
        $users = User::all();

        $checkpoints = Checkpoint::whereNotNull('folder_id')
            ->whereDoesntHave('trips', function($query) {
                $query->where('trip_checkpoints.is_temporary', true);
            })
            ->get();
        
        return view('trips.index', compact('trips', 'categories', 'users', 'checkpoints'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'trip_category_id' => 'required|exists:trip_categories,id',
            'buffer_alert' => 'nullable|date',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'checkpoint_ids' => 'nullable|array',
            'checkpoint_ids.*' => 'exists:checkpoints,id',
            'temp_checkpoint_names' => 'nullable|array',
            'temp_checkpoint_names.*' => 'nullable|string',
            'temp_checkpoint_addresses' => 'nullable|array',
            'temp_checkpoint_addresses.*' => 'nullable|string',
        ]);

        $statusId = $this->determineStatus($validated['start_date'], $validated['end_date']);
        
        $trip = Trip::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'trip_category_id' => $validated['trip_category_id'],
            'buffer_alert' => $validated['buffer_alert'] ?? null,
            'status_id' => $statusId,
        ]);

        $trip->users()->attach($validated['user_ids'], ['is_organizer' => false]);
        
        if (!empty($validated['checkpoint_ids'])) {
            foreach ($validated['checkpoint_ids'] as $index => $checkpointId) {
                $trip->checkpoints()->attach($checkpointId, [
                    'order' => $index + 1,
                    'arrival_date' => null,
                    'is_confirmed' => false,
                    'is_temporary' => false,
                ]);
            }
        }

        $order = ($trip->checkpoints()->max('order') ?? 0);
        if (!empty($validated['temp_checkpoint_names'])) {
            foreach ($validated['temp_checkpoint_names'] as $index => $name) {
                if (!empty($name)) {
                    $order++;
                    $tempCheckpoint = Checkpoint::create([
                        'location' => $name,
                        'address' => $validated['temp_checkpoint_addresses'][$index] ?? null,
                        'coordinates' => null,
                        'folder_id' => null,
                    ]);
                    
                    $trip->checkpoints()->attach($tempCheckpoint->id, [
                        'order' => $order,
                        'arrival_date' => null,
                        'is_confirmed' => false,
                        'is_temporary' => true,
                        'temp_location' => $name,
                        'temp_address' => $validated['temp_checkpoint_addresses'][$index] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('trips.index')->with('success', 'Trip planned successfully!');
    }

    public function update(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'trip_category_id' => 'required|exists:trip_categories,id',
            'buffer_alert' => 'nullable|date',
        ]);

        $statusId = $this->determineStatus($validated['start_date'], $validated['end_date']);
        $validated['status_id'] = $statusId;

        $trip->update($validated);

        return redirect()->route('trips.index')->with('success', 'Trip updated successfully!');
    }

    public function destroy(Trip $trip)
    {
        foreach ($trip->checkpoints as $checkpoint) {
            if ($checkpoint->pivot->is_temporary) {
                $trip->checkpoints()->detach($checkpoint->id);
                $checkpoint->delete();
            }
        }
        
        $trip->delete();

        return redirect()->route('trips.index')->with('success', 'Trip deleted successfully!');
    }

    public function cancel(Trip $trip)
    {
        $cancelledStatus = Status::where('name', 'cancelled')->where('type', 'trip')->first();
        
        if ($cancelledStatus) {
            $trip->update(['status_id' => $cancelledStatus->id]);
        }

        return back()->with('success', 'Trip cancelled successfully!');
    }

    public function addCheckpoint(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'checkpoint_id' => 'nullable|exists:checkpoints,id',
            'temp_name' => 'nullable|string|max:255',
            'temp_address' => 'nullable|string',
        ]);

        $maxOrder = $trip->checkpoints()->max('order') ?? 0;

        if (!empty($validated['checkpoint_id'])) {
            $trip->checkpoints()->attach($validated['checkpoint_id'], [
                'order' => $maxOrder + 1,
                'arrival_date' => null,
                'is_confirmed' => false,
                'is_temporary' => false,
            ]);
        } elseif (!empty($validated['temp_name'])) {
            $tempCheckpoint = Checkpoint::create([
                'location' => $validated['temp_name'],
                'address' => $validated['temp_address'] ?? null,
                'coordinates' => null,
                'folder_id' => null,
            ]);
            
            $trip->checkpoints()->attach($tempCheckpoint->id, [
                'order' => $maxOrder + 1,
                'arrival_date' => null,
                'is_confirmed' => false,
                'is_temporary' => true,
                'temp_location' => $validated['temp_name'],
                'temp_address' => $validated['temp_address'] ?? null,
            ]);
        }

        return back()->with('success', 'Checkpoint added to trip!');
    }

    public function removeCheckpoint(Trip $trip, Checkpoint $checkpoint)
    {
        $pivot = $trip->checkpoints()->where('checkpoint_id', $checkpoint->id)->first()->pivot;
        
        $trip->checkpoints()->detach($checkpoint->id);
        
        if ($pivot->is_temporary) {
            $checkpoint->delete();
        }
        
        $this->reorderCheckpoints($trip);
        
        return back()->with('success', 'Checkpoint removed from trip!');
    }

    public function markCheckpointArrived(Trip $trip, Checkpoint $checkpoint)
    {
        $trip->checkpoints()->updateExistingPivot($checkpoint->id, [
            'is_confirmed' => true,
            'arrival_date' => now(),
        ]);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Checkpoint marked as arrived!'
            ]);
        }

        return back()->with('success', 'Checkpoint marked as arrived!');
    }

    public function uploadFile(Request $request, Trip $trip)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('trip-files/' . $trip->id, 'public');

        AttachedFile::create([
            'name' => $request->name ?? $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'trip_id' => $trip->id,
        ]);

        return back()->with('success', 'File uploaded successfully!');
    }

    public function removeFile(Trip $trip, AttachedFile $file)
    {
        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return back()->with('success', 'File removed!');
    }

    private function determineStatus($startDate, $endDate)
{
    $now = now();
    $startDate = \Carbon\Carbon::parse($startDate);
    $endDate = \Carbon\Carbon::parse($endDate);
    
    // Ensure statuses exist
    $this->ensureStatusesExist();
    
    if ($now->lt($startDate)) {
        $status = Status::where('name', 'upcoming')->where('type', 'trip')->first();
    } elseif ($now->gte($startDate) && $now->lte($endDate)) {
        $status = Status::where('name', 'active')->where('type', 'trip')->first();
    } else {
        $status = Status::where('name', 'completed')->where('type', 'trip')->first();
    }
    
    return $status ? $status->id : Status::where('name', 'upcoming')->where('type', 'trip')->first()->id;
}

private function ensureStatusesExist()
{
    $statuses = ['upcoming', 'active', 'completed', 'cancelled'];
    
    foreach ($statuses as $statusName) {
        Status::firstOrCreate(
            ['name' => $statusName, 'type' => 'trip']
        );
    }
}

    private function reorderCheckpoints(Trip $trip)
    {
        $checkpoints = $trip->checkpoints()->orderByPivot('order')->get();
        foreach ($checkpoints as $index => $checkpoint) {
            $trip->checkpoints()->updateExistingPivot($checkpoint->id, [
                'order' => $index + 1
            ]);
        }
    }

    public function uploadCheckpointImage(Request $request, Trip $trip, Checkpoint $checkpoint)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $image = $request->file('image');
        $path = $image->store('checkpoint-images/' . $trip->id, 'public');

        $trip->checkpoints()->updateExistingPivot($checkpoint->id, [
            'image_path' => $path,
        ]);

        return back()->with('success', 'Image uploaded successfully!');
    }

    public function removeCheckpointImage(Trip $trip, Checkpoint $checkpoint)
    {
        $pivotData = $trip->checkpoints()->where('checkpoint_id', $checkpoint->id)->first()->pivot;
        
        if ($pivotData->image_path) {
            Storage::disk('public')->delete($pivotData->image_path);
        }

        $trip->checkpoints()->updateExistingPivot($checkpoint->id, [
            'image_path' => null,
        ]);

        return back()->with('success', 'Image removed!');
    }

    public function reorderCheckpointsRoute(Request $request, Trip $trip)
    {
        $order = $request->input('order', []);
    
        foreach ($order as $index => $checkpointId) {
            $trip->checkpoints()->updateExistingPivot($checkpointId, [
                'order' => $index + 1
            ]);
        }
    
        return response()->json(['success' => true]);
    }
}