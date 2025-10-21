<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Year Filter --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Leaves Usage Report - {{ $this->year }}</h2>
            <form wire:submit.prevent="$refresh" class="flex gap-4">
                {{ $this->form }}
                <x-filament::button type="submit">
                    Refresh Report
                </x-filament::button>
            </form>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Employees</h3>
                <p class="text-3xl font-bold mt-2">{{ $this->getTableQuery()->count() }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Leave Days</h3>
                <p class="text-3xl font-bold mt-2">
                    {{ number_format(\App\Models\Leave::where('status', 'approved')->whereYear('start_date', $this->year)->sum('days_requested')) }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Vacation Days</h3>
                <p class="text-3xl font-bold mt-2">
                    {{ number_format(\App\Models\Leave::where('status', 'approved')->where('leave_type', 'vacation')->whereYear('start_date', $this->year)->sum('days_requested')) }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Sick Days</h3>
                <p class="text-3xl font-bold mt-2">
                    {{ number_format(\App\Models\Leave::where('status', 'approved')->where('leave_type', 'sick')->whereYear('start_date', $this->year)->sum('days_requested')) }}
                </p>
            </div>
        </div>

        {{-- Detailed Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
