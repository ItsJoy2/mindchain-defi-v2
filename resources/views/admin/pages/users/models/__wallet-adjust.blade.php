<div class="modal fade" id="walletAdjustModal" tabindex="-1">
    <div class="modal-dialog">

        <form
            action="{{ route('admin.users.wallet.adjust', $user->id) }}"
            method="POST"
        >
            @csrf

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Adjust Wallet Balance
                    </h5>

                    <button type="button" class="btn-close" data-coreui-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="wallet" id="walletName" >

                    <div class="mb-3">
                        <label class="form-label">
                            Wallet
                        </label>

                        <input type="text" id="walletDisplay" class="form-control" readonly >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Action
                        </label>

                        <select  name="action"  class="form-select" required >
                            <option value="add">
                                Add Balance
                            </option>

                            <option value="deduct">
                                Deduct Balance
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Amount
                        </label>

                        <input type="number"  step="0.001"  min="0.001" name="amount" class="form-control" required >
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Submit
                    </button>

                </div>

            </div>

        </form>

    </div>
</div>
