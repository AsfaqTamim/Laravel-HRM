<x-filament-panels::page>
    @php $kanban = $this->getKanbanTasks(); $isTable = $this->viewMode === 'table'; @endphp

    <div
        x-data="kanbanBoard()"
        x-init="initBoard($el)"
        class="flex-1"
    >
        <style>
            /* ─── columns ─── */
            .kanban-board { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 24px; min-height: calc(100vh - 270px); }

            .kanban-col { flex: 1; min-width: 270px; max-width: 360px; display: flex; flex-direction: column; border-radius: 8px; background: rgba(249,250,251,0.5); border: 1px solid #e5e7eb; }
            :where(.dark) .kanban-col { background: rgba(17,24,39,0.2); border-color: #374151; }

            .kanban-col-head { display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-bottom: 1px solid #e5e7eb; }
            :where(.dark) .kanban-col-head { border-color: #374151; }

            .kanban-col-title { display: flex; align-items: center; gap: 6px; }
            .kanban-col-dot { width: 8px; height: 8px; border-radius: 50%; }
            .kanban-col-label { font-size: 13px; font-weight: 600; color: #111827; }
            :where(.dark) .kanban-col-label { color: #fff; }

            .kanban-col-count { font-size: 11px; font-weight: 500; color: #9ca3af; background: rgba(229,231,235,0.6); padding: 2px 6px; border-radius: 4px; min-width: 20px; text-align: center; }
            :where(.dark) .kanban-col-count { background: rgba(55,65,81,0.6); }

            .kanban-list { flex: 1; padding: 6px; display: flex; flex-direction: column; gap: 4px; overflow-y: auto; min-height: 80px; }

            /* ─── card ─── */
            .kanban-card {
                position: relative;
                background: #fff;
                border-radius: 6px;
                border: 1px solid #f3f4f6;
                cursor: grab;
                overflow: hidden;
                transition: border-color .15s;
                user-select: none;
            }
            :where(.dark) .kanban-card { background: #1f2937; border-color: #374151; }
            .kanban-card:hover { border-color: #d1d5db; }
            :where(.dark) .kanban-card:hover { border-color: #6b7280; }
            .kanban-card:active { cursor: grabbing; }
            .kanban-card.dragging { opacity: .5; }

            .kanban-card-bar {
                position: absolute;
                left: 0; top: 0; bottom: 0;
                width: 3px;
                border-radius: 3px 0 0 3px;
            }

            .kanban-card-body { padding: 10px 10px 10px 14px; }

            .kanban-card-title {
                font-size: 13px;
                font-weight: 500;
                color: #111827;
                line-height: 1.3;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                padding-right: 44px;
            }
            :where(.dark) .kanban-card-title { color: #fff; }

            .kanban-card-meta {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-top: 6px;
                font-size: 11px;
                color: #9ca3af;
            }

            .kanban-card-assignee { display: flex; align-items: center; gap: 4px; }
            .kanban-card-avatar {
                width: 14px; height: 14px; border-radius: 50%;
                background: #d1d5db;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 7px; font-weight: 600; color: #fff;
                flex-shrink: 0;
                text-transform: uppercase;
            }
            :where(.dark) .kanban-card-avatar { background: #6b7280; }
            .kanban-card-name { max-width: 70px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

            .kanban-card-due { display: flex; align-items: center; gap: 3px; }
            .kanban-card-due.overdue { color: #ef4444; font-weight: 500; }
            :where(.dark) .kanban-card-due.overdue { color: #f87171; }
            .kanban-card-due svg { width: 11px; height: 11px; flex-shrink: 0; }

            .kanban-card-project {
                margin-left: auto;
                display: inline-flex;
                align-items: center;
                padding: 1px 4px;
                border-radius: 3px;
                font-size: 10px;
                font-weight: 500;
                background: #f3f4f6;
                color: #6b7280;
                line-height: 1.3;
            }
            :where(.dark) .kanban-card-project { background: #374151; color: #9ca3af; }

            .kanban-empty {
                display: flex;
                align-items: center;
                justify-content: center;
                height: 60px;
                font-size: 11px;
                color: #9ca3af;
                border: 1.5px dashed #e5e7eb;
                border-radius: 6px;
            }
            :where(.dark) .kanban-empty { border-color: #374151; color: #6b7280; }

            /* ─── card actions (hover) ─── */
            .kanban-card-actions {
                position: absolute;
                top: 6px; right: 6px;
                display: none;
                gap: 2px;
                z-index: 10;
            }
            .kanban-card:hover .kanban-card-actions { display: flex; }

            .kanban-card-btn {
                width: 22px; height: 22px;
                border-radius: 4px;
                border: 1px solid #e5e7eb;
                background: #fff;
                color: #6b7280;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: background .1s, color .1s;
                padding: 0;
                font-size: 11px;
                text-decoration: none;
            }
            :where(.dark) .kanban-card-btn { background: #374151; border-color: #4b5563; color: #d1d5db; }
            .kanban-card-btn:hover { background: #f3f4f6; color: #111827; }
            :where(.dark) .kanban-card-btn:hover { background: #4b5563; color: #fff; }
            .kanban-card-btn.danger:hover { color: #ef4444; }
            :where(.dark) .kanban-card-btn.danger:hover { color: #f87171; }

            .kanban-card-btn svg { width: 12px; height: 12px; }

            /* ─── sortable ─── */
            .sortable-ghost { opacity: .3 !important; background: rgba(59,130,246,.08) !important; border: 2px dashed rgba(59,130,246,.4) !important; border-radius: 6px !important; }
            .sortable-drag { opacity: .95 !important; transform: rotate(2deg) !important; box-shadow: 0 4px 12px rgba(0,0,0,.1) !important; }

            /* ─── toggle button ─── */
            .view-toggle {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 5px 12px;
                font-size: 12px;
                font-weight: 500;
                border-radius: 6px;
                border: 1px solid #e5e7eb;
                background: #fff;
                color: #374151;
                cursor: pointer;
                transition: background .1s;
            }
            :where(.dark) .view-toggle { background: #1f2937; border-color: #4b5563; color: #d1d5db; }
            .view-toggle:hover { background: #f3f4f6; }
            :where(.dark) .view-toggle:hover { background: #374151; }
            .view-toggle svg { width: 14px; height: 14px; }
        </style>

        @if(!$isTable)
        {{-- ═══════════ KANBAN VIEW ═══════════ --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <h2 style="font-size:14px;font-weight:600;color:#111827;margin:0;">Tasks</h2>
                <span style="font-size:11px;color:#9ca3af;">({{ collect($kanban)->sum(fn($c) => $c->count()) }})</span>
            </div>
            <button class="view-toggle" @click="$wire.toggleView()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                List View
            </button>
        </div>

        <div class="kanban-board">
            @foreach([
                'todo' => ['label' => 'To Do', 'color' => '#6b7280'],
                'in_progress' => ['label' => 'In Progress', 'color' => '#3b82f6'],
                'done' => ['label' => 'Done', 'color' => '#22c55e'],
                'cancelled' => ['label' => 'Cancelled', 'color' => '#ef4444'],
            ] as $status => $col)
            <div class="kanban-col">
                <div class="kanban-col-head">
                    <div class="kanban-col-title">
                        <span class="kanban-col-dot" style="background:{{ $col['color'] }}"></span>
                        <span class="kanban-col-label">{{ $col['label'] }}</span>
                    </div>
                    <span class="kanban-col-count">{{ $kanban[$status]->count() }}</span>
                </div>

                <div class="kanban-list" data-status="{{ $status }}" x-ref="col-{{ $status }}">
                    @forelse($kanban[$status] as $task)
                    @php
                        $overdue = $task->due_date && $task->due_date->isPast() && !in_array($task->status, ['done', 'cancelled']);
                        $pColors = ['low'=>'#6b7280','medium'=>'#3b82f6','high'=>'#f59e0b','urgent'=>'#ef4444'];
                    @endphp
                    <div
                        draggable="true"
                        data-task-id="{{ $task->id }}"
                        class="kanban-card"
                        :class="{ 'dragging': dragging && draggedId === {{ $task->id }} }"
                    >
                        <div class="kanban-card-bar" style="background:{{ $pColors[$task->priority] ?? '#6b7280' }}"></div>

                        {{-- hover actions --}}
                        <div class="kanban-card-actions" @click.stop>
                            <a href="{{ url("tasks/{$task->id}/edit") }}" class="kanban-card-btn" title="Edit">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <button class="kanban-card-btn danger" title="Delete"
                                @click="if(confirm('Delete {{ addslashes($task->title) }}?')) { $wire.call('deleteTask', {{ $task->id }}) }">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        <div class="kanban-card-body">
                            <div class="kanban-card-title">{{ $task->title }}</div>

                            <div class="kanban-card-meta">
                                @if($task->assignee)
                                <span class="kanban-card-assignee" title="{{ $task->assignee->name }}">
                                    <span class="kanban-card-avatar">{{ substr($task->assignee->name, 0, 1) }}</span>
                                    <span class="kanban-card-name">{{ explode(' ', $task->assignee->name)[0] }}</span>
                                </span>
                                @endif

                                @if($task->due_date)
                                <span class="kanban-card-due {{ $overdue ? 'overdue' : '' }}">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $task->due_date->format('d M') }}
                                </span>
                                @endif

                                @if($task->project)
                                <span class="kanban-card-project">{{ $task->project->name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="kanban-empty">Drop tasks here</div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- ═══════════ TABLE VIEW ═══════════ --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <h2 style="font-size:14px;font-weight:600;color:#111827;margin:0;">Tasks</h2>
            </div>
            <button class="view-toggle" @click="$wire.toggleView()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
                Board View
            </button>
        </div>
        {{ $this->table }}
        @endif
    </div>

    @assets
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    @endassets

    @script
    <script>
        let sortableInstances = [];

        window.kanbanBoard = function() {
            return {
                dragging: false,
                draggedId: null,
                initBoard(el) {
                    const statuses = ['todo', 'in_progress', 'done', 'cancelled'];
                    statuses.forEach(status => {
                        const container = this.$refs['col-' + status];
                        if (!container) return;
                        const sortable = new Sortable(container, {
                            group: 'tasks',
                            animation: 200,
                            easing: 'cubic-bezier(0.25, 0.1, 0.25, 1)',
                            ghostClass: 'sortable-ghost',
                            dragClass: 'sortable-drag',
                            delay: 100,
                            delayOnTouchOnly: true,
                            onStart: (evt) => {
                                this.dragging = true;
                                this.draggedId = parseInt(evt.item.dataset.taskId);
                            },
                            onEnd: (evt) => {
                                this.dragging = false;
                                this.draggedId = null;
                                const taskId = parseInt(evt.item.dataset.taskId);
                                const newStatus = evt.to.dataset.status;
                                const oldStatus = evt.from.dataset.status;
                                if (oldStatus !== newStatus) {
                                    $wire.updateTaskStatus(taskId, newStatus);
                                }
                            },
                        });
                        sortableInstances.push(sortable);
                    });
                },
            };
        };
    </script>
    @endscript
</x-filament-panels::page>
