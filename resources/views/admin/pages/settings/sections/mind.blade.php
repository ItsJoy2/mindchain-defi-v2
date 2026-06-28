<div class="card border-0 shadow-sm">

    <div class="card-header border-bottom py-3">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h5 class="mb-1 fw-bold">
                    MIND Staking
                </h5>

                <small class="text-body-secondary">
                    Configure staking limits, APY, affiliate bonus and seller bonus.
                </small>
            </div>

            @if($settings['mind']->status)
                <span class="badge bg-success">
                    Active Settings
                </span>
            @else
                <span class="badge bg-danger">
                    Inactive Settings
                </span>
            @endif
        </div>

    </div>

    <div class="card-body">

        <form action="{{ route('admin.settings.mind') }}" method="POST">

            @csrf

            {{-- Staking Limits --}}
            <h6 class="fw-bold mb-3">
                <i class="fas fa-wallet text-success me-2"></i>
                Staking Limits
            </h6>

            <div class="row g-4">

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Minimum Staking</label>

                    <div class="input-group">
                        <span class="input-group-text">$</span>

                        <input
                            type="number"
                            step="0.00000001"
                            class="form-control"
                            name="min_staking"
                            value="{{ old('min_staking', number_format($settings['mind']->min_staking, 2, '.', '')) }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Maximum Staking</label>

                    <div class="input-group">
                        <span class="input-group-text">$</span>

                        <input
                            type="number"
                            step="0.00000001"
                            class="form-control"
                            name="max_staking"
                            value="{{ old('max_staking',  number_format( $settings['mind']->max_staking, 2, '.', '')) }}">
                    </div>
                </div>

            </div>

            <hr class="my-4">

            {{-- APY --}}
            <h6 class="fw-bold mb-3">
                <i class="fas fa-chart-line text-success me-2"></i>
                APY (%)
            </h6>

            <div class="row g-4">

                @foreach([90,180,365,730,1825] as $day)

                    <div class="col-lg col-md-4">

                        <label class="form-label fw-semibold">{{ $day }} Days</label>

                        <div class="input-group">

                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                name="days_{{ $day }}"
                                value="{{ old("days_$day", number_format( $settings['mind']->{"days_$day"}, 2, '.', '')) }}">

                            <span class="input-group-text">%</span>

                        </div>

                    </div>

                @endforeach

            </div>

            <hr class="my-4">

            {{-- Affiliate Bonus --}}
            <h6 class="fw-bold mb-3">
                <i class="fas fa-users text-primary me-2"></i>
                Affiliate Bonus
            </h6>

            <div class="row g-4">

                @foreach([90,180,365,730,1825] as $day)

                    <div class="col-lg col-md-4">

                        <label class="form-label fw-semibold">{{ $day }} Days</label>

                        <div class="input-group">

                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                name="days_{{ $day }}_af"
                                value="{{ old("days_{$day}_af", number_format($settings['mind']->{"days_{$day}_af"}, 2, '.', '')) }}">

                            <span class="input-group-text">%</span>

                        </div>

                    </div>

                @endforeach

            </div>

            <hr class="my-4">

            {{-- Seller Bonus --}}
            <h6 class="fw-bold mb-3">
                <i class="fas fa-store text-warning me-2"></i>
                Seller Bonus
            </h6>

            <div class="row g-4">

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Seller Bonus
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="seller_bonus"
                            value="{{ old('seller_bonus', number_format($settings['mind']->seller_bonus, 2, '.', '')) }}">

                        <span class="input-group-text">%</span>

                    </div>

                </div>

            </div>

            <hr class="my-4">

            {{-- Status --}}
            <div class="row align-items-end">

                <div class="col-md-3">

                    <label class="form-label fw-semibold">
                        Status
                    </label>

                    <select class="form-select" name="status">

                        <option value="1" @selected($settings['mind']->status)>
                            Active
                        </option>

                        <option value="0" @selected(!$settings['mind']->status)>
                            Inactive
                        </option>

                    </select>

                </div>

                <div class="col-md-9 text-end">

                    <button class="btn btn-success px-5">

                        <i class="fas fa-save me-2"></i>

                        Save Changes

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>
