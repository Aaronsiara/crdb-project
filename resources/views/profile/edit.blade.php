@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<h1 class="h4 mb-3">Edit Profile</h1>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Account Details</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="d-flex align-items-center gap-3 mb-4">
                        @if ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
                                 style="width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid #f2b705;">
                        @else
                            <div style="width:64px; height:64px; border-radius:50%; background:#eef6f1; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:22px; color:#00543C;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <label for="avatar" class="form-label mb-1">Profile Photo</label>
                            <input id="avatar" type="file" name="avatar" accept="image/*"
                                   class="form-control @error('avatar') is-invalid @enderror">
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if ($user->avatarUrl())
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="remove_avatar" value="1" id="remove_avatar" class="form-check-input">
                                    <label for="remove_avatar" class="form-check-label small text-muted">Remove current photo</label>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}"
                               class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="position" class="form-label">Data Department Position</label>
                        <select id="position" name="position" class="form-select @error('position') is-invalid @enderror" required>
                            @foreach ($positions as $position)
                                <option value="{{ $position }}" {{ old('position', $user->position) === $position ? 'selected' : '' }}>
                                    {{ $position }}
                                </option>
                            @endforeach
                        </select>
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <h2 class="h6 mb-3">Change Password <span class="text-muted fw-normal">(optional)</span></h2>

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input id="current_password" type="password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror">
                        <div class="form-text">Required only if you're setting a new password below.</div>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input id="new_password" type="password" name="new_password"
                               class="form-control @error('new_password') is-invalid @enderror">
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input id="new_password_confirmation" type="password" name="new_password_confirmation"
                               class="form-control">
                    </div>

                    <button type="submit" class="btn btn-crdb">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Account Info</div>
            <div class="card-body mb-0">
                <p class="mb-1"><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
                <p class="mb-0"><strong>Member since:</strong> {{ $user->created_at->format('M Y') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
