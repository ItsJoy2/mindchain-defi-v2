```blade
{{-- Image Preview Modal --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1"
     aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="imagePreviewModalLabel">
                    Image Preview
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body text-center p-2">
                <img id="previewImage" src="" alt="Image Preview"
                     style="max-width:100%; max-height:75vh; object-fit:contain;">
            </div>
        </div>
    </div>
</div>
```
