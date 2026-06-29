<!-- View MKids Modal -->
<div class="modal fade"
     id="viewMkidsModal{{ $investment->id }}"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    <i class="fas fa-child me-2"></i>
                    MKids Details
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="row">

                    <!-- User -->
                    <div class="col-md-6 mb-3">

                        <div class="card h-100 border">

                            <div class="card-header fw-bold">
                                <i class="fas fa-user me-2 text-primary"></i>
                                User Information
                            </div>

                            <div class="card-body">

                                <table class="table table-sm table-borderless mb-0">

                                    <tr>
                                        <th width="40%">Username</th>
                                        <td>{{ $investment->user->user_name ?? '-' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $investment->user->email ?? '-' }}</td>
                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>

                    <!-- Kid -->
                    <div class="col-md-6 mb-3">

                        <div class="card h-100 border">

                            <div class="card-header fw-bold">
                                <i class="fas fa-child me-2 text-success"></i>
                                Kid Information
                            </div>

                            <div class="card-body">

                                <table class="table table-sm table-borderless mb-0">

                                    <tr>
                                        <th width="40%">Name</th>
                                        <td>{{ $investment->kids_name }}</td>
                                    </tr>

                                    <tr>
                                        <th>Username</th>
                                        <td>{{ '@'.$investment->kids_username }}</td>
                                    </tr>

                                    <tr>
                                        <th>Age</th>
                                        <td>{{ $investment->age }} Years</td>
                                    </tr>

                                    <tr>
                                        <th>Date of Birth</th>
                                        <td>{{ optional($investment->dob)->format('d M Y') }}</td>
                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>

                    <!-- Parents -->
                    <div class="col-md-6 mb-3">

                        <div class="card h-100 border">

                            <div class="card-header fw-bold">
                                <i class="fas fa-users me-2 text-warning"></i>
                                Parent Information
                            </div>

                            <div class="card-body">

                                <table class="table table-sm table-borderless mb-0">

                                    <tr>
                                        <th width="40%">Father</th>
                                        <td>{{ $investment->kids_father_name }}</td>
                                    </tr>

                                    <tr>
                                        <th>Mother</th>
                                        <td>{{ $investment->kids_mother_name }}</td>
                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>

                    <!-- Other -->
                    <div class="col-md-6 mb-3">

                        <div class="card h-100 border">

                            <div class="card-header fw-bold">
                                <i class="fas fa-info-circle me-2 text-danger"></i>
                                Other Information
                            </div>

                            <div class="card-body">

                                <table class="table table-sm table-borderless mb-0">

                                    <tr>
                                        <th width="40%">Birth Place</th>
                                        <td>{{ $investment->kids_birth_place }}</td>
                                    </tr>

                                    <tr>
                                        <th>Country</th>
                                        <td>{{ $investment->country }}</td>
                                    </tr>

                                    <tr>
                                        <th>Count</th>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $investment->count }}
                                            </span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Created At</th>
                                        <td>
                                            <span class="local-time"
                                                  data-time="{{ $investment->created_at->toIso8601String() }}">
                                                {{ $investment->created_at }}
                                            </span>
                                        </td>
                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>

            </div>

        </div>

    </div>

</div>
