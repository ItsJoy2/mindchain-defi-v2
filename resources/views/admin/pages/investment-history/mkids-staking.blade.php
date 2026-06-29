@extends('admin.layouts.app')

@section('title', 'MKids Staking History')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

            <h4 class="mb-0">
                MKids Staking History
            </h4>

            <form method="GET"
                  action="{{ route('admin.history.mkids-staking') }}"
                  class="d-flex gap-2">

                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Search User and Kid's"
                       value="{{ request('search') }}">

                <button class="btn btn-primary">
                    Search
                </button>

                <a href="{{ route('admin.history.mkids-staking') }}"
                   class="btn btn-light border">
                    Reset
                </a>

            </form>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead >
                        <tr>
                            <th width="60">#</th>
                            <th>User</th>
                            <th>Kid</th>
                            <th>Age</th>
                            <th>Count</th>
                            <th>Created At</th>
                            <th class="text-center" width="80">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($investments as $key => $investment)

                        <tr>

                            <td>
                                {{ $investments->firstItem() + $key }}
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $investment->user->user_name ?? 'N/A' }}
                                </div>

                                <small class="text-muted">
                                    {{ $investment->user->email ?? '-' }}
                                </small>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $investment->kids_name }}
                                </div>

                                <small class="text-muted">
                                    {{ '@' . $investment->kids_username }}
                                </small>
                            </td>

                            <td>
                                {{ $investment->age }} Years
                            </td>

                            <td>
                                <span class="badge bg-primary">
                                    {{ $investment->count }}
                                </span>
                            </td>

                            <td>
                                <span class="local-time"
                                      data-time="{{ $investment->created_at->toIso8601String() }}">
                                    {{ $investment->created_at }}
                                </span>
                            </td>

                            <td class="text-center">

                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        title="View Details"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewMkidsModal{{ $investment->id }}">

                                    <i class="fas fa-eye"></i>

                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                No MKids Staking History Found.
                            </td>
                        </tr>

                    @endforelse
                    </tbody>

                </table>
                @foreach($investments as $investment)
                    @include('admin.pages.investment-history.sections.mkids-show-modal')
                @endforeach
            </div>

        </div>

        <div class="card-footer">
            {{ $investments->appends(request()->query())->links('admin.components.pagination') }}
        </div>

    </div>

</div>

@endsection
