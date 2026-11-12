// Lomoland Interactive Scripts

document.addEventListener('DOMContentLoaded', function () {
  // 1. Password Visibility Toggle
  const togglePassBtns = document.querySelectorAll('.toggle-password-btn');
  togglePassBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const targetId = this.getAttribute('data-target') || 'password';
      const input = document.getElementById(targetId);
      const iconEye = this.querySelector('.icon-eye');
      const iconEyeOff = this.querySelector('.icon-eye-off');
      
      if (input) {
        if (input.type === 'password') {
          input.type = 'text';
          if (iconEye && iconEyeOff) {
            iconEye.style.display = 'none';
            iconEyeOff.style.display = 'block';
          }
        } else {
          input.type = 'password';
          if (iconEye && iconEyeOff) {
            iconEye.style.display = 'block';
            iconEyeOff.style.display = 'none';
          }
        }
      }
    });
  });

  // 2. File Upload Label Dynamic Update
  const fileInputs = document.querySelectorAll('input[type="file"]');
  fileInputs.forEach(input => {
    input.addEventListener('change', function () {
      const container = this.closest('.file-upload-styled');
      const textSpan = container ? container.querySelector('.file-upload-text') : null;
      if (textSpan && this.files && this.files.length > 0) {
        textSpan.textContent = this.files[0].name;
      }
    });
  });

  // 3. Admin Price Input Auto-formatter (contoh: 7.000.000)
  const priceInput = document.querySelector('.price-input-field');
  if (priceInput) {
    priceInput.addEventListener('input', function () {
      let rawVal = this.value.replace(/\D/g, '');
      if (rawVal) {
        this.value = parseInt(rawVal, 10).toLocaleString('id-ID');
      } else {
        this.value = '';
      }
    });
  }
});

