<div class="bg-[#f0fcfc] dark:bg-neutral-900/50 border border-[#e1f7f6] dark:border-neutral-700/50 rounded-2xl p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]">
    <h2 class="text-2xl font-medium text-neutral-800 dark:text-neutral-200 mb-4">Upcoming Dinners</h2>

    <div class="flex flex-col gap-3">
        @forelse($dinnerPlans as $dinner)
            <div wire:key="dinner-{{ $dinner->id }}" class="bg-white dark:bg-neutral-800 border border-neutral-100 dark:border-neutral-700 rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                    <div class="bg-[#fdf4ee] dark:bg-orange-900/20 text-[#ea580c] size-[42px] rounded-xl flex items-center justify-center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6">
                          <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2" />
                          <path d="M7 2v20" />
                          <path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-normal text-neutral-800 dark:text-neutral-200">{{ $dinner->meal->name }}</h4>
                        <div class="text-sm text-neutral-500">
                            {{ $dinner->date_time->format('M j, Y • H:i') }} - {{ $dinner->date_time->copy()->addHour()->format('H:i') }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if($dinner->isJoined)
                        <button
                            wire:click="removeGuest({{ $dinner->id }})"
                            @disabled(!$dinner->hasGuest)
                            class="text-red-400 hover:bg-neutral-100 border border-red-200 dark:hover:bg-neutral-700 rounded p-[3px] bg-white disabled:opacity-40 disabled:cursor-not-allowed"
                            title="Remove guest"
                        >
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                            </svg>
                        </button>

                        <button
                            wire:click="startGuestEdit({{ $dinner->id }})"
                            class="text-blue-500 hover:bg-neutral-100 border border-blue-200 dark:hover:bg-neutral-700 rounded p-[3px] bg-white"
                            title="Add or edit guest"
                        >
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </button>
                    @endif

                    @if($dinner->isJoined)
                        <button wire:click="cancelMeal({{ $dinner->id }})" class="bg-gradient-to-r from-blue-600 to-[#0ba5cc] hover:from-blue-700 hover:to-[#0896ba] text-white text-[15px] px-5 py-1.5 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button disabled class="bg-[#8fd9b5] text-white text-[15px] px-5 py-1.5 rounded-lg cursor-not-allowed">
                            Joined
                        </button>
                    @else
                        <button wire:click="joinMeal({{ $dinner->id }})" class="bg-[#1bcc8a] hover:bg-[#15ab73] text-white text-[15px] px-5 py-1.5 rounded-lg transition-colors">
                            Join
                        </button>
                    @endif
                </div>
                </div>

                @if($editingGuestForMealId === $dinner->id)
                    <div class="mt-3 flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                wire:model.defer="guestName"
                                placeholder="Guest name"
                                class="flex-1 px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 dark:text-white"
                            >
                            <button wire:click="saveGuest({{ $dinner->id }})" class="px-3 py-2 text-sm rounded-lg bg-[#1bcc8a] hover:bg-[#15ab73] text-white">
                                Save guest
                            </button>
                            <button wire:click="$set('editingGuestForMealId', null); $set('guestName', ''); $set('guestNote', '')" class="px-3 py-2 text-sm rounded-lg border border-neutral-300 dark:border-neutral-600 text-neutral-700 dark:text-neutral-200">
                                Close
                            </button>
                        </div>
                        <textarea
                            wire:model.defer="guestNote"
                            rows="2"
                            placeholder="Optional note (allergies, dietary preferences, …)"
                            class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 dark:text-white"
                        ></textarea>
                    </div>
                    @error('guestName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('guestNote')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @elseif($dinner->hasGuest)
                    <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">Guest: {{ $dinner->mySubscription->pivot->guest_name }}</p>
                    @if(filled($dinner->mySubscription->pivot->guest_note))
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400 italic">{{ $dinner->mySubscription->pivot->guest_note }}</p>
                    @endif
                @endif
            </div>
        @empty
            <div class="bg-white dark:bg-neutral-800 border border-neutral-100 dark:border-neutral-700 rounded-xl p-4 shadow-sm text-center text-neutral-500">
                No dinners planned for today.
            </div>
        @endforelse
    </div>

    @if($dinnerPlans->hasPages())
        <div class="mt-4">
            {{ $dinnerPlans->onEachSide(1)->links('livewire::simple-tailwind') }}
        </div>
    @endif
</div>
