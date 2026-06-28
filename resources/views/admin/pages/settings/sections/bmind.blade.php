<div class="card border-0 shadow-sm">

    <div class="card-header border-bottom py-3">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h5 class="mb-1 fw-bold">
                    BMIND Staking
                </h5>

                <small class="text-body-secondary">
                    Configure staking limits, APY, multi-level affiliate bonus and seller bonus.
                </small>
            </div>

            @if($settings['bmind']->status)
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

        <form action="{{ route('admin.settings.bmind') }}" method="POST">

            @csrf

            {{-- Staking Limits --}}
            <h6 class="fw-bold mb-3">
                <i class="fas fa-wallet text-success me-2"></i>
                Staking Limits
            </h6>

            <div class="row g-4">

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Minimum Staking
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">$</span>

                        <input
                            type="number"
                            step="0.00000001"
                            class="form-control"
                            name="min_staking"
                            value="{{ old('min_staking', number_format($settings['bmind']->min_staking, 2, '.', '')) }}">

                    </div>

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Maximum Staking
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">$</span>

                        <input
                            type="number"
                            step="0.00000001"
                            class="form-control"
                            name="max_staking"
                            value="{{ old('max_staking', number_format($settings['bmind']->max_staking, 2, '.', '')) }}">

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

                @foreach([180,365,730,1825] as $day)

                    <div class="col-lg-3 col-md-6">

                        <label class="form-label fw-semibold">
                            {{ $day }} Days
                        </label>

                        <div class="input-group">

                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                name="days_{{ $day }}"
                                value="{{ old("days_$day", $settings['bmind']->{"days_$day"}) }}">

                            <span class="input-group-text">%</span>

                        </div>

                    </div>

                @endforeach

            </div>

            <hr class="my-4">

            {{-- Level 1 Affiliate --}}
            <h6 class="fw-bold mb-3">
                <i class="fas fa-users text-primary me-2"></i>
                Level 1 Affiliate Bonus
            </h6>

            <div class="row g-4">

                @foreach([180,365,730,1825] as $day)

                    <div class="col-lg-3 col-md-6">

                        <label class="form-label fw-semibold">
                            {{ $day }} Days
                        </label>

                        <div class="input-group">

                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                name="days_{{ $day }}_af"
                                value="{{ old("days_{$day}_af", $settings['bmind']->{"days_{$day}_af"}) }}">

                            <span class="input-group-text">%</span>

                        </div>

                    </div>

                @endforeach

            </div>

            <hr class="my-4">

            {{-- Level 2 Affiliate --}}
            <h6 class="fw-bold mb-3">
                <i class="fas fa-users text-info me-2"></i>
                Level 2 Affiliate Bonus
            </h6>

            <div class="row g-4">

                @foreach([180,365,730,1825] as $day)

                    <div class="col-lg-3 col-md-6">

                        <label class="form-label fw-semibold">
                            {{ $day }} Days
                        </label>

                        <div class="input-group">

                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                name="days_{{ $day }}_af2"
                                value="{{ old("days_{$day}_af2", $settings['bmind']->{"days_{$day}_af2"}) }}">

                            <span class="input-group-text">%</span>

                        </div>

                    </div>

                @endforeach

            </div>

            <hr class="my-4">

            {{-- Level 3 Affiliate --}}
            <h6 class="fw-bold mb-3">
                <i class="fas fa-users text-warning me-2"></i>
                Level 3 Affiliate Bonus
            </h6>

            <div class="row g-4">

                @foreach([180,365,730,1825] as $day)

                    <div class="col-lg-3 col-md-6">

                        <label class="form-label fw-semibold">
                            {{ $day }} Days
                        </label>

                        <div class="input-group">

                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                name="days_{{ $day }}_af3"
                                value="{{ old("days_{$day}_af3", $settings['bmind']->{"days_{$day}_af3"}) }}">

                            <span class="input-group-text">%</span>

                        </div>

                    </div>

                @endforeach

            </div>

            <hr class="my-4">

            {{-- Seller Bonus --}}
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
                            value="{{ old('seller_bonus', $settings['bmind']->seller_bonus) }}">

                        <span class="input-group-text">%</span>

                    </div>

                </div>

            </div>

            <hr class="my-4">

            <div class="row align-items-end">

                <div class="col-md-3">

                    <label class="form-label fw-semibold">
                        Status
                    </label>

                    <select class="form-select" name="status">

                        <option value="1" @selected($settings['bmind']->status)>
                            Active
                        </option>

                        <option value="0" @selected(!$settings['bmind']->status)>
                            Inactive
                        </option>

                    </select>

                </div>

                <div class="col-md-9 text-end">

                    <button class="btn btn-dark px-5">

                        <i class="fas fa-save me-2"></i>

                        Save Changes

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>
