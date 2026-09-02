@props(['owners' => []])

<!-- Client Type Segmented Switcher -->
<div class="client-type-switcher-container" style="margin-bottom: 1.25rem;">
    <div style="display: flex; gap: 0.5rem; background: var(--navy-dark); padding: 5px; border-radius: var(--radius-sm); border: 1px solid var(--black-border);">
        <label style="flex: 1; margin: 0; cursor: pointer; text-align: center;">
            <input type="radio" name="client_type" value="existing" checked class="client-type-radio" style="display: none;">
            <span class="client-toggle-btn" style="display: block; padding: 9px 14px; border-radius: 4px; font-weight: 700; font-size: 0.85rem; transition: all 0.2s; background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-dark) 100%); color: #040609;">
                🔍 Existing / Old Client
            </span>
        </label>
        <label style="flex: 1; margin: 0; cursor: pointer; text-align: center;">
            <input type="radio" name="client_type" value="new" class="client-type-radio" style="display: none;">
            <span class="client-toggle-btn" style="display: block; padding: 9px 14px; border-radius: 4px; font-weight: 700; font-size: 0.85rem; transition: all 0.2s; background: transparent; color: var(--text-secondary);">
                ✨ New Client & Pet Registration
            </span>
        </label>
    </div>
</div>

<!-- ================= SECTION 1: EXISTING / OLD CLIENT ================= -->
<div class="section-existing-client">
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Search Client / Owner (Select2) <span class="req">*</span></label>
            <select name="owner_id" class="form-select select2-searchable" style="width: 100%;" required data-placeholder="-- Search by Name, Code, or Contact --">
                <option value="">-- Search by Name, Code, or Contact --</option>
                @foreach($owners as $owner)
                    <option value="{{ $owner->id }}">
                        {{ $owner->client_code }} - {{ $owner->full_name }} ({{ $owner->contact_number }})
                    </option>
                @endforeach
            </select>
            <span style="font-size: 0.72rem; color: var(--text-muted); margin-top: 4px; display: block;">
                💡 Type to search client database. Selecting an owner immediately filters their registered pets.
            </span>
        </div>

        <div class="form-group">
            <label class="form-label">Select Patient / Pet <span class="req">*</span></label>
            <select name="pet_id" class="form-select" required>
                <option value="">-- Choose Pet --</option>
                @foreach($owners as $owner)
                    @foreach($owner->pets as $pet)
                        <option value="{{ $pet->id }}" data-owner-id="{{ $owner->id }}">
                            {{ $pet->pet_code }} - {{ $pet->name }} ({{ $pet->species }}{{ $pet->breed ? ', ' . $pet->breed : '' }})
                        </option>
                    @endforeach
                @endforeach
            </select>
        </div>
    </div>
</div>

<!-- ================= SECTION 2: NEW CLIENT & PET REGISTRATION ================= -->
<div class="section-new-client" style="display: none; background: rgba(11, 25, 44, 0.4); border: 1px solid var(--gold-border); border-radius: var(--radius-sm); padding: 1.25rem; margin-bottom: 1.25rem;">
    <!-- Subheader: Owner Details -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.85rem; border-bottom: 1px solid var(--black-border); padding-bottom: 0.5rem;">
        <span style="font-size: 1.1rem;">👤</span>
        <h5 style="color: var(--gold-primary); font-size: 0.88rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
            New Client / Owner Details
        </h5>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Full Name <span class="req">*</span></label>
            <input type="text" name="owner_name" class="form-control" data-required placeholder="e.g. Maria Clara Santos">
        </div>
        <div class="form-group">
            <label class="form-label">Contact Number <span class="req">*</span></label>
            <input type="text" name="contact_number" class="form-control" data-required placeholder="e.g. 0917-123-4567">
        </div>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Complete Address <span class="req">*</span></label>
            <input type="text" name="address" class="form-control" data-required placeholder="e.g. 124 Rizal St, San Modesto">
        </div>
        <div class="form-group">
            <label class="form-label">Email Address <span style="color: var(--text-muted); font-size: 0.72rem;">(Optional)</span></label>
            <input type="email" name="email" class="form-control" placeholder="e.g. owner@example.com">
        </div>
    </div>

    <!-- Subheader: Pet Details -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin: 1rem 0 0.85rem 0; border-bottom: 1px solid var(--black-border); padding-bottom: 0.5rem;">
        <span style="font-size: 1.1rem;">🐾</span>
        <h5 style="color: var(--gold-primary); font-size: 0.88rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">
            New Pet Information
        </h5>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Pet Name <span class="req">*</span></label>
            <input type="text" name="pet_name" class="form-control" data-required placeholder="e.g. Browny, Luna, Max">
        </div>
        <div class="form-group">
            <label class="form-label">Species <span class="req">*</span></label>
            <select name="species" class="form-select" data-required>
                <option value="">-- Select Species --</option>
                <option value="Canine (Dog)">Canine (Dog)</option>
                <option value="Feline (Cat)">Feline (Cat)</option>
                <option value="Avian (Bird)">Avian (Bird)</option>
                <option value="Rabbit">Rabbit</option>
                <option value="Hamster/Rodent">Hamster / Small Pet</option>
                <option value="Reptile">Reptile</option>
                <option value="Other">Other</option>
            </select>
        </div>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Breed</label>
            <input type="text" name="breed" class="form-control" placeholder="e.g. Golden Retriever, Shih Tzu, Persian">
        </div>
        <div class="form-group">
            <label class="form-label">Age</label>
            <input type="text" name="age" class="form-control" placeholder="e.g. 2 yrs, 6 mos">
        </div>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Sex</label>
            <select name="sex" class="form-select">
                <option value="">-- Choose Sex --</option>
                <option value="Male">Male (Intact)</option>
                <option value="Female">Female (Intact)</option>
                <option value="Neutered Male">Neutered Male</option>
                <option value="Spayed Female">Spayed Female</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Color / Markings</label>
            <input type="text" name="color_markings" class="form-control" placeholder="e.g. White with brown patches">
        </div>
    </div>
</div>

<script>
(function() {
    function initSelect2Component() {
        if (window.jQuery && typeof jQuery.fn.select2 === 'function') {
            $('.select2-searchable').each(function() {
                const $el = $(this);
                if (!$el.hasClass('select2-hidden-accessible')) {
                    const $modal = $el.closest('.modal-backdrop');
                    $el.select2({
                        dropdownParent: $modal.length ? $modal : $(document.body),
                        width: '100%',
                        placeholder: $el.attr('data-placeholder') || '-- Search by Name, Code, or Contact --',
                        allowClear: true
                    });
                }
            });
        } else {
            setTimeout(initSelect2Component, 100);
        }
    }
    initSelect2Component();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSelect2Component);
    }
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-modal-target]')) {
            setTimeout(initSelect2Component, 60);
        }
    });
})();
</script>
