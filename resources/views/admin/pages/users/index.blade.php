@extends('admin.layouts.app')

@section('title', 'User Management')

@section('content')

<div class="row">
    <div class="col-12">

        <div class="card mb-4">

            <div class="card-header fw-bold">
                User Management
            </div>

            <div class="card-body">

                <form method="GET" action="{{ route('admin.users.index') }}">
                    <div class="mb-3 d-flex gap-2" style="max-width: 650px;">

                        <input type="search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by email or username..." >

                        <select  class="form-select" style="width:180px;" name="status" >
                            <option value="">All Status</option>

                            <option value="active"  {{ request('status') == 'active' ? 'selected' : '' }} >
                                Active
                            </option>

                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }} >
                                Inactive
                            </option>
                        </select>

                        <button type="submit" class="btn btn-primary">
                            Search
                        </button>

                        @if(request()->hasAny(['search','status']))
                            <a  href="{{ route('admin.users.index') }}" class="btn btn-secondary" >
                                Reset
                            </a>
                        @endif

                    </div>
                </form>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead>
                            <tr>
                                <th width="70">#</th>
                                <th>User</th>
                                <th>Status</th>
                                <th>KYC</th>
                                <th>Joined At</th>
                                <th width="180" class="text-center">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                        @forelse($users as $user)

                            <tr>

                                <td>
                                    {{ $users->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-md rounded-circle overflow-hidden" style="width:40px;height:40px;" >

                                            @if(!empty($user->image))

                                                <img src="{{ asset($user->image) }}" alt="{{ $user->user_name }}" class="w-100 h-100" style="object-fit:cover;" >

                                            @else

                                                <div class="bg-primary text-white fw-bold d-flex align-items-center justify-content-center w-100 h-100" >
                                                    {{ strtoupper(substr($user->user_name, 0, 1)) }}
                                                </div>

                                            @endif

                                        </div>
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $user->user_name ?: 'N/A' }}
                                            </div>
                                            <div class="small text-muted">
                                                {{ $user->email }}
                                            </div>
                                        </div>

                                    </div>
                                </td>

                                <td>
                                    @if($user->status)
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($user->kyc)
                                        <span class="badge bg-success">
                                            Verified
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <td class="text-nowrap">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>

                                <td class="text-center">

                                    <div class="d-flex gap-2 justify-content-center">

                                        <a
                                            href="{{ route('admin.users.show', $user->id) }}"
                                            class="btn btn-sm btn-outline-info"
                                        >
                                            View
                                        </a>

                                        <a
                                            href=""
                                            class="btn btn-sm btn-outline-warning"
                                        >
                                            <i class="cil-pen"></i>
                                        </a>

                                        <div class="dropdown">

                                            <button
                                                class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                type="button"
                                                data-coreui-toggle="dropdown"
                                            >
                                                More
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">

                                                <li>
                                                    <a
                                                        class="dropdown-item"
                                                        href="#"
                                                    >
                                                        View Staking
                                                    </a>
                                                </li>

                                                <li>
                                                    <a
                                                        class="dropdown-item"
                                                        href="#"
                                                    >
                                                        View Transaction
                                                    </a>
                                                </li>

                                                <li>
                                                    <a
                                                        class="dropdown-item"
                                                        href="#"
                                                    >
                                                        Wallet History
                                                    </a>
                                                </li>

                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>

                                                <li>
                                                    <a
                                                        class="dropdown-item"
                                                        href="#"
                                                    >
                                                        Referral Tree
                                                    </a>
                                                </li>

                                            </ul>

                                        </div>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    No users found.
                                </td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

                {{ $users->links('admin.components.pagination') }}

            </div>

        </div>

    </div>
</div>

@endsection
