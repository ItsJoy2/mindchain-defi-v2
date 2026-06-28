@extends('admin.layouts.app')

@section('title', 'Wallet Icons')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="row mb-4">

        <div class="col-12">

            <div class="card border-0 shadow-sm">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <h3 class="mb-1 fw-bold">
                            <i class="fas fa-wallet text-primary me-2"></i>
                            Wallet Icons
                        </h3>

                        <small class="text-body-secondary">
                            Upload and manage wallet icons used throughout the application.
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Success/ error alert --}}
    @include('admin.components.alerts')

    {{-- Card --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header">

            <strong>
                Wallet Icons
            </strong>

        </div>

        <div class="card-body">

            <form
                action="{{ route('admin.settings.wallet-icons.update') }}"
                method="POST"
                enctype="multipart/form-data">

                @csrf

                <div class="row g-4">

                    @foreach($walletIcons as $icon)

                        <div class="col-xl-3 col-lg-4 col-md-6">

                            <div class="card h-100 border">

                                <div class="card-body text-center">

                                    <img
                                        src="{{ asset($icon->value) }}"
                                        class="img-fluid mb-3 border rounded p-3 bg-light"
                                        style="height:90px;width:90px;object-fit:contain;">

                                    <h5 class="fw-bold text-uppercase">
                                        {{ $icon->key }}
                                    </h5>

                                    <small class="text-body-secondary d-block mb-3">
                                        {{ $icon->value }}
                                    </small>

                                    <input
                                        type="file"
                                        class="form-control"
                                        name="icons[{{ $icon->id }}]"
                                        accept=".png,.jpg,.jpeg,.svg,.webp">

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

                <hr class="my-4">

                <div class="text-end">

                    <button
                        class="btn btn-primary px-5">

                        <i class="fas fa-save me-2"></i>

                        Save Changes

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
