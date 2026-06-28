<div class="card border-0 shadow-sm">

    <div class="card-header border-bottom py-3">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h5 class="mb-1 fw-bold">
                    MKIDS Staking
                </h5>

                <small class="text-body-secondary">
                    Configure staking amount, token reward and referral bonus settings.
                </small>
            </div>

            @if($settings['mkids']->status)
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

        <form action="{{ route('admin.settings.mkids') }}" method="POST">

            @csrf

            {{-- General Settings --}}
            <h6 class="fw-bold mb-3">
                <i class="fas fa-coins text-danger me-2"></i>
                Staking Configuration
            </h6>

            <div class="row g-4">

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Staking Amount
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">$</span>

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="amount"
                            value="{{ old('amount', $settings['mkids']->amount) }}">

                    </div>

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Token Bonus
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="token_bonus"
                            value="{{ old('token_bonus', $settings['mkids']->token_bonus) }}">

                        <span class="input-group-text">%</span>

                    </div>

                </div>

            </div>

            <hr class="my-4">

            {{-- Referral Bonus --}}
            <h6 class="fw-bold mb-3">

                <i class="fas fa-users text-primary me-2"></i>

                Referral Bonus

            </h6>

            <div class="row g-4">

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Level 1 Bonus
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="level_1_bonus"
                            value="{{ old('level_1_bonus', $settings['mkids']->level_1_bonus) }}">

                        <span class="input-group-text">%</span>

                    </div>

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Level 2 Bonus
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="level_2_bonus"
                            value="{{ old('level_2_bonus', $settings['mkids']->level_2_bonus) }}">

                        <span class="input-group-text">%</span>

                    </div>

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Level 3 Bonus
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="level_3_bonus"
                            value="{{ old('level_3_bonus', $settings['mkids']->level_3_bonus) }}">

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

                        <option value="1" @selected($settings['mkids']->status)>
                            Active
                        </option>

                        <option value="0" @selected(!$settings['mkids']->status)>
                            Inactive
                        </option>

                    </select>

                </div>

                <div class="col-md-9 text-end">

                    <button class="btn btn-danger px-5">

                        <i class="fas fa-save me-2"></i>

                        Save Changes

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>
