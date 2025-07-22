@extends('layout.base_layout')

@section('title', 'Squad Settings')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Squad Settings</h1>
                <p class="text-muted">Configure team squads and assign members</p>
            </div>
            <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Settings
            </a>
        </div>
    </div>
    
    <!-- Squad Settings -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Squad Configuration</h5>
                    <span class="badge bg-info">Teams</span>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    
                    <form method="POST" action="{{ route('settings.update_squad') }}">
                        @csrf
                        <div class="mb-4">
                            <h6 class="mb-3">Active Squads</h6>
                            
                            <div class="card mb-3">
                                <div class="card-body pb-0">
                                    <div id="squad-container">
                                        <!-- Squad 1 -->
                                        <div class="squad-item mb-4">
                                            <div class="row g-3 mb-2">
                                                <div class="col-md-5">
                                                    <label class="form-label">Squad Name</label>
                                                    <input type="text" class="form-control" name="squad_name[]" value="Backend Team" placeholder="Enter squad name">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Squad Lead</label>
                                                    <select class="form-select" name="squad_lead[]">
                                                        <option selected disabled>Select Squad Lead</option>
                                                        @foreach(\App\Models\User::all() as $user)
                                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger remove-squad w-100">Remove</button>
                                                </div>
                                            </div>
                                            
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Squad Members</label>
                                                    <select class="form-select squad-members" name="squad_members[0][]" multiple>
                                                        @foreach(\App\Models\User::all() as $user)
                                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <hr class="my-4">
                                        </div>
                                        
                                        <!-- Squad 2 -->
                                        <div class="squad-item mb-4">
                                            <div class="row g-3 mb-2">
                                                <div class="col-md-5">
                                                    <label class="form-label">Squad Name</label>
                                                    <input type="text" class="form-control" name="squad_name[]" value="Frontend Team" placeholder="Enter squad name">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Squad Lead</label>
                                                    <select class="form-select" name="squad_lead[]">
                                                        <option selected disabled>Select Squad Lead</option>
                                                        @foreach(\App\Models\User::all() as $user)
                                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger remove-squad w-100">Remove</button>
                                                </div>
                                            </div>
                                            
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Squad Members</label>
                                                    <select class="form-select squad-members" name="squad_members[1][]" multiple>
                                                        @foreach(\App\Models\User::all() as $user)
                                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <hr class="my-4">
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mb-3">
                                        <button type="button" id="add-squad" class="btn btn-outline-primary">
                                            <i class="bi bi-plus-circle me-1"></i> Add New Squad
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Squad Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Squad Statistics</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Current squad configuration:</p>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total Squads</span>
                        <span class="badge bg-primary">2</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Team Members</span>
                        <span class="badge bg-primary">{{ \App\Models\User::count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Squad Leads</span>
                        <span class="badge bg-primary">2</span>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Help</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Use these settings to configure your team squads. You can create multiple squads, assign team leads, and add members to each squad.</p>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i> Squads will be used for reporting and performance metrics.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add new squad
        document.getElementById('add-squad').addEventListener('click', function() {
            const squadContainer = document.getElementById('squad-container');
            const squadItems = document.querySelectorAll('.squad-item');
            const newIndex = squadItems.length;
            
            const newSquad = document.createElement('div');
            newSquad.className = 'squad-item mb-4';
            newSquad.innerHTML = `
                <div class="row g-3 mb-2">
                    <div class="col-md-5">
                        <label class="form-label">Squad Name</label>
                        <input type="text" class="form-control" name="squad_name[]" placeholder="Enter squad name">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Squad Lead</label>
                        <select class="form-select" name="squad_lead[]">
                            <option selected disabled>Select Squad Lead</option>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger remove-squad w-100">Remove</button>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Squad Members</label>
                        <select class="form-select squad-members" name="squad_members[${newIndex}][]" multiple>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <hr class="my-4">
            `;
            
            squadContainer.appendChild(newSquad);
            
            // Add event listener to new remove button
            newSquad.querySelector('.remove-squad').addEventListener('click', function() {
                newSquad.remove();
            });
        });
        
        // Remove squad
        document.querySelectorAll('.remove-squad').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.squad-item').remove();
            });
        });
    });
</script>
@endsection 