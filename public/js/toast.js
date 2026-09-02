/**
 * San Modesto Vet Clinic - Toast Notification Engine
 * Displays top-right notifications for Success, Error, and Warning states
 */

class ToastManager {
  constructor() {
    this.container = document.querySelector('.toast-container');
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.className = 'toast-container';
      document.body.appendChild(this.container);
    }
  }

  show(type = 'success', title = '', message = '', duration = 4500) {
    const toast = document.createElement('div');
    toast.className = `toast-alert toast-${type}`;

    let iconSvg = '';
    if (type === 'success') {
      iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>`;
    } else if (type === 'error') {
      iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>`;
    } else {
      iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
    }

    toast.innerHTML = `
      <div class="toast-icon">
        ${iconSvg}
      </div>
      <div class="toast-content">
        <div class="toast-title">${title || (type === 'success' ? 'Success!' : 'Notice')}</div>
        <div class="toast-message">${message}</div>
      </div>
      <button class="toast-close" type="button" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
      </button>
      <div class="toast-progress"></div>
    `;

    this.container.appendChild(toast);

    // Trigger animation in next tick
    setTimeout(() => {
      toast.classList.add('show');
    }, 20);

    // Close button
    toast.querySelector('.toast-close').addEventListener('click', () => {
      this.dismiss(toast);
    });

    // Auto dismiss after duration
    if (duration > 0) {
      setTimeout(() => {
        this.dismiss(toast);
      }, duration);
    }
  }

  dismiss(toast) {
    toast.classList.remove('show');
    toast.classList.add('hide');
    setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 400);
  }
}

// Global instance
window.Toast = new ToastManager();

// Read Laravel session flash messages on page load if rendered as data-attributes
document.addEventListener('DOMContentLoaded', () => {
  const flashSuccess = document.querySelector('meta[name="flash-success"]');
  if (flashSuccess && flashSuccess.content) {
    window.Toast.show('success', 'Success', flashSuccess.content);
  }

  const flashError = document.querySelector('meta[name="flash-error"]');
  if (flashError && flashError.content) {
    window.Toast.show('error', 'Error Occurred', flashError.content);
  }

  const flashWarning = document.querySelector('meta[name="flash-warning"]');
  if (flashWarning && flashWarning.content) {
    window.Toast.show('warning', 'Attention', flashWarning.content);
  }
});
