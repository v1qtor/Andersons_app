<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripCategory;
use App\Models\Status;
use App\Models\Checkpoint;
use App\Models\CheckpointImage;
use App\Models\PlusOne;
use App\Models\User;
use App\Models\AttachedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['status', 'tripCategory', 'users', 'checkpoints', 'checkpointImages', 'plusOnes']);
        if ($request->has('status') && $request->status !== 'all' && $request->status !== '') {
            $query->whereHas('status', fn($q) => $q->where('name', $request->status));
        }
        if ($request->has('search') && $request->search !== '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
        }
        
        $trips = $query->orderBy('start_date', 'desc')->paginate(3)->withQueryString();
        $categories = TripCategory::all();
        $users = User::all();
        $checkpoints = Checkpoint::whereNotNull('folder_id')
            ->whereDoesntHave('trips', fn($q) => $q->where('trip_checkpoints.is_temporary', true))
            ->get();
        return view('trips.index', compact('trips', 'categories', 'users', 'checkpoints'));
    }

    public function store(Request $request)
    {
        // Check if user is authorized to create trips
        $canCreateTrip = auth()->user()->email === 'laurien@andersons.com' || auth()->user()->email === 'andersons@andersons.com';
        if (!$canCreateTrip) {
            abort(403, 'You are not authorized to create trips.');
        }

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
            'temp_checkpoint_lat' => 'nullable|array',
            'temp_checkpoint_lat.*' => 'nullable|string',
            'temp_checkpoint_lng' => 'nullable|array',
            'temp_checkpoint_lng.*' => 'nullable|string',
            'plus_one_names' => 'nullable|array',
            'plus_one_names.*' => 'nullable|string',
            'plus_one_emails' => 'nullable|array',
            'plus_one_emails.*' => 'nullable|email',
            'plus_one_phones' => 'nullable|array',
            'plus_one_phones.*' => 'nullable|string',
        ]);

        $this->ensureStatusesExist();
        $now = now();
        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);
        if ($now->lt($start)) {
            $status = Status::where('name', 'upcoming')->where('type', 'trip')->first();
        } elseif ($now->gte($start) && $now->lte($end)) {
            $status = Status::where('name', 'active')->where('type', 'trip')->first();
        } else {
            $status = Status::where('name', 'completed')->where('type', 'trip')->first();
        }
        $statusId = $status->id ?? Status::where('name', 'upcoming')->where('type', 'trip')->first()->id;

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
            foreach ($validated['checkpoint_ids'] as $idx => $cpId) {
                $trip->checkpoints()->attach($cpId, [
                    'order' => $idx + 1,
                    'arrival_date' => null,
                    'is_confirmed' => false,
                    'is_temporary' => false,
                ]);
            }
        }

        $order = $trip->checkpoints()->max('order') ?? 0;
        if (!empty($validated['temp_checkpoint_names'])) {
            foreach ($validated['temp_checkpoint_names'] as $idx => $name) {
                if (empty($name)) continue;
                $lat = $validated['temp_checkpoint_lat'][$idx] ?? null;
                $lng = $validated['temp_checkpoint_lng'][$idx] ?? null;
                $coordinates = ($lat && $lng) ? $lat.','.$lng : null;
                $temp = Checkpoint::create([
                    'location' => $name,
                    'address' => $validated['temp_checkpoint_addresses'][$idx] ?? null,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'coordinates' => $coordinates,
                    'folder_id' => null,
                ]);
                $trip->checkpoints()->attach($temp->id, [
                    'order' => ++$order,
                    'arrival_date' => null,
                    'is_confirmed' => false,
                    'is_temporary' => true,
                    'temp_location' => $name,
                    'temp_address' => $validated['temp_checkpoint_addresses'][$idx] ?? null,
                ]);
            }
        }

        // Add plus-ones
        if (!empty($validated['plus_one_names'])) {
            foreach ($validated['plus_one_names'] as $idx => $name) {
                if (empty($name)) continue;
                PlusOne::create([
                    'trip_id' => $trip->id,
                    'added_by' => auth()->id(),
                    'name' => $name,
                    'email' => $validated['plus_one_emails'][$idx] ?? null,
                    'phone' => $validated['plus_one_phones'][$idx] ?? null,
                ]);
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
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'plus_one_names' => 'nullable|array',
            'plus_one_names.*' => 'nullable|string',
            'plus_one_emails' => 'nullable|array',
            'plus_one_emails.*' => 'nullable|email',
            'plus_one_phones' => 'nullable|array',
            'plus_one_phones.*' => 'nullable|string',
        ]);

        $this->ensureStatusesExist();
        $now = now();
        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);
        if ($now->lt($start)) {
            $status = Status::where('name', 'upcoming')->where('type', 'trip')->first();
        } elseif ($now->gte($start) && $now->lte($end)) {
            $status = Status::where('name', 'active')->where('type', 'trip')->first();
        } else {
            $status = Status::where('name', 'completed')->where('type', 'trip')->first();
        }

        $trip->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'trip_category_id' => $validated['trip_category_id'],
            'buffer_alert' => $validated['buffer_alert'] ?? null,
            'status_id' => $status->id ?? Status::where('name', 'upcoming')->where('type', 'trip')->first()->id,
        ]);
        $trip->users()->sync($validated['user_ids']);

        // Handle plus-ones
        if (!empty($validated['plus_one_names'])) {
            $existingPlusOnes = $trip->plusOnes()->where('added_by', auth()->id())->pluck('id')->toArray();
            $trip->plusOnes()->whereIn('id', $existingPlusOnes)->delete();
            
            foreach ($validated['plus_one_names'] as $index => $name) {
                if (!empty($name)) {
                    PlusOne::create([
                        'trip_id' => $trip->id,
                        'added_by' => auth()->id(),
                        'name' => $name,
                        'email' => $validated['plus_one_emails'][$index] ?? null,
                        'phone' => $validated['plus_one_phones'][$index] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('trips.index')->with('success', 'Trip updated!');
    }

    public function destroy(Trip $trip)
    {
        foreach ($trip->checkpoints as $cp) {
            if ($cp->pivot->is_temporary) {
                $trip->checkpoints()->detach($cp->id);
                $cp->delete();
            }
        }
        $trip->delete();
        return redirect()->route('trips.index')->with('success', 'Trip deleted!');
    }

    public function cancel(Trip $trip)
    {
        $this->ensureStatusesExist();
        $cancelled = Status::where('name', 'cancelled')->where('type', 'trip')->first();
        if ($cancelled) $trip->update(['status_id' => $cancelled->id]);
        return back()->with('success', 'Trip cancelled.');
    }

    public function addCheckpoint(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'checkpoint_id' => 'nullable|exists:checkpoints,id',
            'temp_name' => 'nullable|string|max:255',
            'temp_address' => 'nullable|string',
            'temp_lat' => 'nullable|string',
            'temp_lng' => 'nullable|string',
        ]);
        $max = $trip->checkpoints()->max('order') ?? 0;
        if (!empty($validated['checkpoint_id'])) {
            $trip->checkpoints()->attach($validated['checkpoint_id'], [
                'order' => $max + 1,
                'arrival_date' => null,
                'is_confirmed' => false,
                'is_temporary' => false,
            ]);
        } elseif (!empty($validated['temp_name'])) {
            $lat = $validated['temp_lat'] ?? null;
            $lng = $validated['temp_lng'] ?? null;
            $coords = ($lat && $lng) ? $lat.','.$lng : null;
            $temp = Checkpoint::create([
                'location' => $validated['temp_name'],
                'address' => $validated['temp_address'] ?? null,
                'latitude' => $lat,
                'longitude' => $lng,
                'coordinates' => $coords,
                'folder_id' => null,
            ]);
            $trip->checkpoints()->attach($temp->id, [
                'order' => $max + 1,
                'arrival_date' => null,
                'is_confirmed' => false,
                'is_temporary' => true,
                'temp_location' => $validated['temp_name'],
                'temp_address' => $validated['temp_address'] ?? null,
            ]);
        }
        return back()->with('success', 'Checkpoint added.');
    }

    public function removeCheckpoint(Trip $trip, Checkpoint $checkpoint)
    {
        $pivot = $trip->checkpoints()->where('checkpoint_id', $checkpoint->id)->first()->pivot;
        $trip->checkpoints()->detach($checkpoint->id);
        if ($pivot->is_temporary) $checkpoint->delete();
        $this->reorderCheckpoints($trip);
        return back()->with('success', 'Checkpoint removed.');
    }

    public function markCheckpointArrived(Trip $trip, Checkpoint $checkpoint)
    {
        $trip->checkpoints()->updateExistingPivot($checkpoint->id, [
            'is_confirmed' => true,
            'arrival_date' => now(),
        ]);
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Arrived!');
    }

    public function unmarkCheckpointArrived(Trip $trip, Checkpoint $checkpoint)
    {
        $trip->checkpoints()->updateExistingPivot($checkpoint->id, [
            'is_confirmed' => false,
            'arrival_date' => null,
        ]);
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Checkpoint unmarked.');
    }

    public function uploadFile(Request $request, Trip $trip)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $path = $request->file('file')->store('trip-files', 'public');
        AttachedFile::create([
            'name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $request->file('file')->getClientMimeType(),
            'trip_id' => $trip->id,
        ]);
        return back()->with('success', 'File uploaded.');
    }

    public function removeFile(Trip $trip, AttachedFile $file)
    {
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        $file->delete();
        return back()->with('success', 'File removed.');
    }

    public function uploadCheckpointImage(Request $request, Trip $trip, Checkpoint $checkpoint)
    {
        $request->validate(['images' => 'required|array', 'images.*' => 'image|max:5120']);
        
        foreach ($request->file('images') as $image) {
            $path = $image->store('checkpoint-images', 'public');
            CheckpointImage::create([
                'trip_id' => $trip->id,
                'checkpoint_id' => $checkpoint->id,
                'image_path' => $path,
                'uploaded_by' => auth()->id(),
            ]);
        }
        
        return back()->with('success', 'Image(s) uploaded successfully.');
    }

    public function removeCheckpointImage(Trip $trip, Checkpoint $checkpoint, CheckpointImage $image)
    {
        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        $image->delete();
        return back()->with('success', 'Image removed.');
    }

    public function reorderCheckpointsRoute(Request $request, Trip $trip)
    {
        foreach ($request->input('order', []) as $idx => $cpId) {
            $trip->checkpoints()->updateExistingPivot($cpId, ['order' => $idx + 1]);
        }
        return response()->json(['success' => true]);
    }

    private function reorderCheckpoints(Trip $trip)
    {
        $cps = $trip->checkpoints()->orderByPivot('order')->get();
        foreach ($cps as $i => $cp) {
            $trip->checkpoints()->updateExistingPivot($cp->id, ['order' => $i + 1]);
        }
    }

    private function ensureStatusesExist()
    {
        foreach (['upcoming', 'active', 'completed', 'cancelled'] as $name) {
            if (!Status::where('name', $name)->where('type', 'trip')->exists()) {
                Status::create(['name' => $name, 'type' => 'trip']);
            }
        }
    }
}