<div class="card border-0 shadow-sm">

    <div class="card-header border-bottom py-3">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h5 class="mb-1 fw-bold">
                    Angel Membership
                </h5>

                <small class="text-body-secondary">
                    Configure angel membership fees, APY and referral bonuses.
                </small>
            </div>

            @if($settings['angel']->status)
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

        <form action="{{ route('admin.settings.angel') }}" method="POST">

            @csrf

            <div class="row g-4">

                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label class="form-label fw-semibold">
                        Membership Fee
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">$</span>

                        <input
                            type="number"
                            step="0.00000001"
                            class="form-control"
                            name="membership_fee"
                            value="{{ old('membership_fee', number_format($settings['angel']->membership_fee, 2, '.', '')) }}">
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6">

                    <label class="form-label fw-semibold">
                        Duration
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            class="form-control"
                            name="duration"
                            value="{{ old('duration',$settings['angel']->duration) }}">

                        <span class="input-group-text">
                            Days
                        </span>

                    </div>

                </div>

                <div class="col-xl-3 col-lg-4 col-md-6">

                    <label class="form-label fw-semibold">
                        APY
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="apy"
                            value="{{ old('apy',$settings['angel']->apy) }}">

                        <span class="input-group-text">
                            %
                        </span>

                    </div>

                </div>

                <div class="col-xl-3 col-lg-4 col-md-6">

                    <label class="form-label fw-semibold">
                        Total Member
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        name="total_member"
                        value="{{ old('total_member',$settings['angel']->total_member) }}">

                </div>

            </div>


            <hr class="my-4">


            <h6 class="fw-bold mb-3">

                <i class="fas fa-sitemap text-primary me-2"></i>

                Referral Bonus

            </h6>


            <div class="row g-4">

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Level 1
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="level_1_bonus"
                            value="{{ old('level_1_bonus',$settings['angel']->level_1_bonus) }}">

                        <span class="input-group-text">%</span>

                    </div>

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Level 2
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="level_2_bonus"
                            value="{{ old('level_2_bonus',$settings['angel']->level_2_bonus) }}">

                        <span class="input-group-text">%</span>

                    </div>

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Level 3
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="level_3_bonus"
                            value="{{ old('level_3_bonus',$settings['angel']->level_3_bonus) }}">

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

                        <option value="1" @selected($settings['angel']->status)>
                            Active
                        </option>

                        <option value="0" @selected(!$settings['angel']->status)>
                            Inactive
                        </option>

                    </select>

                </div>

                <div class="col-md-9 text-end">

                    <button class="btn btn-primary px-5">

                        <i class="fas fa-save me-2"></i>

                        Save Changes

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>



