/**
 * San Modesto Vet Clinic - Unified Modal Engine
 * Handles opening, closing, animations, and dynamic data-binding for CRUD modals
 */

document.addEventListener('DOMContentLoaded', () => {
  // Event delegation for opening modals via data-modal-target
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-modal-target]');
    if (trigger) {
      // If click was on the logout form or button inside the sidebar, don't open modal
      if (e.target.closest('.btn-logout') || e.target.closest('form[action*="logout"]')) {
        return;
      }
      e.preventDefault();
      const modalId = trigger.getAttribute('data-modal-target');
      openModal(modalId, trigger);
      return;
    }

    // Event delegation for closing modals via data-modal-close
    const closeBtn = e.target.closest('[data-modal-close]');
    if (closeBtn) {
      e.preventDefault();
      const modal = closeBtn.closest('.modal-backdrop');
      if (modal) {
        closeModal(modal.id);
      }
      return;
    }

    // Close when clicking directly on the backdrop
    if (e.target.classList.contains('modal-backdrop')) {
      closeModal(e.target.id);
    }
  });

  // Close active modal on ESC key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const activeModal = document.querySelector('.modal-backdrop.active');
      if (activeModal) {
        closeModal(activeModal.id);
      }
    }
  });
});

/**
 * Open modal and optionally populate dynamic values from trigger dataset
 */
function openModal(modalId, triggerElement = null) {
  const modal = document.getElementById(modalId);
  if (!modal) return;

  // If trigger passed with data attributes, populate modal inputs
  if (triggerElement) {
    // Dynamic Form Action URL (e.g. for Edit or Delete forms)
    const actionUrl = triggerElement.getAttribute('data-action-url');
    if (actionUrl) {
      const form = modal.querySelector('form');
      if (form) {
        form.action = actionUrl;
      }
    }

    // Populate input fields matching data-field-*
    const attributes = triggerElement.dataset;
    for (const key in attributes) {
      if (key.startsWith('field')) {
        const fieldName = key.replace('field', '').toLowerCase();
        const value = attributes[key];

        // Find input, select, or textarea
        const targetInput = modal.querySelector(`[name="${fieldName}"]`) || 
                            modal.querySelector(`#${fieldName}`) ||
                            modal.querySelector(`[data-bind="${fieldName}"]`);

        if (targetInput) {
          if (targetInput.tagName === 'INPUT' || targetInput.tagName === 'TEXTAREA' || targetInput.tagName === 'SELECT') {
            targetInput.value = value;
          } else {
            targetInput.textContent = value;
          }
        }
      }
    }
  }

  modal.classList.add('active');
  document.body.style.overflow = 'hidden'; // Prevent page scroll
  modal.dispatchEvent(new CustomEvent('modal:opened', { bubbles: true, detail: { modal, trigger: triggerElement } }));

  // Initialize Select2 if present in modal
  if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
    setTimeout(function() {
      $(modal).find('.select2-searchable').each(function() {
        const $el = $(this);
        if (!$el.hasClass('select2-hidden-accessible')) {
          $el.select2({
            dropdownParent: $(modal),
            width: '100%',
            placeholder: $el.attr('data-placeholder') || $el.attr('placeholder') || '-- Choose Client / Owner --',
            allowClear: true
          });
        }
      });
    }, 20);
  }
}

/**
 * Close modal
 */
function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;

  modal.classList.remove('active');
  
  // Check if any other modal is still active
  const remaining = document.querySelectorAll('.modal-backdrop.active');
  if (remaining.length === 0) {
    document.body.style.overflow = '';
  }
}

// Global exposure
window.openModal = openModal;
window.closeModal = closeModal;
