@props([
    'name' => 'split_users',
    'id' => null,
    'selected' => [],
    'placeholder' => 'Pilih siapa saja yang titip / berutang...',
    'options' => [], // [ ['id' => 1, 'name' => 'User Name', 'desc' => 'Penghuni'], ... ]
])

@php
    $selectId = $id ?? 'multiselect_' . str_replace(['[', ']'], '_', $name) . '_' . \Illuminate\Support\Str::random(5);
    $selectedValues = (array) old($name, $selected ?? []);
    // pastikan bentuk string untuk perbandingan
    $selectedValues = array_map('strval', $selectedValues);
@endphp

<div class="relative w-full custom-multiselect-component" id="{{ $selectId }}_wrapper" data-multiselect-id="{{ $selectId }}">
    <!-- Hidden Container for submitted inputs -->
    <div id="{{ $selectId }}_hidden_inputs">
        @foreach($selectedValues as $val)
            <input type="hidden" name="{{ $name }}[]" value="{{ $val }}" class="multi-val-input" data-user-id="{{ $val }}">
        @endforeach
    </div>

    <!-- Dropdown Trigger Button -->
    <button
        type="button"
        id="{{ $selectId }}_btn"
        aria-haspopup="listbox"
        aria-expanded="false"
        class="w-full h-11 flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 hover:border-slate-400 focus:outline-none focus:border-slate-400 transition-all text-left outline-none group cursor-pointer"
    >
        <span id="{{ $selectId }}_label" class="text-sm font-medium text-slate-800 truncate">
            {{ $placeholder }}
        </span>

        <svg 
            class="multiselect-chevron w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0" 
            viewBox="0 0 20 20" 
            fill="currentColor"
        >
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </button>

    <!-- Dropdown Options Menu -->
    <div
        id="{{ $selectId }}_menu"
        role="listbox"
        class="custom-multiselect-menu hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-lg shadow-slate-900/10 p-2 z-50 max-h-64 overflow-y-auto no-scrollbar space-y-1.5"
        style="-ms-overflow-style: none; scrollbar-width: none;"
    >
        @foreach($options as $opt)
            @php
                $optId = (string)($opt['id'] ?? $opt['value']);
                $optName = $opt['name'] ?? $opt['label'];
                $optDesc = $opt['desc'] ?? null;
                $optInitial = substr($optName, 0, 1);
                $isSelected = in_array($optId, $selectedValues);
            @endphp
            <div
                role="option"
                data-id="{{ $optId }}"
                data-name="{{ $optName }}"
                class="multiselect-option w-full flex items-center justify-between px-3 py-2.5 rounded-xl border text-sm transition-all text-left cursor-pointer {{ $isSelected ? 'bg-sky-50 border-sky-200 text-sky-950 font-medium' : 'bg-white border-transparent text-slate-700 hover:bg-slate-50 hover:border-slate-100 font-normal' }}"
            >
                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                    <div class="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                        {{ $optInitial }}
                    </div>
                    <div class="truncate">
                        <span class="block truncate font-medium text-slate-800 text-xs sm:text-sm">{{ $optName }}</span>
                        @if($optDesc)
                            <span class="block text-[11px] text-slate-400 font-normal truncate">{{ $optDesc }}</span>
                        @endif
                    </div>
                </div>

                <!-- Checkbox Indicator icon -->
                <div class="multiselect-check-indicator w-5 h-5 rounded-md border flex items-center justify-center transition-all flex-shrink-0 {{ $isSelected ? 'bg-sky-500 border-sky-500 text-white' : 'border-slate-300 bg-white' }}">
                    <svg class="w-3.5 h-3.5 {{ $isSelected ? '' : 'hidden' }}" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        @endforeach
    </div>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initMultiSelects() {
            document.querySelectorAll('.custom-multiselect-component').forEach(function (wrapper) {
                if (wrapper.dataset.initialized) return;
                wrapper.dataset.initialized = 'true';

                const selectId = wrapper.dataset.multiselectId;
                const btn = document.getElementById(selectId + '_btn');
                const menu = document.getElementById(selectId + '_menu');
                const hiddenInputsContainer = document.getElementById(selectId + '_hidden_inputs');
                const labelEl = document.getElementById(selectId + '_label');
                const chevron = btn.querySelector('.multiselect-chevron');
                const placeholderText = '{{ $placeholder }}';
                const inputName = 'split_users[]';

                function updateTriggerLabel() {
                    const currentInputs = hiddenInputsContainer.querySelectorAll('.multi-val-input');
                    const count = currentInputs.length;

                    if (count === 0) {
                        labelEl.textContent = placeholderText;
                        labelEl.className = 'text-sm font-normal text-slate-400 truncate';
                    } else if (count === 1) {
                        const optEl = menu.querySelector(`.multiselect-option[data-id="${currentInputs[0].value}"]`);
                        const name = optEl ? optEl.dataset.name : `1 orang dipilih`;
                        labelEl.textContent = `${name} dipilih`;
                        labelEl.className = 'text-sm font-medium text-slate-800 truncate';
                    } else {
                        labelEl.textContent = `${count} orang dipilih`;
                        labelEl.className = 'text-sm font-medium text-slate-800 truncate';
                    }

                    // Dispatch custom event to notify parent form
                    const selectedData = Array.from(hiddenInputsContainer.querySelectorAll('.multi-val-input')).map(inp => {
                        const optEl = menu.querySelector(`.multiselect-option[data-id="${inp.value}"]`);
                        return {
                            id: inp.value,
                            name: optEl ? optEl.dataset.name : `User #${inp.value}`
                        };
                    });

                    wrapper.dispatchEvent(new CustomEvent('multiselect-change', {
                        bubbles: true,
                        detail: { selected: selectedData }
                    }));
                }

                function openMenu() {
                    // Close others
                    document.querySelectorAll('.custom-multiselect-menu, .custom-select-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    menu.classList.remove('hidden');
                    btn.setAttribute('aria-expanded', 'true');
                    chevron.classList.add('rotate-180');
                }

                function closeMenu() {
                    menu.classList.add('hidden');
                    btn.setAttribute('aria-expanded', 'false');
                    chevron.classList.remove('rotate-180');
                }

                btn.addEventListener('click', function (e) {
                    if (menu.classList.contains('hidden')) {
                        openMenu();
                    } else {
                        closeMenu();
                    }
                });

                function toggleOption(id) {
                    const optEl = menu.querySelector(`.multiselect-option[data-id="${id}"]`);
                    const existingInp = hiddenInputsContainer.querySelector(`.multi-val-input[data-user-id="${id}"]`);
                    const indicator = optEl ? optEl.querySelector('.multiselect-check-indicator') : null;
                    const checkSvg = indicator ? indicator.querySelector('svg') : null;

                    if (existingInp) {
                        // Unselect
                        existingInp.remove();
                        if (optEl) {
                            optEl.classList.remove('bg-sky-50', 'border-sky-200', 'text-sky-950', 'font-medium');
                            optEl.classList.add('bg-white', 'border-transparent', 'text-slate-700', 'hover:bg-slate-50', 'hover:border-slate-100', 'font-normal');
                        }
                        if (indicator) {
                            indicator.className = 'multiselect-check-indicator w-5 h-5 rounded-md border flex items-center justify-center transition-all flex-shrink-0 border-slate-300 bg-white';
                        }
                        if (checkSvg) checkSvg.classList.add('hidden');
                    } else {
                        // Select
                        const newInp = document.createElement('input');
                        newInp.type = 'hidden';
                        newInp.name = inputName;
                        newInp.value = id;
                        newInp.className = 'multi-val-input';
                        newInp.dataset.userId = id;
                        hiddenInputsContainer.appendChild(newInp);

                        if (optEl) {
                            optEl.classList.add('bg-sky-50', 'border-sky-200', 'text-sky-950', 'font-medium');
                            optEl.classList.remove('bg-white', 'border-transparent', 'text-slate-700', 'hover:bg-slate-50', 'hover:border-slate-100', 'font-normal');
                        }
                        if (indicator) {
                            indicator.className = 'multiselect-check-indicator w-5 h-5 rounded-md border flex items-center justify-center transition-all flex-shrink-0 bg-sky-500 border-sky-500 text-white';
                        }
                        if (checkSvg) checkSvg.classList.remove('hidden');
                    }

                    updateTriggerLabel();
                }

                menu.querySelectorAll('.multiselect-option').forEach(opt => {
                    opt.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const id = this.dataset.id;
                        toggleOption(id);
                    });
                });

                updateTriggerLabel();
            });

            // Click outside to close
            document.addEventListener('click', function (e) {
                document.querySelectorAll('.custom-multiselect-component').forEach(function (wrapper) {
                    if (!wrapper.contains(e.target)) {
                        const selectId = wrapper.dataset.multiselectId;
                        const menu = document.getElementById(selectId + '_menu');
                        const btn = document.getElementById(selectId + '_btn');
                        if (menu && !menu.classList.contains('hidden')) {
                            menu.classList.add('hidden');
                            btn.setAttribute('aria-expanded', 'false');
                            const chev = btn.querySelector('.multiselect-chevron');
                            if (chev) chev.classList.remove('rotate-180');
                        }
                    }
                });
            });
        }

        initMultiSelects();
    });
</script>
@endonce

