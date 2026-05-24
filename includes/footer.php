  </div><!-- end page-body -->
</div><!-- end main-content -->

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
    <div class="modal-content p-4 text-center">
      <div style="width:60px;height:60px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
        <i class="fa-solid fa-triangle-exclamation fa-lg" style="color:#dc2626;"></i>
      </div>
      <h5 class="fw-700 mb-1">Confirm Delete</h5>
      <p class="text-muted mb-4" id="deleteModalMsg">Are you sure you want to delete this record?</p>
      <div class="d-flex gap-2 justify-content-center">
        <button class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
        <a href="#" id="deleteConfirmBtn" class="btn btn-danger px-4">
          <i class="fa-solid fa-trash-can me-1"></i> Delete
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Wire up all delete buttons to the shared modal
document.querySelectorAll('[data-delete-url]').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var url  = this.getAttribute('data-delete-url');
    var name = this.getAttribute('data-delete-name');
    document.getElementById('deleteModalMsg').textContent = 'Are you sure you want to delete "' + name + '"? This cannot be undone.';
    document.getElementById('deleteConfirmBtn').href = url;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
  });
});

// Auto-dismiss alerts after 4s
setTimeout(function() {
  document.querySelectorAll('.alert-dismissible').forEach(function(el) {
    var bsAlert = bootstrap.Alert.getOrCreateInstance(el);
    if (bsAlert) bsAlert.close();
  });
}, 4000);
</script>
</body>
</html>
