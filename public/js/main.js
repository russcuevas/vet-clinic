/**
 * San Modesto Vet Clinic - Main UI Logic
 * Handles mobile sidebar, live table filters, and print triggers
 */

document.addEventListener('DOMContentLoaded', () => {
  // Sidebar Toggle (Supports both Desktop Hide/Collapse and Mobile Drawer)
  const toggleBtn = document.getElementById('sidebarToggleBtn') || document.querySelector('.sidebar-toggle-btn');
  const sidebar = document.querySelector('.app-sidebar');
  const appMain = document.querySelector('.app-main');
  const overlay = document.querySelector('.sidebar-overlay');

  // Check saved state on desktop
  if (window.innerWidth > 1024) {
    const isSavedCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
    if (isSavedCollapsed && sidebar && appMain) {
      sidebar.classList.add('collapsed');
      appMain.classList.add('sidebar-collapsed');
    }
  }

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      if (window.innerWidth <= 1024) {
        // Mobile behavior
        sidebar.classList.toggle('show');
        if (overlay) overlay.classList.toggle('show');
      } else {
        // Desktop behavior: hide/show sidebar
        sidebar.classList.toggle('collapsed');
        if (appMain) appMain.classList.toggle('sidebar-collapsed');
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
      }

      // Smoothly recalculate and adjust all DataTable widths and column layouts
      setTimeout(() => {
        if (window.jQuery && $.fn.DataTable) {
          $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
          $(window).trigger('resize');
        }
      }, 260);
    });
  }

  window.addEventListener('resize', () => {
    if (window.jQuery && $.fn.DataTable) {
      $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    }
  });

  if (overlay && sidebar) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });
  }

  // Automatically Initialize Responsive DataTables on all .data-table
  if (window.jQuery && $.fn.DataTable) {
    $.fn.dataTable.ext.errMode = 'none';
    $('.data-table').each(function() {
      const $table = $(this);
      
      // If table only has an empty row with colspan, empty it so DataTables handles zeroRecords cleanly without _DT_CellIndex errors
      if ($table.find('tbody tr td[colspan]').length) {
        $table.find('tbody').empty();
      }

      if (!$.fn.DataTable.isDataTable(this)) {
        const dt = $table.DataTable({
          responsive: false,
          scrollX: true,
          pageLength: 10,
          lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
          language: {
            search: "",
            searchPlaceholder: "Search table records...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ entries)",
            zeroRecords: "No matching records found",
            paginate: {
              first: "«",
              last: "»",
              next: "›",
              previous: "‹"
            }
          },
          order: [],
          autoWidth: false
        });

        // Check if there is an external custom search input targeting this table
        const tableId = $table.attr('id');
        if (tableId) {
          const $externalSearch = $('[data-table-search="' + tableId + '"]');
          if ($externalSearch.length) {
            $externalSearch.on('input', function() {
              dt.search(this.value).draw();
            });
            // Hide the default search box so there's no duplicate
            $table.closest('.dataTables_wrapper').find('.dataTables_filter').hide();
          }
        }
      }
    });
  }

  // Global helper to initialize Select2
  window.initSelect2Elements = function(context) {
    if (!window.jQuery || typeof jQuery.fn.select2 !== 'function') return;
    const $ctx = context ? $(context) : $(document);
    $ctx.find('.select2-searchable').each(function() {
      const $el = $(this);
      if (!$el.hasClass('select2-hidden-accessible')) {
        const $modal = $el.closest('.modal-backdrop');
        $el.select2({
          dropdownParent: $modal.length ? $modal : $(document.body),
          width: '100%',
          placeholder: $el.attr('data-placeholder') || $el.attr('placeholder') || '-- Search by Name, Code, or Contact --',
          allowClear: true
        });
      }
    });
  };

  // Run on DOM ready
  initSelect2Elements();

  // Run when clicking any modal trigger
  $(document).on('click', '[data-modal-target]', function() {
    const target = $(this).attr('data-modal-target');
    setTimeout(function() {
      initSelect2Elements('#' + target);
    }, 40);
  });

  // Also listen for modal:opened custom event
  $(document).on('modal:opened', function(e) {
    const modal = e.detail ? e.detail.modal : this;
    if (modal) {
      initSelect2Elements(modal);
    }
  });

  // Client Type Switcher (Existing / Old vs New Client Registration)
  $(document).on('change', '.client-type-radio', function() {
    const $form = $(this).closest('form');
    const selectedType = $(this).val();

    // Toggle button visual state
    $form.find('.client-toggle-btn').css({
      'background': 'transparent',
      'color': 'var(--text-secondary)'
    });
    $(this).siblings('.client-toggle-btn').css({
      'background': 'linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-dark) 100%)',
      'color': '#040609'
    });

    if (selectedType === 'new') {
      $form.find('.section-existing-client').slideUp(200);
      $form.find('.section-new-client').slideDown(200);

      // Adjust required attributes
      $form.find('.section-new-client [data-required]').prop('required', true);
      $form.find('.section-existing-client select').prop('required', false);
    } else {
      $form.find('.section-new-client').slideUp(200);
      $form.find('.section-existing-client').slideDown(200);

      // Adjust required attributes
      $form.find('.section-new-client [data-required]').prop('required', false);
      $form.find('.section-existing-client select[name="owner_id"]').prop('required', true);
      $form.find('.section-existing-client select[name="pet_id"]').prop('required', true);

      // Re-trigger select2 resize
      $form.find('.select2-searchable').trigger('change.select2');
    }
  });

  // Dynamic Pet dropdown filtering when Owner is selected via Select2 / Standard select
  $(document).on('change', 'select[name="owner_id"]', function() {
    const $form = $(this).closest('form');
    const selectedOwnerId = $(this).val();
    const $petSelect = $form.find('select[name="pet_id"]');

    if (!$petSelect.length) return;

    let availableCount = 0;
    $petSelect.find('option').each(function() {
      const ownerId = $(this).attr('data-owner-id');
      if (!ownerId) {
        // Placeholder
        $(this).prop('disabled', false);
      } else if (ownerId === selectedOwnerId) {
        $(this).show().prop('disabled', false);
        availableCount++;
      } else {
        $(this).hide().prop('disabled', true);
      }
    });

    // Reset pet select value
    $petSelect.val('').trigger('change');

    if (selectedOwnerId && availableCount === 0) {
      if (!$petSelect.find('.no-pets-opt').length) {
        $petSelect.append('<option value="" class="no-pets-opt" disabled>⚠️ No registered pets found for this owner</option>');
      }
    } else {
      $petSelect.find('.no-pets-opt').remove();
    }
  });
});

function printSection(elementId) {
  window.print();
}
window.printSection = printSection;
