<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Tasks</h1>
        <p class="mt-1 text-sm text-gray-600">Manage and track all your tasks</p>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <!-- Search -->
            <div class="sm:col-span-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input
                    type="text"
                    wire:model.live="search"
                    placeholder="Search tasks..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-1">
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select
                    wire:model.live="statusFilter"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="review">Review</option>
                    <option value="completed">Completed</option>
                    <option value="blocked">Blocked</option>
                </select>
            </div>

            <!-- Priority Filter -->
            <div class="sm:col-span-1">
                <label for="priorityFilter" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select
                    wire:model.live="priorityFilter"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>

            <!-- Clear Filters -->
            <div class="sm:col-span-1 flex items-end">
                <button
                    wire:click="clearFilters"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="bg-white shadow overflow-hidden rounded-lg">
        <ul class="divide-y divide-gray-200">
            @forelse($tasks as $task)
            <li class="hover:bg-gray-50 transition">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-3">
                                <h3 class="text-sm font-medium text-gray-900 truncate">
                                    {{ $task->title }}
                                </h3>

                                <!-- Priority Badge -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    @if($task->priority->value === 'critical') bg-red-100 text-red-800
                                    @elseif($task->priority->value === 'high') bg-orange-100 text-orange-800
                                    @elseif($task->priority->value === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($task->priority->value) }}
                                </span>

                                <!-- Status Badge -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    @if($task->status->value === 'completed') bg-green-100 text-green-800
                                    @elseif($task->status->value === 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($task->status->value === 'review') bg-purple-100 text-purple-800
                                    @elseif($task->status->value === 'blocked') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $task->status->value)) }}
                                </span>
                            </div>

                            <div class="mt-2 flex items-center text-sm text-gray-500 space-x-4">
                                <span class="flex items-center">
                                    <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $task->project->name ?? 'No Project' }}
                                </span>

                                @if($task->assignedTo)
                                <span class="flex items-center">
                                    <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $task->assignedTo->name }}
                                </span>
                                @endif

                                @if($task->due_date)
                                <span class="flex items-center {{ $task->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                                    <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $task->due_date->format('M d, Y') }}
                                    @if($task->isOverdue())
                                        (Overdue)
                                    @endif
                                </span>
                                @endif
                            </div>

                            <!-- Tags -->
                            @if($task->tags->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($task->tags as $tag)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: {{ $tag->color }}22; color: {{ $tag->color }};">
                                    {{ $tag->name }}
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <div class="ml-4 flex-shrink-0">
                            <a href="/tasks/{{ $task->id }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                View →
                            </a>
                        </div>
                    </div>
                </div>
            </li>
            @empty
            <li class="px-6 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or create a new task.</p>
            </li>
            @endforelse
        </ul>
    </div>
</div>
