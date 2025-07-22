@php use App\Models\User;
/**
 * @var User[] $users
 */
@endphp
@extends('layout.base_layout')

@section('content')
    @include('layout.sidebar_nav')

    <div class="col p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 mb-1">Users</h2>
                <p class="text-muted mb-0">
                    Manage user access and roles 
                    <span class="ms-1 badge bg-light text-dark">Total Users: {{ $users->total() }}</span>
                </p>
            </div>

            @can('manage_users')
                <a href="{{route('users_create_page')}}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i> Add User
                </a>
            @endcan
        </div>

        <div class="card border-0">
            <div class="card-body p-0">
                <div class="p-4 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="search-container position-relative">
                            <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" 
                                   class="form-control ps-5" 
                                   placeholder="Search users..." 
                                   id="searchUsers">
                        </div>
                        <button type="button" 
                                class="btn btn-outline-secondary ms-3 d-none" 
                                id="resetSearch">
                            Reset filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="text-uppercase ps-4" style="width: 25%">Name</th>
                        <th class="text-uppercase" style="width: 15%">Role</th>
                        <th class="text-uppercase" style="width: 15%">Created</th>
                        <th class="text-uppercase" style="width: 35%">Email</th>
                        <th class="text-uppercase" style="width: 10%">Status</th>
                        @can('manage_users')
                            <th class="text-uppercase text-end pe-4" style="width: 10%">Actions</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    <tr id="noResultsRow" class="d-none">
                        <td colspan="5" class="py-5">
                            <div class="empty-state">
                                <img src="{{ asset_path('img/logo_full.png') }}" alt="AF-TMS Logo" class="empty-state-icon mb-4">
                                <h6 class="empty-state-title">No users found</h6>
                                <p class="empty-state-description">Try adjusting your search or filters</p>
                            </div>
                        </td>
                    </tr>
                    @forelse($users as $user)
                        @php
    // Determine role based on name
    $role = match (true) {
        str_contains(strtolower($user->name), 'master') => 'Administrator',
        str_contains(strtolower($user->name), 'Manager') => 'Test Engineer Manager',
        in_array(strtolower($user->name), ['Senior Test Engineer']) => 'Moderator (Test Engineer)',
        str_contains(strtolower($user->name), '') ||
        str_contains(strtolower($user->name), 'abdul') ||
        str_contains(strtolower($user->name), 'annisa') ||
        str_contains(strtolower($user->name), 'titis') => 'Test Engineer',
        default => 'Engineer',
    };

    // Mask email
    $email = $user->email;
    $atPosition = strpos($email, '@');
    $maskedEmail = substr($email, 0, 1) . str_repeat('*', $atPosition - 2) .
        substr($email, $atPosition - 1);
                        @endphp
                        <tr class="user-row border-bottom">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="user-initial me-3">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td>
                                <span class="badge-role">{{ $role }}</span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>{{ $maskedEmail }}</td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            @can('manage_users')
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-light-primary" data-bs-toggle="dropdown">
                                            Actions <i class="fas fa-ellipsis-vertical ms-2"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="{{route('users_edit_page', $user->id)}}" class="dropdown-item">
                                                <i class="far fa-edit me-2"></i> Edit
                                            </a>
                                            @if(Auth::id() != $user->id)
                                                <form method="POST" action="{{ route('user_toggle_active', $user->id) }}" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-toggle-{{ $user->is_active ? 'off' : 'on' }} me-2"></i>
                                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            @else
                                                <span class="dropdown-item text-muted" style="cursor: not-allowed;">
                                                    <i class="far fa-trash-alt me-2"></i> Cannot delete own account
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5">
                                <div class="empty-state">
                                    <img src="{{ asset_path('img/logo_full.png') }}" alt="AF-TMS Logo" class="empty-state-icon mb-4">
                                    <h6 class="empty-state-title">No users found</h6>
                                    <p class="empty-state-description">Try adjusting your search or filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Simple pagination section -->
            <div class="d-flex justify-content-between align-items-center p-4">
                <div class="text-muted">
                    Page {{ $users->currentPage() }}
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ $users->previousPageUrl() }}" 
                       class="btn btn-light {{ $users->currentPage() == 1 ? 'disabled' : '' }}">
                        Previous
                    </a>
                    <a href="{{ $users->nextPageUrl() }}" 
                       class="btn btn-light {{ !$users->hasMorePages() ? 'disabled' : '' }}">
                        Next
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .user-initial {
            width: 36px;
            height: 36px;
            background-color: #E8F5E9;
            color: #43A047;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .badge-role {
            display: inline-block;
            padding: 4px 12px;
            background-color: #E8F5E9;
            color: #43A047;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
        }

        .table {
            --bs-table-border-color: #dee2e6;
        }

        .table > thead > tr > th {
            color: #666;
            font-size: 12px;
            font-weight: 500;
            padding: 12px 16px;
            background-color: #fff;
            border-top: 1px solid var(--bs-table-border-color);
            border-bottom: 1px solid var(--bs-table-border-color);
        }

        .table > tbody > tr > td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--bs-table-border-color);
        }

        .btn-success {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }

        .btn-icon {
            padding: 0;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
        }

        .btn-icon:hover {
            background: transparent;
            color: #333;
        }

        .btn-icon i {
            font-size: 14px;
            color: #6c757d;
        }

        .dropdown-menu {
            min-width: 120px;
            padding: 8px 0;
            margin-top: 4px;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-radius: 8px;
        }

        .dropdown-item {
            padding: 8px 16px;
            font-size: 14px;
            color: #333;
        }

        .dropdown-item:hover {
            background-color: #f5f5f5;
        }

        .dropdown-item.text-danger {
            color: #dc3545 !important;
        }

        .dropdown-item.text-danger:hover {
            background-color: #fff5f5;
        }

        /* Add subtle hover effect to rows */
        .table > tbody > tr:hover {
            background-color: #fafafa;
        }

        .search-container {
            position: relative;
        }

        .form-control {
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .btn-outline-secondary {
            border: 1px solid #dee2e6;
            color: #666;
            font-size: 0.875rem;
            padding: 0.75rem 1rem;
            background-color: #fff;
        }

        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            color: #333;
            border-color: #dee2e6;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 20px;
            text-align: center;
        }

        .empty-state-icon {
            width: 200px;
            height: auto;
            margin-bottom: 24px;
            opacity: 0.8;
        }

        .empty-state-title {
            font-size: 16px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .empty-state-description {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        .d-none {
            display: none !important;
        }

        .btn-light-primary {
            background-color: #EBF5FF;
            color: #0D6EFD;
            border: none;
            height: 32px;
            padding: 0 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.2s;
            font-size: 13px;
            font-weight: 500;
        }

        .btn-light-primary:hover {
            background-color: #0D6EFD;
            color: #fff;
        }

        .btn-light-primary i {
            font-size: 12px;
            opacity: 0.8;
        }

        .dropdown-menu {
            padding: 8px 0;
            min-width: 160px;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-radius: 8px;
        }

        .dropdown-item {
            padding: 8px 16px;
            font-size: 14px;
            color: #333;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .dropdown-item.text-danger:hover {
            background-color: #fff5f5;
        }

        .btn-light {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #333;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 16px;
            height: 36px;
            display: inline-flex;
            align-items: center;
        }

        .btn-light:hover:not(.disabled) {
            background-color: #e9ecef;
            border-color: #dee2e6;
            color: #333;
        }

        .btn-light.disabled {
            background-color: #e9ecef;
            border-color: #dee2e6;
            color: #9ca3af;
            opacity: 0.65;
            pointer-events: none;
            cursor: not-allowed;
        }

        .fs-13 {
            font-size: 13px;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .dropdown-item.text-muted {
            color: #9ca3af !important;
            background-color: #f9fafb;
            opacity: 0.8;
        }

        .dropdown-item.text-muted:hover {
            background-color: #f9fafb;
        }

        .badge.bg-light {
            background-color: #f8f9fa !important;
            border: 1px solid #dee2e6;
            font-weight: 500;
            font-size: 13px;
            padding: 4px 8px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchUsers');
            const resetButton = document.getElementById('resetSearch');
            const rows = document.querySelectorAll('.user-row');
            const noResultsRow = document.getElementById('noResultsRow');
            const paginationContainer = document.querySelector('.d-flex.justify-content-between');

            function performSearch() {
                const searchText = searchInput.value.toLowerCase();
                let visibleRows = 0;

                rows.forEach(row => {
                    const name = row.querySelector('td:first-child').textContent.toLowerCase();
                    const email = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

                    if (name.includes(searchText) || email.includes(searchText)) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show/hide no results message and pagination
                if (visibleRows === 0) {
                    noResultsRow.classList.remove('d-none');
                    paginationContainer.style.display = 'none';
                } else {
                    noResultsRow.classList.add('d-none');
                    paginationContainer.style.display = 'flex';
                }

                // Show/hide reset button
                if (searchText.length > 0) {
                    resetButton.classList.remove('d-none');
                } else {
                    resetButton.classList.add('d-none');
                }
            }

            // Search input event
            searchInput.addEventListener('input', performSearch);

            // Reset button click
            resetButton.addEventListener('click', function() {
                searchInput.value = '';
                performSearch();
                searchInput.focus();
            });
        });
    </script>

    @foreach($users as $user)
        <form id="delete-form-{{ $user->id }}" 
              action="{{ route('user_delete') }}" 
              method="POST" 
              style="display: none;">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
        </form>
    @endforeach
@endsection
