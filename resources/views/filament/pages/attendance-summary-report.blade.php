<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Period Filter --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Attendance Summary - {{ $this->getPeriod() }}</h2>
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
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Hours Worked</h3>
                <p class="text-3xl font-bold mt-2">
                    {{ number_format(\App\Models\Attendance::whereYear('date', $this->year)->whereMonth('date', $this->month)->sum('total_hours'), 0) }}h
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Overtime Hours</h3>
                <p class="text-3xl font-bold mt-2">
                    {{ number_format(\App\Models\Attendance::whereYear('date', $this->year)->whereMonth('date', $this->month)->sum('overtime_hours'), 0) }}h
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Absences</h3>
                <p class="text-3xl font-bold mt-2">
                    {{ \App\Models\Attendance::whereYear('date', $this->year)->whereMonth('date', $this->month)->where('status', 'absent')->count() }}
                </p>
            </div>
        </div>

        {{-- Detailed Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
