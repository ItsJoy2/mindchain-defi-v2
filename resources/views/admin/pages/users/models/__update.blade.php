<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">

        <form action="{{ route('admin.users.update', $user->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Update User
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-coreui-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Username
                            </label>

                            <input
                                type="text"
                                name="user_name"
                                class="form-control"
                                value="{{ $user->user_name }}"
                                required
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="{{ $user->email }}"
                                required
                            >
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">
                                Sponsor
                            </label>

                            <select name="sponsor_id" id="sponsorSelect" class="form-select bg-transparent" style="width: 100%;">
                                @if($user->sponsor)
                                    <option value="{{ $user->sponsor->id }}" selected>
                                        {{ $user->sponsor->user_name }}
                                        ({{ $user->sponsor->email }})
                                    </option>
                                @else
                                    <option value="" selected>
                                        No Sponsor
                                    </option>
                                @endif
                            </select>

                            <small class="text-body-secondary">
                                Search by username or email
                            </small>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-coreui-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Changes
                    </button>

                </div>

            </div>

        </form>

    </div>
</div>
