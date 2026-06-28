<div class="card border-0 shadow-sm">

    <div class="card-header border-bottom py-3">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h5 class="mb-1 fw-bold">
                    Elite V2 Membership
                </h5>

                <small class="text-body-secondary">
                    Configure Elite V2 membership fees, daily rewards and referral bonuses.
                </small>
            </div>

            @if($settings['eliteV2']->status)
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

        <form action="{{ route('admin.settings.elite-v2') }}" method="POST">

            @csrf

            {{-- General Settings --}}
            <div class="row g-4">

                <div class="col-xl-4 col-lg-4 col-md-6">

                    <label class="form-label fw-semibold">
                        Membership Fee
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">$</span>

                        <input
                            type="number"
                            class="form-control"
                            name="mem_fee"
                            value="{{ old('mem_fee', $settings['eliteV2']->mem_fee) }}">

                    </div>

                </div>

                <div class="col-xl-4 col-lg-4 col-md-6">

                    <label class="form-label fw-semibold">
                        Duration
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            class="form-control"
                            name="duration"
                            value="{{ old('duration', $settings['eliteV2']->duration) }}">

                        <span class="input-group-text">
                            Days
                        </span>

                    </div>

                </div>

                <div class="col-xl-4 col-lg-4 col-md-6">

                    <label class="form-label fw-semibold">
                        Daily Bonus
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.001"
                            class="form-control"
                            name="daily_bonus"
                            value="{{ old('daily_bonus', $settings['eliteV2']->daily_bonus) }}">

                        <span class="input-group-text">
                            %
                        </span>

                    </div>

                </div>

            </div>


            <hr class="my-4">


            <h6 class="fw-bold mb-3">

                <i class="fas fa-sitemap text-info me-2"></i>

                Referral Bonus

            </h6>


            <div class="row g-4">

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Sponsor Bonus
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="sponsor_bonus"
                            value="{{ old('sponsor_bonus', $settings['eliteV2']->sponsor_bonus) }}">

                        <span class="input-group-text">
                            %
                        </span>

                    </div>

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Level 1 Bonus
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            class="form-control"
                            name="lvl1"
                            value="{{ old('lvl1', $settings['eliteV2']->lvl1) }}">

                        <span class="input-group-text">
                            %
                        </span>

                    </div>

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Level 2 Bonus
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            class="form-control"
                            name="lvl2"
                            value="{{ old('lvl2', $settings['eliteV2']->lvl2) }}">

                        <span class="input-group-text">
                            %
                        </span>

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

                        <option value="1" @selected($settings['eliteV2']->status == 1)>
                            Active
                        </option>

                        <option value="0" @selected($settings['eliteV2']->status == 0)>
                            Inactive
                        </option>

                    </select>

                </div>

                <div class="col-md-9 text-end">

                    <button class="btn btn-info text-white px-5">

                        <i class="fas fa-save me-2"></i>

                        Save Changes

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>
