<div class="card border-0 shadow-sm">

    <div class="card-header border-bottom py-3">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h5 class="mb-1 fw-bold">
                    MIND / USDT Liquidity Pool
                </h5>

                <small class="text-body-secondary">
                    Configure minimum contribution, reward percentage and lock period.
                </small>

            </div>

            @if($settings['liquidity'] && $settings['liquidity']->status)

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

        <form
            action="{{ route('admin.settings.liquidity') }}"
            method="POST">

            @csrf


            {{-- Contribution Settings --}}
            <h6 class="fw-bold mb-3">

                <i class="fas fa-wallet text-success me-2"></i>

                Contribution Settings

            </h6>


            <div class="row g-4">

                {{-- Minimum Contribution --}}
                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        Minimum Contribution

                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            $
                        </span>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control"
                            name="min_amount"
                            value="{{ old(
                                'min_amount',
                                number_format($settings['liquidity']->min_amount ?? 50, 2, '.', '')
                            ) }}"
                            required>

                    </div>

                </div>


                {{-- Reward Percentage --}}
                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        Reward Percentage

                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control"
                            name="reward_percentage"
                            value="{{ old(
                                'reward_percentage',
                                $settings['liquidity']->reward_percentage ?? 100
                            ) }}"
                            required>

                        <span class="input-group-text">
                            %
                        </span>

                    </div>

                </div>

            </div>


            <hr class="my-4">


            {{-- Lock Period --}}
            <h6 class="fw-bold mb-3">

                <i class="fas fa-lock text-warning me-2"></i>

                Lock Period

            </h6>


            <div class="row g-4">

                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        Lock Days

                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            min="1"
                            class="form-control"
                            name="lock_days"
                            value="{{ old(
                                'lock_days',
                                $settings['liquidity']->lock_days ?? 730
                            ) }}"
                            required>

                        <span class="input-group-text">
                            Days
                        </span>

                    </div>

                </div>


                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        Status

                    </label>

                    <select
                        class="form-select"
                        name="status">

                        <option
                            value="1"
                            @selected(
                                old(
                                    'status',
                                    $settings['liquidity']->status ?? 1
                                ) == 1
                            )>

                            Active

                        </option>

                        <option
                            value="0"
                            @selected(
                                old(
                                    'status',
                                    $settings['liquidity']->status ?? 1
                                ) == 0
                            )>

                            Inactive

                        </option>

                    </select>

                </div>

            </div>


            <hr class="my-4">


            {{-- Status --}}
            <div class="row align-items-end">




                <div class="col-md-12 text-end">

                    <button
                        type="submit"
                        class="btn btn-secondary px-5">

                        <i class="fas fa-save me-2"></i>

                        Save Changes

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>
