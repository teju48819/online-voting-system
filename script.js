// Common functionality
document.addEventListener("DOMContentLoaded", function () {
  // Mobile menu toggle (if needed)
  const mobileMenuToggle = document.getElementById("mobile-menu-toggle")
  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", function () {
      const nav = document.querySelector("nav")
      nav.classList.toggle("active")
    })
  }

  // Password confirmation validation
  const password = document.getElementById("password")
  const confirmPassword = document.getElementById("confirm_password")

  if (password && confirmPassword) {
    function validatePassword() {
      if (password.value !== confirmPassword.value) {
        confirmPassword.setCustomValidity("Passwords don't match")
      } else {
        confirmPassword.setCustomValidity("")
      }
    }

    password.onchange = validatePassword
    confirmPassword.onkeyup = validatePassword
  }

  // Form submission handling
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      // Add loading state
      const submitButton = this.querySelector('button[type="submit"]')
      if (submitButton) {
        submitButton.disabled = true
        submitButton.innerHTML = '<span class="spinner"></span> Processing...'
      }
    })
  })

  // OTP input auto-tab
  const otpInput = document.getElementById("otp")
  if (otpInput) {
    otpInput.addEventListener("input", function () {
      if (this.value.length === 6) {
        this.form.submit()
      }
    })
  }
})
